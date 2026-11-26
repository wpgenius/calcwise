/**
 * Editor for the CalcWise Calculator block: a calculator picker, per-field
 * starting values and limits in the sidebar, and a live preview rendered by
 * the server (the same markup as the front end).
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	Placeholder,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { fieldsFor, setFieldValue } from './fields';

/**
 * Calculator definitions from PHP (src/Blocks/Blocks.php).
 *
 * @return {Object} Type → { title, description, fields }.
 */
const calculators = () => window.calcwiseBlock?.calculators || {};

/**
 * Start the calculators inside the preview each time the server sends new
 * markup. The preview lives in the editor canvas, which may be an iframe with
 * its own window, so the runtime is looked up there.
 *
 * @param {Object} ref  React ref of the preview wrapper.
 * @param {string} type Calculator type (the preview mounts when it is set).
 */
function useStartPreview( ref, type ) {
	useEffect( () => {
		const node = ref.current;
		if ( ! node ) {
			return undefined;
		}
		const start = () =>
			node.ownerDocument?.defaultView?.calcwise?.init?.( node );
		const observer = new node.ownerDocument.defaultView.MutationObserver(
			start
		);
		observer.observe( node, { childList: true, subtree: true } );
		start();
		return () => observer.disconnect();
	}, [ ref, type ] );
}

export default function Edit( { attributes, setAttributes } ) {
	const { type, fields } = attributes;
	const blockProps = useBlockProps();
	const preview = useRef();
	useStartPreview( preview, type );

	const all = calculators();
	const definition = all[ type ];
	const options = [
		{ label: __( 'Choose a calculator', 'calcwise' ), value: '' },
		...Object.entries( all ).map( ( [ value, calc ] ) => ( {
			label: calc.title,
			value,
		} ) ),
	];

	const chooseType = ( value ) =>
		setAttributes( {
			type: value,
			fields: fieldsFor( fields, all[ value ] ),
		} );

	const picker = (
		<SelectControl
			label={ __( 'Calculator', 'calcwise' ) }
			value={ type }
			options={ options }
			onChange={ chooseType }
			__nextHasNoMarginBottom
			__next40pxDefaultSize
		/>
	);

	const numberInput = ( key, prop, label, field ) => (
		<TextControl
			key={ prop }
			type="number"
			label={ label }
			value={ fields?.[ key ]?.[ prop ] ?? '' }
			placeholder={ String( field[ prop ] ) }
			onChange={ ( raw ) =>
				setAttributes( {
					fields: setFieldValue( fields, key, prop, raw ),
				} )
			}
			__nextHasNoMarginBottom
			__next40pxDefaultSize
		/>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Calculator', 'calcwise' ) }>
					{ picker }
					{ definition?.description && (
						<p className="description">
							{ definition.description }
						</p>
					) }
				</PanelBody>
				{ definition &&
					Object.entries( definition.fields ).map(
						( [ key, field ] ) => (
							<PanelBody
								key={ key }
								title={ field.label }
								initialOpen={ false }
							>
								<p className="description">
									{ __(
										'Leave a box empty to use the calculator’s own value.',
										'calcwise'
									) }
								</p>
								{ numberInput(
									key,
									'default',
									__( 'Starting value', 'calcwise' ),
									field
								) }
								{ numberInput(
									key,
									'min',
									__( 'Lowest value', 'calcwise' ),
									field
								) }
								{ numberInput(
									key,
									'max',
									__( 'Highest value', 'calcwise' ),
									field
								) }
								{ numberInput(
									key,
									'step',
									__( 'Step', 'calcwise' ),
									field
								) }
							</PanelBody>
						)
					) }
			</InspectorControls>
			<div { ...blockProps }>
				{ definition ? (
					<div ref={ preview }>
						<ServerSideRender
							block="calcwise/calculator"
							attributes={ { type, fields } }
							skipBlockSupportAttributes
						/>
					</div>
				) : (
					<Placeholder
						icon="calculator"
						label={ __( 'CalcWise Calculator', 'calcwise' ) }
						instructions={
							type
								? sprintf(
										/* translators: %s: calculator type. */
										__(
											'The "%s" calculator is not available. Choose another one.',
											'calcwise'
										),
										type
									)
								: __(
										'Choose which calculator to show.',
										'calcwise'
									)
						}
					>
						{ picker }
					</Placeholder>
				) }
			</div>
		</>
	);
}

/**
 * Editor view: sidebar controls plus a live preview rendered by render.php.
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

export default function Edit( { attributes, setAttributes } ) {
	const { count, layout } = attributes;

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Settings', 'calcwise' ) }
				>
					<RangeControl
						label={ __(
							'Number of items',
							'calcwise'
						) }
						value={ count }
						onChange={ ( value ) =>
							setAttributes( { count: value } )
						}
						min={ 1 }
						max={ 12 }
					/>
					<SelectControl
						label={ __( 'Layout', 'calcwise' ) }
						value={ layout }
						options={ [
							{
								label: __(
									'Grid',
									'calcwise'
								),
								value: 'grid',
							},
							{
								label: __(
									'List',
									'calcwise'
								),
								value: 'list',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { layout: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block={ metadata.name }
				attributes={ attributes }
			/>
		</div>
	);
}

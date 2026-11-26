/**
 * CalcWise Calculator block. One block with a calculator picker; each
 * calculator is also offered as its own variation (registered in PHP from the
 * calculator definitions), so it can be found by name in the inserter.
 */
import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );

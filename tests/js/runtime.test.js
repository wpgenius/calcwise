import { beforeEach, describe, expect, it, vi } from 'vitest';

/**
 * Load a fresh copy of the runtime (window.calcwise).
 *
 * @return {Promise<Object>} window.calcwise.
 */
async function loadRuntime() {
	vi.resetModules();
	delete window.calcwise;
	await import( '../../src/runtime/index.js' );
	return window.calcwise;
}

/**
 * A calculator element as the server renders it (no fields needed here).
 *
 * @param {string} type Calculator type.
 * @param {string} id   Element ID.
 * @return {HTMLElement} Element, added to the page.
 */
function calculator( type, id ) {
	const element = document.createElement( 'div' );
	element.className = 'calcwise-calculator';
	element.id = id;
	element.dataset.calcwiseType = type;
	element.dataset.calcwiseSettings = '{}';
	document.body.appendChild( element );
	return element;
}

beforeEach( () => {
	document.body.innerHTML = '';
} );

describe( 'init', () => {
	it( 'starts the other calculators when one fails to start', async () => {
		const runtime = await loadRuntime();
		calculator( 'broken', 'calcwise-1' );
		const good = calculator( 'good', 'calcwise-2' );
		const error = vi
			.spyOn( console, 'error' )
			.mockImplementation( () => {} );

		runtime.register( 'broken', () => {
			throw new Error( 'boom' );
		} );
		const started = [];
		runtime.register( 'good', ( element ) => started.push( element ) );

		expect( started ).toEqual( [ good ] );
		expect( error ).toHaveBeenCalled();
		error.mockRestore();
	} );

	it( 'destroys instances whose markup was removed (editor preview refreshes)', async () => {
		const runtime = await loadRuntime();
		const destroy = vi.fn();
		const first = calculator( 'sip', 'calcwise-1' );
		runtime.register( 'sip', () => ( { destroy } ) );

		first.remove();
		calculator( 'sip', 'calcwise-1' );
		runtime.init();

		expect( destroy ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'destroys the instances in a container on request', async () => {
		const runtime = await loadRuntime();
		const destroy = vi.fn();
		const element = calculator( 'sip', 'calcwise-1' );
		runtime.register( 'sip', () => ( { destroy } ) );

		runtime.destroy( document.body );

		expect( destroy ).toHaveBeenCalledTimes( 1 );
		expect( element.dataset.calcwiseReady ).toBeUndefined();
		// It can be started again afterwards.
		runtime.init();
		expect( element.dataset.calcwiseReady ).toBe( '1' );
	} );
} );

#!/usr/bin/env node
/**
 * Copies @wpgenius/financial-calculators from node_modules into
 * src/vendor/financial-calculators/ and updates the version it is quoted with.
 *
 *   npm run vendor:financial-calculators          copy and update
 *   npm run vendor:financial-calculators:check    report differences, exit 1 (CI)
 *
 * The plugin zip ships no node_modules, so the readable ES module build is
 * committed in src/vendor/ and bundled into build/runtime/ by `npm run build`.
 * package.json declares the dependency, so GitHub's dependency graph sees it.
 */
import { readFileSync, writeFileSync, copyFileSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const check = process.argv.includes( '--check' );
const pkgDir = join( root, 'node_modules/@wpgenius/financial-calculators' );

if ( ! existsSync( join( pkgDir, 'package.json' ) ) ) {
	console.error( 'Run `npm ci` first: @wpgenius/financial-calculators is not installed.' );
	process.exit( 1 );
}

const { version } = JSON.parse( readFileSync( join( pkgDir, 'package.json' ), 'utf8' ) );
const problems = [];

// The bundled module itself.
const source = join( pkgDir, 'dist/financial-calculators.esm.js' );
const target = join( root, 'src/vendor/financial-calculators/financial-calculators.esm.js' );
if ( ! existsSync( target ) || ! readFileSync( source ).equals( readFileSync( target ) ) ) {
	problems.push( 'src/vendor/financial-calculators/financial-calculators.esm.js differs from the npm package' );
	if ( ! check ) {
		copyFileSync( source, target );
	}
}

// Where the version is quoted: file, pattern, replacement.
const mentions = [
	[ 'src/vendor/financial-calculators/README.md', /^Version: .*$/m, `Version: ${ version }` ],
	[ 'readme.txt', /(\[financial-calculators\]\(https:\/\/github\.com\/wpgenius\/financial-calculators-js\) )[\d.]+/, `$1${ version }` ],
	[ 'THIRD-PARTY-NOTICES.txt', /^financial-calculators [\d.]+$/m, `financial-calculators ${ version }` ],
];
for ( const [ file, pattern, replacement ] of mentions ) {
	const path = join( root, file );
	const text = readFileSync( path, 'utf8' );
	if ( ! pattern.test( text ) ) {
		problems.push( `${ file }: version mention not found` );
		continue;
	}
	const updated = text.replace( pattern, replacement );
	if ( updated !== text ) {
		problems.push( `${ file } quotes another version than ${ version }` );
		if ( ! check ) {
			writeFileSync( path, updated );
		}
	}
}

if ( check ) {
	if ( problems.length ) {
		console.error( problems.map( ( p ) => `- ${ p }` ).join( '\n' ) );
		console.error( 'Run `npm run vendor:financial-calculators`, then `npm run build`, and commit the result.' );
		process.exit( 1 );
	}
	console.log( `src/vendor matches @wpgenius/financial-calculators ${ version }.` );
} else {
	console.log( problems.length ? `Updated to ${ version }. Run \`npm run build\` and commit src/vendor, build/ and the docs.` : `Already at ${ version }.` );
}

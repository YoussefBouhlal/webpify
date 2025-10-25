import '../scss/settings.scss';

import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import Main from './components/settings/main';

domReady( () => {
	const root = createRoot( document.getElementById( 'JS-webpify-settings' ) );
	root.render( <Main /> );
} );

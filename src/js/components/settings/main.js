import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Panel } from '@wordpress/components';

import Notices from '../notices';
import Generale from './generale';
import Bulk from './bulk';
import Footer from './footer';

const { optionName, ajaxUrl, nonce } = WEBPIFY_SETTINGS; // eslint-disable-line no-undef

const Main = () => {
	const [ format, setFormat ] = useState();
	const [ display, setDisplay ] = useState();
	const [ startBulk, setStartBulk ] = useState();

	const { createSuccessNotice, createErrorNotice, removeNotice } =
		useDispatch( noticesStore );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/settings' } ).then( ( settings ) => {
			const webpifySettings = settings.webpify_settings;

			setFormat( webpifySettings.format );
			setDisplay( webpifySettings.display );
		} );
	}, [] );

	/**
	 * Do bulk optimization
	 */
	const startBulkOptimization = () => {
		setStartBulk( true );

		const form = new FormData();
		form.append( 'action', 'webpify_bulk_optimization_start' );
		form.append( '_wpnonce', nonce );

		fetch( ajaxUrl, {
			method: 'POST',
			body: form,
		} )
			.then( ( r ) => r.json() )
			.then( ( res ) => {
				console.log( res );
			} )
			.catch( ( err ) => {
				console.log( err );
			} );
	};

	/**
	 * Stop bulk optimization
	 */
	const stopBulkOptimization = () => {
		setStartBulk( false );

		const form = new FormData();
		form.append( 'action', 'webpify_bulk_optimization_end' );
		form.append( '_wpnonce', nonce );

		fetch( ajaxUrl, {
			method: 'POST',
			body: form,
		} )
			.then( ( r ) => r.json() )
			.then( ( res ) => {
				console.log( res );
			} )
			.catch( ( err ) => {
				console.log( err );
			} );
	};

	/**
	 * Clear notices
	 */
	const clearNotices = () => {
		const existingNotices = wp.data.select( noticesStore ).getNotices();
		existingNotices.forEach( ( notice ) => removeNotice( notice.id ) );
	};

	/**
	 * Save settings
	 */
	const saveSettings = () => {
		clearNotices();

		apiFetch( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: {
				[ optionName ]: {
					format,
					display,
				},
			},
		} )
			.then( () => {
				createSuccessNotice( __( 'Settings saved', 'webpify' ) );
			} )
			.catch( ( error ) => {
				createErrorNotice( error.message );
			} );
	};

	return (
		<>
			<Notices />
			<Panel>
				<Generale format={ format } setFormat={ setFormat } />
				<Bulk
					display={ display }
					setDisplay={ setDisplay }
					stopBulkOptimization={ stopBulkOptimization }
					startBulkOptimization={ startBulkOptimization }
					startBulk={ startBulk }
				/>
				<Footer saveSettings={ saveSettings } />
			</Panel>
		</>
	);
};

export default Main;

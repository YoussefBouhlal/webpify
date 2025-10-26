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

const Main = () => {
	const [ format, setFormat ] = useState();
	const [ display, setDisplay ] = useState();

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
	const doBulkOptimization = () => {
		//TODO: add bulk optimization
		console.log( 'doBulkOptimization' );
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
				[ WEBPIFY_SETTINGS.option_name ]: { // eslint-disable-line no-undef, prettier/prettier
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
					doBulkOptimization={ doBulkOptimization }
				/>
				<Footer saveSettings={ saveSettings } />
			</Panel>
		</>
	);
};

export default Main;

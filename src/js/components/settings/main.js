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

	const { createSuccessNotice } = useDispatch( noticesStore );

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
	 * Save settings
	 */
	const saveSettings = () => {
		apiFetch( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: {
				webpify_settings: {
					format,
					display,
				},
			},
		} ).then( () => {
			createSuccessNotice( __( 'Settings saved', 'webpify' ) );
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

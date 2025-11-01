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

const { optionName, ajaxUrl, nonce, isPhpCompatibleAvif } = WEBPIFY_SETTINGS; // eslint-disable-line no-undef

const Main = () => {
	const [ format, setFormat ] = useState();
	const [ display, setDisplay ] = useState();
	const [ startBulk, setStartBulk ] = useState( false );
	const [ progressText, setProgressText ] = useState( '' );

	const { createSuccessNotice, createErrorNotice, removeNotice } =
		useDispatch( noticesStore );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/settings' } ).then( ( settings ) => {
			const webpifySettings = settings.webpify_settings;

			setFormat( webpifySettings.format );
			setDisplay( webpifySettings.display );
		} );
	}, [] );

	useEffect( () => {
		const checkProgressBulkOptimization = () => {
			if ( ! startBulk ) {
				return;
			}

			const form = new FormData();
			form.append( 'action', 'webpify_bulk_optimization_progress' );
			form.append( '_wpnonce', nonce );

			fetch( ajaxUrl, {
				method: 'POST',
				body: form,
			} )
				.then( ( r ) => r.json() )
				.then( ( res ) => {
					if ( res.success ) {
						setProgressText( res.data.progress );

						if ( ! res.data.running ) {
							clearInterval( intervalId );
							setStartBulk( false );
						}
					}
				} )
				.catch( () => {
					clearInterval( intervalId );
					setStartBulk( false );
					setProgressText(
						__(
							'Unxpected error. Please reload the page and try again.',
							'webpify'
						)
					);
				} );
		};
		const intervalId = setInterval( checkProgressBulkOptimization, 5000 );
		return () => clearInterval( intervalId );
	}, [ startBulk ] );

	/**
	 * Do bulk optimization
	 */
	const startBulkOptimization = () => {
		setStartBulk( true );
		setProgressText( '' );

		const form = new FormData();
		form.append( 'action', 'webpify_bulk_optimization_start' );
		form.append( '_wpnonce', nonce );

		fetch( ajaxUrl, {
			method: 'POST',
			body: form,
		} )
			.then( ( r ) => r.json() )
			.then( ( res ) => {
				if ( res.success ) {
					setProgressText( res.data.progress );
				} else {
					setProgressText( res.data );
					setStartBulk( false );
				}
			} )
			.catch( () => {
				setStartBulk( false );
				setProgressText(
					__(
						'Unxpected error. Please reload the page and try again.',
						'webpify'
					)
				);
			} );
	};

	/**
	 * Stop bulk optimization
	 */
	const stopBulkOptimization = () => {
		setStartBulk( false );
		setProgressText( '' );

		const form = new FormData();
		form.append( 'action', 'webpify_bulk_optimization_end' );
		form.append( '_wpnonce', nonce );

		fetch( ajaxUrl, {
			method: 'POST',
			body: form,
		} )
			.then( ( r ) => r.json() )
			.then( ( res ) => {
				if ( ! res.success ) {
					setProgressText( res.data );
				}
			} )
			.catch( () => {
				setProgressText(
					__(
						'Unxpected error. Please reload the page and try again.',
						'webpify'
					)
				);
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
				<Generale
					format={ format }
					setFormat={ setFormat }
					isPhpCompatibleAvif={ isPhpCompatibleAvif }
				/>
				<Bulk
					display={ display }
					setDisplay={ setDisplay }
					stopBulkOptimization={ stopBulkOptimization }
					startBulkOptimization={ startBulkOptimization }
					startBulk={ startBulk }
					progressText={ progressText }
				/>
				<Footer saveSettings={ saveSettings } />
			</Panel>
		</>
	);
};

export default Main;

import '../scss/settings.scss';

import domReady from '@wordpress/dom-ready';
import { createRoot, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Panel,
	PanelBody,
	SelectControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

const SettingsPage = () => {
	const [ format, setFormat ] = useState( 2 );
	const [ quality, setQuality ] = useState( 2 );
	const [ value, setValue ] = useState( false );

	const onClick = () => {
		setValue( ! value );
	};

	return (
		<Panel>
			<PanelBody>
				<SelectControl
					__next40pxDefaultSize={ false }
					__nextHasNoMarginBottom={ false }
					label={ __( 'Format', 'webpify' ) }
					value={ format }
					options={ [
						{ label: __( 'Off', 'webpify' ), value: 1 },
						{ label: __( 'WebP', 'webpify' ), value: 2 },
						{ label: __( 'AVIF', 'webpify' ), value: 3 },
					] }
					onChange={ setFormat }
				/>
				<SelectControl
					__next40pxDefaultSize={ false }
					__nextHasNoMarginBottom={ false }
					label={ __( 'Quality', 'webpify' ) }
					value={ quality }
					options={ [
						{ label: __( 'Low', 'webpify' ), value: 1 },
						{ label: __( 'Medium', 'webpify' ), value: 2 },
						{ label: __( 'High', 'webpify' ), value: 3 },
					] }
					onChange={ setQuality }
				/>

				<ToggleControl
					__nextHasNoMarginBottom={ false }
					label={ __(
						'Do not compress images already in same format',
						'webpify'
					) }
					checked={ value }
					onChange={ () => setValue( ( state ) => ! state ) }
				/>

				<Button
					variant="primary"
					onClick={ onClick }
					__next40pxDefaultSize
				>
					{ __( 'Save', 'webpify' ) }
				</Button>
			</PanelBody>
		</Panel>
	);
};

domReady( () => {
	const root = createRoot( document.getElementById( 'webpify-settings' ) );

	root.render( <SettingsPage /> );
} );

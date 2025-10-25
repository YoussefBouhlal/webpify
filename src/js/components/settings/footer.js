import { PanelBody, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Footer = ( { saveSettings } ) => {
	return (
		<PanelBody>
			<Button
				variant="primary"
				onClick={ saveSettings }
				__next40pxDefaultSize
			>
				{ __( 'Save', 'webpify' ) }
			</Button>
		</PanelBody>
	);
};

export default Footer;

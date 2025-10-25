import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Generale = ( { format, setFormat } ) => {
	return (
		<PanelBody title={ __( 'General settings', 'webpify' ) }>
			<div className="webpify-settings__field-container">
				<SelectControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Format', 'webpify' ) }
					value={ format }
					options={ [
						{ label: __( 'WebP', 'webpify' ), value: '1' },
						{ label: __( 'AVIF', 'webpify' ), value: '2' },
					] }
					onChange={ setFormat }
				/>
			</div>
		</PanelBody>
	);
};

export default Generale;

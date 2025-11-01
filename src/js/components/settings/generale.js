import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Generale = ( { format, setFormat, isPhpCompatibleAvif } ) => {
	return (
		<PanelBody title={ __( 'General settings', 'webpify' ) }>
			<div className="webpify-settings__field-container">
				<SelectControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Format', 'webpify' ) }
					value={ format }
					onChange={ setFormat }
				>
					<>
						<option value="1">{ __( 'WebP', 'webpify' ) }</option>
						<option disabled={ ! isPhpCompatibleAvif } value="2">
							{ __( 'AVIF', 'webpify' ) }{ ' ' }
							{ ! isPhpCompatibleAvif &&
								__( '(PHP 8.1 or higher)', 'webpify' ) }
						</option>
					</>
				</SelectControl>
			</div>
		</PanelBody>
	);
};

export default Generale;

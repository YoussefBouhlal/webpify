import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	PanelRow,
	RadioControl,
	Button,
} from '@wordpress/components';

const Bulk = ( {
	display,
	setDisplay,
	startBulkOptimization,
	stopBulkOptimization,
	startBulk,
} ) => {
	return (
		<PanelBody title={ __( 'Bulk settings', 'webpify' ) }>
			<div className="webpify-settings__field-container">
				<RadioControl
					label={ __(
						'Display images with new format on the site',
						'webpify'
					) }
					selected={ display }
					options={ [
						{
							label: __( 'Deactivate', 'webpify' ),
							value: '1',
						},
						{
							label: __( 'Use <picture> tags', 'webpify' ),
							value: '2',
						},
						{
							label: __( 'Use rewrite rules', 'webpify' ),
							value: '3',
						},
					] }
					onChange={ setDisplay }
				/>
			</div>
			<PanelRow>
				<div className="webpify-settings__field-container">
					<div className="webpify-settings__button__label">
						{ __( 'Bulk optimization', 'webpify' ) }
					</div>
					{ startBulk ? (
						<Button
							isDestructive
							variant="secondary"
							onClick={ stopBulkOptimization }
							__next40pxDefaultSize
						>
							{ __( 'Stop', 'webpify' ) }
						</Button>
					) : (
						<Button
							variant="secondary"
							onClick={ startBulkOptimization }
							__next40pxDefaultSize
						>
							{ __( 'Start', 'webpify' ) }
						</Button>
					) }
				</div>
			</PanelRow>
		</PanelBody>
	);
};

export default Bulk;

import '../scss/settings.scss';

import domReady from '@wordpress/dom-ready';
import { createRoot, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow } from '@wordpress/components';

const SettingsPage = () => {
    return (
        <Panel>
            <PanelBody>
                <PanelRow>
                    <div>{__( 'Placeholder for message control', 'webpify' )}</div>
                </PanelRow>
                <PanelRow>
                    <div>{__( 'Placeholder for display control', 'webpify' )}</div>
                </PanelRow>
            </PanelBody>
            <PanelBody
                title={ __( 'Appearance', 'webpify' ) }
                initialOpen={ false }
            >
                <PanelRow>
                    <div>{__( 'Placeholder for size control', 'webpify' )}</div>
                </PanelRow>
            </PanelBody>
        </Panel>
   );
};

domReady( () => {
    const root = createRoot(
        document.getElementById( 'webpify-settings' )
    );

    root.render( <SettingsPage /> );
} );

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Quotes.Layout.ConfigDrawerContent', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Quotes', 'config-drawer-content', null, null, true);
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
    });

    describe('_initHowTo()', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_MODULE_NAME').returns('LBL_MODULE_NAME')
                .withArgs('LBL_CONFIG_FIELD_SELECTOR').returns('LBL_MODULE_NAME LBL_CONFIG_FIELD_SELECTOR');
            layout.currentHowToData = {};
        });

        it('should set currentHowToData', function() {
            layout._switchHowToData('config-columns');

            expect(layout.currentHowToData.title).toBe('LBL_MODULE_NAME LBL_CONFIG_FIELD_SELECTOR');
        });
    });

    describe('onConfigPanelFieldsLoad', function() {
        var configPanel;

        beforeEach(function() {
            configPanel = {
                name: 'config-columns',
                eventViewName: 'worksheet_columns',
                panelFields: [
                    {
                        name: 'fake_field',
                        eventViewName: 'worksheet_columns'
                    }
                ]
            };

            sinon.stub(layout.context, 'trigger');
        });

        it('should trigger a config fields change if the panel is currently selected', function() {
            layout.selectedPanel = 'config-columns';
            layout.trigger('config:panel:fields:loaded', configPanel);
            expect(layout.context.trigger).toHaveBeenCalledWith(
                'config:fields:change', configPanel.eventViewName, configPanel.panelFields);
        });

        it('should not trigger a config fields change if the panel is not currently selected', function() {
            layout.selectedPanel = 'not-a-real-panel';
            layout.trigger('config:panel:fields:loaded', configPanel);
            expect(layout.context.trigger).not.toHaveBeenCalled();
        });
    });
});

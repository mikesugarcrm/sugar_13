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
describe('Base.View.HintDataEnrichmentDrawerView', function() {
    var app;
    var view;
    var module;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'Accounts';

        app.hint = {
            isDarkMode: function() {},
            getAccountBasicPanelFields: function() {},
            getAccountExpandedPanelFields: function() {},
            getPeopleBasicPanelFields: function() {},
            getPeopleExpandedPanelFields: function() {},
            getAccountDefaultBasicPanelFields: function() {},
            getAccountDefaultExpandedPanelFields: function() {},
            getPeopleDefaultBasicPanelFields: function() {},
            getPeopleDefaultExpandedPanelFields: function() {},
        };

        view = SugarTest.createView('base', module, 'hint-data-enrichment-drawer-view');
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('will have module name in currentModule', function() {
            expect(view.currentModule).toBe('Accounts');
        });

        it('will have config key name', function() {
            expect(view.configKey).toBe('hintConfig');
        });
    });

    describe('_render()', function() {
        it('should render the view', function() {
            var renderStub = sinon.stub(view, '_render');
            view._render();
            expect(renderStub).toHaveBeenCalledOnce();
            renderStub.restore();
        });
    });
});

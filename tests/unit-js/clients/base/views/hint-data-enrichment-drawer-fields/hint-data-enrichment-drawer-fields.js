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
describe('Base.View.HintDataEnrichmentDrawerFields', function() {
    var app;
    var view;
    var module;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'Accounts';

        app.hint = {
            getPanelsForHintEnrichFields: function() {},
            getModuleFieldsAvailableForSelection: function() {},
            getVisibleFieldsFromAllPannelsForDefaultSelection: function() {}
        };

        view = SugarTest.createView('base', module, 'hint-data-enrichment-drawer-fields');
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should add listeners', function() {
            expect(view.events['keyup .searchbox-field']).toEqual('onSearchFilterChanged');
            expect(view.events['click .fieldSelector']).toEqual('fieldToggled');
            expect(view.events['click .fieldSelectorForEnrich']).toEqual('addEnrichArray');
        });

        it('will have module name in currentModule', function() {
            expect(view.currentModule).toBe('Accounts');
        });

        it('will have config key name', function() {
            expect(view.configKey).toBe('hintConfig');
        });

        it('will have field config cache lifetime', function() {
            expect(view._maxLifetime).toBe(5 * 60 * 1000);
        });

        it('will have token expiration timeOut', function() {
            expect(view.tokenExpirationTimeOut).toBe(60 * 60 * 1000);
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

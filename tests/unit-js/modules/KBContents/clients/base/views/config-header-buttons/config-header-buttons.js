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
describe('modules.KBContents.clients.base.view.ConfigHeaderButtons', function() {
    var app;
    var view;
    var module;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'KBContents';

        SugarTest.loadComponent('base', 'view', 'config-header-buttons');
        view = SugarTest.createView('base', module, 'config-header-buttons', null, null, true);

        context = app.context.getContext({
            module: module
        });
        context.set('model', app.data.createBean(module));

        sinon.stub(view, 'triggerBefore').returns(true);
        sinon.stub(view, 'getField').withArgs('save_button').returns({
            setDisabled: $.noop
        });

        sinon.stub(app, 'sync');
        sinon.stub(app.accessibility, 'run').callsFake(function() {});
        sinon.stub(app.alert, 'show').callsFake(function() {
            return {
                getCloseSelector: function() {
                    return {
                        on: function() {}
                    };
                }
            };
        });
        sinon.stub(app.api, 'call').callsFake(function(method, url, data, callbacks) {
            callbacks.success({});
        });
    });

    afterEach(function() {
        sinon.restore();
        view = null;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('saveConfig()', function() {

        it('will sync metadata', function() {
            app.drawer = {
                close: $.noop,
                count: $.noop
            };
            sinon.stub(app.drawer, 'count').returns(1);

            var model = view.context.get('model');
            var doValidateStub = sinon.stub(model, 'doValidate');

            // emulating passed validation
            doValidateStub.callsArgWith(1, true);

            view.saveConfig();
            expect(app.sync).toHaveBeenCalledOnce();
        });

        it('will validate model', function() {
            app.drawer = {
                close: $.noop,
                count: $.noop
            };
            sinon.stub(app.drawer, 'count').returns(1);

            var model = view.context.get('model');
            sinon.stub(model, 'doValidate');

            view.saveConfig();
            expect(model.doValidate).toHaveBeenCalledOnce();
        });
    });
});


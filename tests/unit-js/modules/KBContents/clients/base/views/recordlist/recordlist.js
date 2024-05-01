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
describe('KBContents.Base.Views.RecordList', function() {

    var app, view, sandbox, layout, context, moduleName = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', app.data.createBean(moduleName));
        context.parent = app.context.getContext({
            module: moduleName
        });
        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBContent',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        layout = SugarTest.createLayout(
            'base',
            moduleName,
            'list',
            null,
            context.parent
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'recordlist',
            null,
            null,
            true,
            layout
        );
    });

    afterEach(function() {
        sandbox.restore();
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['KBContent'];
        view = null;
        layout = null;
    });

    describe('initialize()', function() {
        var superStub, hasAccessToModelStub, contextSetStub;

        beforeEach(function() {
            superStub = sandbox.stub(view, '_super');
            contextSetStub = sandbox.stub(view.context, 'set');
        });

        it('should call parent method when initialize', function() {
            view.initialize({});
            expect(superStub).toHaveBeenCalled();
        });

        it('should check acl edit and when not allowed set context noedit', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return false;
            });
            view.initialize({});
            expect(contextSetStub).toHaveBeenCalledWith('requiredFilter', 'records-noedit');
        });

        it('should check acl edit and when allowed then not set context', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return true;
            });
            view.initialize({});
            expect(contextSetStub).not.toHaveBeenCalled();
        });
    });

    describe('parseFieldMetadata()', function() {
        var superStub, hasAccessToModelStub, contextSetStub, data;

        beforeEach(function() {
            data = {
                module: moduleName,
                meta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'test1'
                                },
                                {
                                    name: 'test2'
                                },
                                {
                                    name: 'status'
                                }
                            ]
                        }
                    ]
                }
            };
            superStub = sandbox.stub(view, '_super').callsFake(function(func, args) {
                return _.clone(args[0]);
            });
            contextSetStub = sandbox.stub(view.context, 'set');
        });

        it('should check if data is not filtered', function() {
            var result = view.parseFieldMetadata(data);
            expect(result.meta.panels[0].fields).toContain({
                name: 'status'
            });
        });
    });

    describe('Additional validation', function() {

        it('Expiration date cannot be lower than publishing.', function() {
            var validateSpy = sandbox.spy(function(mod, field, errors) {
            });
            sandbox.stub(view, 'getField').callsFake(function(name) {
                var obj = {};
                obj[name] = true;
                return obj;
            });
            view.model.set('status', 'draft');
            view.model.set('exp_date', '2010-10-10');
            view.model.set('active_date', '2011-10-10');

            view._doValidateExpDateField(view.model, [], [], validateSpy);

            expect(validateSpy.args[0][2].exp_date.expDateLow).toBeTruthy();
        });

        it('Approved without publishing date generates confirmation alert.', function() {
            var validateSpy = sandbox.spy(function(mod, field, errors) {
            });
            sandbox.stub(view, 'getField').callsFake(function(name) {
                if (name == 'active_date') {
                    return {name: 'active_date'};
                }
            });
            var alertShowStub = sandbox.stub(app.alert, 'show');

            view.model.set('status', 'approved');
            view.model.set('active_date', null);

            view._doValidateActiveDateField(view.model, [], [], validateSpy);

            expect(alertShowStub).toHaveBeenCalled();
            expect(alertShowStub.lastCall.args[0]).toEqual('save_without_publish_date_confirmation');
        });

        it('Approved with publishing date less or equal current date generates an error.', function() {
            var validateSpy = sandbox.spy(function(mod, field, errors) {
            });
            sandbox.stub(view, 'getField').callsFake(function(name) {
                if (name == 'active_date') {
                    return {name: 'active_date'};
                }
            });
            view.model.set('status', 'approved');
            view.model.set('active_date', '2011-10-10');
            view._doValidateActiveDateField(view.model, [], [], validateSpy);
            expect(validateSpy.args[0][2].active_date.activeDateLow).toBeTruthy();

            view.model.set('active_date', Date.now() - 1);
            view._doValidateActiveDateField(view.model, [], [], validateSpy);
            expect(validateSpy.args[0][2].active_date.activeDateLow).toBeTruthy();
        });

        it('Expiration changes own date to current.', function() {
            sandbox.stub(view.model, 'changedAttributes').callsFake(function() {
                return {status: 'published'};
            });
            view.model.set('status', 'expired');
            view._validationComplete(view.model, true);

            expect(view.model.get('exp_date')).toEqual(app.date().formatServer(true));
        });

        it('Publishing changes own date to current.', function() {
            sandbox.stub(view.model, 'changedAttributes').callsFake(function() {
                return {status: 'approved'};
            });
            view.model.set('status', 'published');
            view._validationComplete(view.model, true);

            expect(view.model.get('active_date')).toEqual(app.date().formatServer(true));
        });
    });
});

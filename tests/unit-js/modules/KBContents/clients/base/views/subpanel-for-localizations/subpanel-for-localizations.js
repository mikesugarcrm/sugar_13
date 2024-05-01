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
describe('KBContents.Base.Views.SubpanelForLocalizations', function() {

    var app, view, sandbox, context, moduleName = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new Backbone.Model());
        context.parent = new Backbone.Model();
        SugarTest.loadComponent(
            'base',
            'view',
            'subpanel-for-localizations',
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'subpanel-for-localizations',
            null,
            context,
            moduleName
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        view = null;
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

        it('should check acl edit and set context noedit when not allowed', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return false;
            });
            view.initialize({});
            expect(contextSetStub).toHaveBeenCalledWith('requiredFilter', 'records-noedit');
        });

        it('should check acl edit and not set context when allowed', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return true;
            });
            view.initialize({});
            expect(contextSetStub).not.toHaveBeenCalledWith('requiredFilter', 'records-noedit');
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

        it('should check acl edit and do not filter data when allowed', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return true;
            });
            var result = view.parseFieldMetadata(data);

            expect(superStub).toHaveBeenCalled();
            expect(hasAccessToModelStub).toHaveBeenCalled();
            expect(result.meta.panels[0].fields).toContain({
                name: 'status'
            });
        });

        it('should check acl edit and remove secure fields when not allowed', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return false;
            });
            var result = view.parseFieldMetadata(data);

            expect(superStub).toHaveBeenCalled();
            expect(hasAccessToModelStub).toHaveBeenCalled();
            expect(result.meta.panels[0].fields).not.toContain({
                name: 'status'
            });
        });
    });
});

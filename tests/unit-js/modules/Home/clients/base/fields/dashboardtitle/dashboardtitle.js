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
describe('Home.Base.Field.Dashboardtitle', function() {
    var app;
    var field;
    var sandbox = sinon.createSandbox();

    beforeEach(function() {
        app = SugarTest.app;
        app.routing.start();
    });

    afterEach(function() {
        sandbox.restore();
        app.router.stop();

        if (field) {
            field.dispose();
        }
    });

    describe('toggleClicked', function() {
        var context;
        var modelA;
        var modelB;
        var getLangStub;
        var htmlStub;

        beforeEach(function() {
            context = new app.Context();
            modelA = app.data.createBean('Dashboards', {name: 'Dashboard A'});
            modelB = app.data.createBean('Dashboards', {
                name: 'LBL_DASHBOARD_B',
                dashboard_module: 'Home'
            });
            field = SugarTest.createField({
                name: 'test_field',
                type: 'dashboardtitle',
                module: 'Home',
                context: context,
                loadFromModule: true,
                model: modelA
            });
            field.$ = function() {
                return {
                    html: htmlStub
                };
            };
            sinon.stub(field, '_super');
            htmlStub = sandbox.stub();
            getLangStub = sandbox.stub(app.lang, 'get');
            getLangStub.withArgs('LBL_DASHBOARD_B', 'Home').returns('Dashboard B');
        });

        it('should do nothing when dashboard list is already populated', function() {
            field.dashboards = app.data.createBeanCollection('Dashboards', [modelB]);

            field.toggleClicked();

            expect(htmlStub.callCount).toEqual(0);
        });

        it('should populate the dropdown list if the list is empty', function() {
            var contextBro = new app.Context({
                dashboard_module: 'Home'
            });
            contextBro.set('collection', app.data.createBeanCollection(
                'Dashboards',
                [modelA, modelB]
            ));
            var parentStub = {
                getChildContext: function() {
                    return contextBro;
                },
                get: function() {
                    return 'record';
                }
            };

            var templateStub = sandbox.stub().returns('Rendered Template');
            sandbox.stub(app.template, 'getField').returns(templateStub);
            context.parent = parentStub;
            field.dashboards = [];

            field.toggleClicked();

            expect(field.dashboards.models.length).toEqual(1);
            expect(field.dashboards.models[0].get('name')).toEqual('Dashboard B');
            expect(templateStub).toHaveBeenCalledWith(field.dashboards);
            expect(htmlStub).toHaveBeenCalledWith('Rendered Template');
        });
    });

    describe('managerClicked', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'test_field',
                type: 'dashboardtitle',
                viewName: 'testViewName',
                fieldDef: {},
                module: 'Home',
                loadFromModule: true
            });
        });

        it('should navigate to the Manage Dashboards page based on the context', function() {
            var contextGetStub = sandbox.stub(app.controller.context, 'get');
            var navigateStub = sandbox.stub(app.router, 'navigate');

            contextGetStub.withArgs('module').returns('TestModule');
            contextGetStub.withArgs('layout').returns('TestLayout');

            field.managerClicked();
            expect(navigateStub).toHaveBeenCalledWith('#Dashboards?moduleName=TestModule' +
                '&viewName=TestLayout', {trigger: true});
        });
    });

    describe('format', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'test_field',
                type: 'dashboardtitle',
                fieldDef: {},
                module: 'Home',
                loadFromModule: true
            });
            field.context.parent = new app.Context();
        });

        afterEach(function() {
            delete field.context.parent;
        });

        it('should translate the dashlet label from the parent context\'s module if it exists', function() {
            field.context.parent.set('module', 'ParentContextModule');
            sandbox.stub(app.lang, 'get').withArgs('LBL_FORMAT_TEST', 'ParentContextModule').returns('Translated');
            expect(field.format('LBL_FORMAT_TEST')).toEqual('Translated');
        });

        it('should translate the dashlet label from the context\'s module if the parent context lacks one', function() {
            sandbox.stub(app.lang, 'get').withArgs('LBL_FORMAT_TEST', 'Home').returns('Translated from Home module');
            expect(field.format('LBL_FORMAT_TEST')).toEqual('Translated from Home module');
        });
    });

    describe('getCellPadding', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'test_field',
                type: 'dashboardtitle',
                module: 'Home',
                loadFromModule: true
            });
        });

        it('should return 0 if there is no dropdown toggle button', function() {
            sandbox.stub(field, '$').returns({
                length: 0
            });
            expect(field.getCellPadding()).toBe(0);
        });

        it('should calculate total padding if dropdown toggle exists', function() {
            field.$ = function() {
                return {
                    css: function(direction) {
                        return direction === 'padding-left' ? 5 : 10;
                    },
                    length: 1
                };
            };

            expect(field.getCellPadding()).toBe(15);
        });
    });
});

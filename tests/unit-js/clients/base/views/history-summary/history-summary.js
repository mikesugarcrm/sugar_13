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
describe('Base.View.HistorySummary', function() {
    var app,
        view,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        sandbox.stub(app.template, 'getView').callsFake(function() {
            return function() {
                return '';
            };
        });

        var context = app.context.getContext(),
            meta = {
                template: 'history-summary'
            };

        view = SugarTest.createView('base', null, 'history-summary', meta, context, false, null, true);

        sandbox.stub(view.collection, 'fetch').callsFake(function() {});
    });

    afterEach(function() {
        app = null;
        sandbox.restore();
        view = null;
    });

    describe('setActivityModulesToFetch()', function() {
        it('should populate activityModules with defaults', function() {
            view.setActivityModulesToFetch();
            expect(view.activityModules).toEqual(view.allActivityModules);
        });
    });

    describe('_renderField()', function() {
        var field,
            superStub,
            testModule;

        beforeEach(function() {
            field = {
                name: '',
                model: new Backbone.Model(),
                def: {
                    type: 'default'
                }
            };
            superStub = sinon.stub(view, '_super').callsFake(function() {});
            testModule = 'TestModule';
        });

        afterEach(function() {
            field = null;
            superStub.restore();
            testModule = null;
        });

        it('should set field.model.module when fieldName=="name"', function() {
            field.name = 'name';
            field.model.set({
                _module: testModule
            });

            view._renderField(field);
            expect(field.model.module).toEqual(testModule);
        });

        it('should set module in field.model when fieldName=="module"', function() {
            field.name = 'module';
            field.model.set({
                moduleNameSingular: testModule
            });

            view._renderField(field);
            expect(field.model.get('module')).toEqual(testModule);
        });

        describe('should set related_contact and related_contact_id when fieldName=="related_contact"', function() {
            it('when fieldModule=="Emails"', function() {
                field.name = 'related_contact';
                field.model.set({
                    _module: 'Emails'
                });

                view._renderField(field);
                expect(field.model.get('related_contact')).toEqual('');
                expect(field.model.get('related_contact_id')).toEqual('');
            });

            it('when fieldModule is any other module', function() {
                field.name = 'related_contact';
                field.model.set({
                    _module: 'Notes',
                    contact_name: 'TestContactName',
                    contact_id: 'TestContactId'
                });

                view._renderField(field);
                expect(field.model.get('related_contact')).toEqual('TestContactName');
                expect(field.model.get('related_contact_id')).toEqual('TestContactId');
            });
        });

        it('should set module in field.model when fieldName=="status" and the module is "Emails"', function() {
            sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
                return {
                    testStatus: 'Test Status'
                }
            });

            field.name = 'status';
            field.model.set({
                _module: 'Emails',
                status: 'testStatus'
            });
            view._renderField(field);

            expect(field.model.get('status')).toEqual('Test Status');

            app.lang.getAppListStrings.restore();
        });

        it('should set field.model.module when field id=="previewBtn"', function() {
            field.name = 'name';
            field.def = {
                id: 'previewBtn'
            };
            field.model.set({
                _module: testModule
            });

            view._renderField(field);
            expect(field.model.module).toEqual(testModule);
        });

        it('should call _super() ', function() {
            field.def = {};
            view._renderField(field);
            expect(superStub).toHaveBeenCalled();
        });
    });
});

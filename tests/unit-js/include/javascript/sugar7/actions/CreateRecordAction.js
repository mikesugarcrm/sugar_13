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
describe('Actions.CreateRecordAction', function() {
    var app;
    var sandbox;
    var metadata;

    beforeEach(function() {
        app = SugarTest.app;

        metadata = {
            fields: {
                name: {
                    name: 'name',
                    vname: 'LBL_NAME',
                    type: 'varchar',
                    len: 255,
                    comment: 'Name of this bean'
                },
                description: {
                    name: 'description',
                    vname: 'LBL_DESCRIPTION',
                    type: 'text',
                    comment: 'Description of this bean'
                }
            }
        };

        SugarTest.seedMetadata();
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.updateModuleMetadata('Accounts', metadata);
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        app.data.declareModels();

        SugarTest.loadFile('../include/javascript/sugar7/actions', 'CreateRecordAction', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();
    });

    describe('Action registration', function() {
        it('should have been added to the `app.actions` registry', function() {
            expect(typeof app.actions.CreateRecord).toEqual('function');
        });
    });

    describe('run()', function() {
        var currentExecution;
        var context;
        var recordView;

        beforeEach(function() {
            currentExecution = {
                nextAction: sinon.stub()
            };

            app.drawer = {
                open: sandbox.stub()
            };

            context = SugarTest.app.context.getContext();
            context.set({
                module: 'Accounts'
            });
            context.prepare();
            context.get('model').set({
                id: _.uniqueId(),
                name: 'Test Account',
                description: 'Test Description'
            });

            recordView = SugarTest.createView('base', 'Accounts', 'record', null, context);
        });

        afterEach(function() {
            recordView.dispose();

            delete app.drawer;
        });

        it('should open a basic create record drawer with static values', function() {
            var def = {
                properties: {
                    'attributes': {
                        'description': {
                            'fieldName': 'description',
                            'isCalculated': false,
                            'formula': '',
                            'value': {
                                'description': 'test'
                            }
                        }
                    },
                    'parentAttributes': {
                        'name': {
                            'fieldName': 'name',
                            'parentFieldName': 'name'
                        }
                    },
                    'module': 'Accounts',
                    'link': '',
                    'mustLinkRecord': false,
                    'copyFromParent': false,
                    'autoCreate': false
                }
            };

            var opts = {
                recordModel: context.get('model'),
                recordView: recordView,
                createLinkModelFct: recordView.createLinkModel
            };

            var action = new app.actions.CreateRecord(def);

            action.run(opts, currentExecution);

            expect(app.drawer.open).toHaveBeenCalledOnce();

            var accountModel = app.drawer.open.firstCall.args[0].context.model;

            expect(accountModel.get('description')).toBe('test');
        });

        it('should open a basic create record drawer with parent record values', function() {
            var def = {
                properties: {
                    'attributes': {

                    },
                    'parentAttributes': {

                    },
                    'module': 'Accounts',
                    'link': '',
                    'mustLinkRecord': false,
                    'copyFromParent': true,
                    'autoCreate': false
                }
            };

            var opts = {
                recordModel: context.get('model'),
                recordView: recordView,
                createLinkModelFct: recordView.createLinkModel
            };

            var action = new app.actions.CreateRecord(def);

            action.run(opts, currentExecution);

            expect(app.drawer.open).toHaveBeenCalledOnce();
            expect(currentExecution.nextAction).toHaveBeenCalledOnce();

            var accountModel = app.drawer.open.firstCall.args[0].context.model;

            expect(accountModel.get('name')).toBe('Test Account');
            expect(accountModel.get('description')).toBe('Test Description');
        });
    });

    describe('_getRecordViewFields()', function() {
        it('should return all the fields from the record view', function() {
            var def = {
                properties: {
                    'attributes': {

                    },
                    'parentAttributes': {

                    },
                    'module': 'Accounts',
                    'link': '',
                    'mustLinkRecord': false,
                    'copyFromParent': true,
                    'autoCreate': false
                }
            };

            var panels = [{
                fields: [
                    {'name': 'name', 'type': 'hint-accounts-search-dropdown'},
                    {'type': 'badge', 'name': 'is_escalated'},
                    {'name': 'hint_account_pic', 'type': 'hint-accounts-logo'}
                ]
            }];

            var moduleFields = {
                name: {'name': 'name','type': 'hint-accounts-search-dropdown'},
                is_escalated: {'type': 'badge', 'name': 'is_escalated'},
                hint_account_pic: {'name': 'hint_account_pic', 'type': 'hint-accounts-logo'}
            };

            var action = new app.actions.CreateRecord(def);

            var getViewSpy = sandbox.stub(app.metadata, 'getView').returns({panels: panels});
            var getModuleSpy = sandbox.stub(app.metadata, 'getModule').returns(moduleFields);

            var fields = action._getRecordViewFields('Accounts');

            expect(fields.length).toEqual(3);
            expect(getViewSpy).toHaveBeenCalled();
            expect(getModuleSpy).toHaveBeenCalled();
        });
    });
});

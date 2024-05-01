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
describe('View.Views.Base.DataPrivacy.MarkForErasureView', function() {
    var view;
    var app;
    var model;
    var parentModel;
    var cloneModel;
    var mockData = [
        {
            field_name: 'first_name',
            value: 'Bob',
            date_modified: '2018-01-24T12:44:58-08:00',
            source: {
                type: 'user',
                module: 'Users',
                id: '1',
                name: 'Max Jensen',
                first_name: 'Max',
                last_name: 'Jensen'
            }
        },
        {
            field_name: 'last_name',
            value: 'Belcher',
            date_modified: '2017-01-20T12:44:58-08:00',
            source: {
                type: 'pmse_process',
                module: 'pmse_Inbox',
                id: 'pid1',
                pmse_project_id: 'ppid1',
                name: 'My Process',
            }
        },
        {
            field_name: 'phone_office',
            value: '555-555-5555',
            date_modified: '2018-01-23T02:44:58-08:00',
            source: {
                type: 'markto',
            }
        },
        {
            field_name: 'email',
            value: {id: 'emailId1', email_address: 'foo@example.com'},
            date_modified: '2018-01-23T12:44:58-08:00',
            source: {
                type: 'user',
                module: 'Users',
                id: '1',
                name: 'Max Jensen',
                first_name: 'Max',
                last_name: 'Jensen'
            }
        },
        {
            field_name: 'email',
            value: {id: 'emailId2', email_address: 'bar@example.net'},
            date_modified: '2018-01-23T12:44:58-08:00',
            source: {
                type: 'markto'
            }
        }
    ];
    var fieldsToErase;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'filtered-list');
        SugarTest.loadComponent('base', 'view', 'pii');
        SugarTest.loadComponent('base', 'view', 'mark-for-erasure', 'DataPrivacy');

        SugarTest.seedMetadata();
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('mark-for-erasure', {
            'panels': [
                {
                    name: 'primary',
                    fields: [
                        {
                            type: 'piiname',
                            name: 'field_name',
                            label: 'LBL_DATAPRIVACY_FIELDNAME',
                            sortable: true,
                            filter: 'contains',
                        },
                        {
                            type: 'base',
                            name: 'value',
                            label: 'LBL_DATAPRIVACY_VALUE',
                            sortable: true,
                            filter: 'contains',
                        },
                        {
                            type: 'base',
                            name: 'created_by_username',
                            label: 'LBL_DATAPRIVACY_CHANGED_BY',
                            sortable: false,
                        },
                        {
                            type: 'datetimecombo',
                            name: 'date_modified',
                            label: 'LBL_DATAPRIVACY_CHANGE_DATE',
                            sortable: false,
                        }
                    ],
                }
            ],
        }, 'DataPrivacy');
        SugarTest.testMetadata.set();

        app = SUGAR.App;
        app.drawer = {
            close: sinon.stub()
        };
        app.router = {
            reset: sinon.stub()
        };

        var modelForErase = app.data.createBean('Contacts', {first_name: 'Bob', last_name: 'Wombat'});
        modelForErase.link = {name: 'contacts'};
        modelForErase.set('id', 5);

        parentModel = app.data.createBean('DataPrivacy', {name: 'DP Request', type: 'Request to Erase Information'});
        parentModel.set('id', 10);

        var parentContext = new app.Context({
            model: parentModel
        });
        var context = parentContext.getChildContext({
            modelForErase: modelForErase
        });

        view = SugarTest.createView('base', 'DataPrivacy', 'mark-for-erasure', null, context, 'DataPrivacy');

        fieldsToErase = {
            contacts: {
                '5': [
                    'first_name'
                ]
            }
        };
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        delete app.drawer;
        delete app.router;
        app = null;
        sinon.restore();
    });

    describe('rendering the collection', function() {
        it('should render on collection reset', function() {
            view.collection = app.data.createBeanCollection('MarkForErasure', mockData);
            view._renderData();
            var types = ['varchar', 'varchar', 'base', 'base', 'base'];
            _.each(view.collection.models, function(model, index) {
                var fields = model.fields;
                var value = _.findWhere(fields, {name: 'value'});
                expect(fields.length).toEqual(4);
                expect(value.type).toEqual(types[index]);
            });
        });
    });

    describe('MarkForErasureCollection', function() {
        describe('sync', function() {
            it('should make a call to the PII endpoint and translate retrieved records to fields', function() {
                var url = 'rest/v11/Contacts/5/pii';
                var attributes = {key: 'value'};
                var dummySyncCallbacks = {
                    success: $.noop,
                    error: $.noop,
                    complete: $.noop,
                    abort: $.noop
                };

                sinon.stub(app.api, 'buildURL').returns(url);
                sinon.stub(app.data, 'getSyncCallbacks').returns(dummySyncCallbacks);
                var defaultSuccessCallbackStub = sinon.stub();
                sinon.stub(app.data, 'getSyncSuccessCallback').returns(defaultSuccessCallbackStub);

                var callStub = sinon.stub(app.api, 'call');
                var dummyFields = [{dummy: 'field'}];
                callStub.yieldsTo('success', {fields: dummyFields});
                sinon.stub(view, 'mergePiiFields').returns(dummyFields);
                view.collection.sync('read', model, {attributes: attributes, params: {}});
                expect(callStub).toHaveBeenCalledWith(
                    'read',
                    url,
                    attributes
                );

                expect(defaultSuccessCallbackStub).toHaveBeenCalledWith({
                    fields: dummyFields,
                    records: dummyFields
                });
            });
        });
    });

    describe('Mass Collection initialization', function() {
        it('should initialize on collection sync', function() {
            var triggerStub = sinon.stub(view.context, 'trigger');
            var addStub = sinon.stub(view.massCollection, 'add');
            parentModel.set('fields_to_erase', fieldsToErase);

            var bean1 = app.data.createBean('MarkForErasureView', mockData[0]);
            view.collection.add(bean1);
            view.collection.trigger('sync');

            expect(addStub).toHaveBeenCalledWith([bean1]);
            expect(triggerStub).toHaveBeenCalledWith('markforerasure:masscollection:init');
        });
    });

    describe('Marking for erasure', function() {
        it('should update the fields_to_erase and then save the record', function() {
            var bean1 = app.data.createBean('MarkForErasureView', mockData[0]);
            var saveStub = sinon.stub(view, 'saveRecord');
            view.massCollection.reset();
            view.massCollection.add([bean1]);

            view.context.trigger('markforerasure:mark');

            expect(saveStub).toHaveBeenCalledWith({
                id: 10,
                fields_to_erase: fieldsToErase
            });
        });
    });

    describe('saveRecord', function() {
        var saveStub;
        var attributes;

        beforeEach(function() {
            attributes = {
                id: 10,
                fields_to_erase: fieldsToErase
            };
            cloneModel = app.data.createBean('DataPrivacy', attributes);
            sinon.stub(parentModel, 'clone').returns(cloneModel);
        });

        it('should save just the fields_to_erase (and the id) and close the drawer', function() {
            saveStub = sinon.stub(cloneModel, 'save').yieldsTo('success');
            view.saveRecord(attributes);
            expect(saveStub.getCall(0).args[0]).toEqual(attributes);
            expect(app.drawer.close).toHaveBeenCalled();
        });

        describe('clone sync', function() {
            it('should trigger data:sync:start and sync just the desired attributes', function() {
                // sync testing
                sinon.stub(app.data, 'getSyncCallbacks').returns({
                    success: sinon.stub()
                });
                sinon.stub(app.data, 'parseOptionsForSync').returns({params: {a: 'a'}});

                sinon.stub(cloneModel, 'save').callsFake(function() {
                    cloneModel.sync('update', cloneModel);
                });

                var appDataTriggerStub = sinon.stub(app.data, 'trigger');
                var modelTriggerStub = sinon.stub(cloneModel, 'trigger');
                var recordsStub = sinon.stub(app.api, 'records');

                view.saveRecord(attributes);

                expect(appDataTriggerStub).toHaveBeenCalledWith('data:sync:start');
                expect(modelTriggerStub).toHaveBeenCalledWith('data:sync:start');
                expect(recordsStub.getCall(0).args[2]).toEqual(attributes);
            });
        });

        describe('error handling', function() {
            var saveStub;

            beforeEach(function() {
                saveStub = sinon.stub(cloneModel, 'save');
            });

            it('should retry the request on 412 errors', function() {
                var error = {
                    status: 412,
                    request: {
                        execute: sinon.stub(),
                        metadataRetry: false
                    }
                };
                saveStub.yieldsTo('error', cloneModel, error);
                sinon.stub(app.api, 'getMetadataHash').returns('new-hash');

                view.saveRecord(attributes);

                app.trigger('app:sync:complete');
                expect(error.request.execute).toHaveBeenCalledWith(null, 'new-hash');
            });

            it('should handle all other error types', function() {
                var error = {
                    status: 500
                };
                saveStub.yieldsTo('error', cloneModel, error);
                var alertStub = sinon.stub(app.alert, 'show');

                view.saveRecord(attributes);

                expect(alertStub).toHaveBeenCalledWith('error_while_save');
            });
        });
    });

    describe('Mass Collection event handling', function() {
        var bean1;
        var bean2;
        var bean3;
        var triggerStub;

        beforeEach(function() {
            view.massCollection = app.data.createBeanCollection('MarkForErasureView');
            view.collection = app.data.createBeanCollection('MarkForErasureView');
            bean1 = app.data.createBean('MarkForErasureView', mockData[0]);
            bean1.set('id', 1);

            bean2 = app.data.createBean('MarkForErasureView', mockData[1]);
            bean3 = app.data.createBean('MarkForErasureView', mockData[2]);

            triggerStub = sinon.stub(view.massCollection, 'trigger');
        });

        afterEach(function() {
            view.massCollection.reset();
            view.collection.reset();
            bean1 = null;
        });

        describe('mass_collection:add', function() {
            it('should add the specified model(s) to the mass collection', function() {
                var addStub = sinon.stub(view.massCollection, 'add');
                view.context.trigger('mass_collection:add', bean1);
                expect(addStub).toHaveBeenCalledWith([bean1]);

                var beans = [bean2, bean3];
                view.context.trigger('mass_collection:add', beans);
                expect(addStub).toHaveBeenCalledWith(beans);
            });

            it('should trigger all:checked if all the models are checked', function() {
                view.collection.add(bean1);
                view.context.trigger('mass_collection:add', bean1);
                expect(triggerStub).toHaveBeenCalledWith('all:checked');
            });
        });

        describe('mass_collection:add:all', function() {
            it('should add all models from the collection into the mass collection', function() {
                var resetStub = sinon.stub(view.massCollection, 'reset');
                view.context.trigger('mass_collection:add:all');
                expect(resetStub).toHaveBeenCalledWith(view.collection.models);
                expect(triggerStub).toHaveBeenCalledWith('all:checked');
            });
        });

        describe('mass_collection:remove', function() {
            it('should remove the specified model(s) from the mass collection and trigger not:all:checked', function() {
                var removeStub = sinon.stub(view.massCollection, 'remove');

                view.context.trigger('mass_collection:remove', bean1);
                expect(removeStub).toHaveBeenCalledWith([bean1]);
                expect(triggerStub).toHaveBeenCalledWith('not:all:checked');

                var beans = [bean2, bean3];
                view.context.trigger('mass_collection:remove', beans);
                expect(removeStub).toHaveBeenCalledWith(beans);
                expect(triggerStub).toHaveBeenCalledWith('not:all:checked');
            });
        });

        describe('mass_collection:remove:all and mass_collection:clear', function() {
            using('different events', ['mass_collection:remove:all', 'mass_collection:clear'], function(event) {
                it('should reset the mass collection and trigger not:all:checked', function() {
                    var resetStub = sinon.stub(view.massCollection, 'reset');
                    view.context.trigger(event);
                    expect(triggerStub).toHaveBeenCalledWith('not:all:checked');
                });
            });
        });
    });
});

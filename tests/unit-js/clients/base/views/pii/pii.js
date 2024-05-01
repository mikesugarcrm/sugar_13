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
describe('View.Views.Base.PiiView', function() {
    var view;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'filtered-list');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('pii', {
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
        });
        SugarTest.testMetadata.set();

        app = SUGAR.App;
        var context = new app.Context({
            module: 'Contacts',
            modelId: '5'
        });
        var childContext = context.getChildContext();
        childContext.set('pModule', 'Contacts');
        childContext.set('pId', '5');
        view = SugarTest.createView('base', null, 'pii', null, childContext);
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('loadData', function() {
        var oldFetched;
        var fetchStub;

        beforeEach(function() {
            oldFetched = view.collection.dataFetched;
            fetchStub = sinon.stub(view.collection, 'fetch');
        });

        afterEach(function() {
            view.collection.dataFetched = oldFetched;
        });

        it('should not fetch the collection if the data has already been fetched', function() {
            view.collection.dataFetched = true;
            view.loadData();
            expect(fetchStub).not.toHaveBeenCalled();
        });

        it('should fetch the collection if the data has not yet been fetched', function() {
            view.collection.dataFetched = false;
            view.loadData();
            expect(fetchStub).toHaveBeenCalled();
        });
    });

    describe('rendering the collection', function() {
        it('should render on collection reset', function() {
            view.collection = app.data.createBeanCollection('Pii', [
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
                    value: 'foo@example.com',
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
                    value: 'bar@example.net',
                    date_modified: '2018-01-23T12:44:58-08:00',
                    source: {
                        type: 'markto'
                    }
                }
            ]);
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

    describe('PiiCollection', function() {
        describe('sync', function() {
            it('should call the PII endpoint and translate retrieved fields to records', function() {
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
                view.collection.sync('read', app.data.createBean(), {attributes: attributes, params: {}});
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

    describe('mergePiiFields', function() {
        var records;
        var getModuleStub;

        beforeEach(function() {
            records = [
                {
                    field_name: 'field1',
                    value: 'value1'
                },
                {
                    field_name: 'email',
                    value: {id: 'emailId1', email_address: 'a@a.com'}
                },
                {
                    field_name: 'email',
                    value: {id: 'emailId2', email_address: 'b@b.com'}
                },
            ];

            getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    field1: {name: 'field1', pii: true},
                    field2: {name: 'field2', pii: true},
                    email: {name: 'email', pii: true},
                };
            });
        });

        it('should combine module PII fields with response fields', function() {
            var actual = view.mergePiiFields(records);
            expect(actual[0].field_name).toEqual('field1');
            expect(actual[1].field_name).toEqual('field2');
            expect(actual[2].field_name).toEqual('email');
            expect(actual[2].value.id).toEqual('emailId1');
            expect(actual[3].field_name).toEqual('email');
            expect(actual[3].value.id).toEqual('emailId2');
        });
    });

    describe('applyDataToRecords', function() {
        var records;

        beforeEach(function() {
            records = [
                {
                    field_name: 'field1',
                    value: 'value1'
                },
                {
                    field_name: 'field2',
                    value: 'value2'

                }
            ];
        });

        it('should apply ACLs to records', function() {
            var data = {
                records: records,
                _acl: {
                    fields: {
                        field1: {read: 'no', write: 'no', create: 'no'}
                    }
                }
            };
            view.applyDataToRecords(data);
            expect(data.records[0]._acl).toEqual({fields: {value: {read: 'no', write: 'no', create: 'no'}}});
            expect(data.records[1]._acl).toBeUndefined();
        });

        it('should apply the erased field list to records', function() {
            var data = {
                records: records,
                _erased_fields: ['field2']
            };
            view.applyDataToRecords(data);
            expect(data.records[0]._erasedFields).toBeUndefined();
            expect(data.records[1]._erased_fields).toEqual(['value']);
        });
    });
});

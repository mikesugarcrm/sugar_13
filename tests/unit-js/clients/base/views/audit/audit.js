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
describe('View.Views.Base.AuditView', function() {
    var view;
    var app;
    var model;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'filtered-list');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('audit', {
            'panels': [
                {
                    name: 'primary',
                    fields: [
                        {
                            type: 'fieldtype',
                            name: 'field_name',
                            label: 'LBL_FIELD_NAME',
                            sortable: true,
                            filter: 'startswith',
                        },
                        {
                            type: 'base',
                            name: 'before',
                            label: 'LBL_OLD_NAME',
                            sortable: false,
                            filter: 'contains',
                        },
                        {
                            type: 'base',
                            name: 'after',
                            label: 'LBL_NEW_VALUE',
                            sortable: false,
                            filter: 'contains',
                        },
                        {
                            type: 'base',
                            name: 'created_by_username',
                            label: 'LBL_CREATED_BY',
                            sortable: false,
                        },
                        {
                            type: 'datetimecombo',
                            name: 'date_modified',
                            label: 'LBL_LIST_DATE',
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
            model: app.data.createBean('Contacts'),
            modelId: '5'
        });
        var childContext = context.getChildContext();
        view = SugarTest.createView('base', null, 'audit', null, childContext);
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('_applyModelDataOnRecords', function() {
        var records;

        beforeEach(function() {
            records = [
                {
                    // normal change
                    field_name: 'field1',
                    before: 'value1a',
                    after: 'value1b'
                },
                {
                    // erasure of scalar field
                    field_name: 'field2',
                    before: null,
                    after: null
                },
                {
                    // erasure of an email address
                    field_name: 'email',
                    before: null,
                    after: 'erased-id'
                }
            ];
        });

        it('should apply the erased field list to records', function() {
            var model = app.data.createBean('Contacts');
            model.set('_erased_fields', ['field2', {field_name: 'email', id: 'erased-id'}]);
            view._applyModelDataOnRecords(model, records);
            expect(records[0]._erased_fields).toBeUndefined();
            expect(records[1]._erased_fields).toEqual(['before', 'after']);
            expect(records[2]._erased_fields).toEqual(['after']);
        });
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

    describe('Audit Collection', function() {
        describe('sync', function() {
            it('should call the PII endpoint and translate retrieved fields to records', function() {
                var url = 'rest/v11_1/Contacts/5/audit';
                var attributes = {key: 'value'};
                var dummySyncCallbacks = {
                    success: $.noop,
                    error: $.noop,
                    complete: $.noop,
                    abort: $.noop
                };

                // FIXME PX-46: change view.collection to app.api
                sinon.stub(view.collection, 'buildURL').returns(url);
                sinon.stub(app.data, 'getSyncCallbacks').returns(dummySyncCallbacks);
                var applyModelDataOnRecordsStub = sinon.stub(view, '_applyModelDataOnRecords');
                var defaultSuccessCallbackStub = sinon.stub();
                sinon.stub(app.data, 'getSyncSuccessCallback').returns(defaultSuccessCallbackStub);

                var callStub = sinon.stub(app.api, 'call');
                var dummyRecords = [{before: 'a', after: 'b', field_name: 'myfield'}];
                callStub.yieldsTo('success', {records: dummyRecords});

                var model = view.context.parent.get('model');
                view.context.set('model', model);

                view.collection.sync('read', model, {attributes: attributes, params: {}});

                expect(callStub).toHaveBeenCalledWith(
                    'read',
                    url,
                    attributes
                );

                expect(applyModelDataOnRecordsStub).toHaveBeenCalledWith(
                    view.context.get('model'),
                    dummyRecords
                );
                expect(defaultSuccessCallbackStub).toHaveBeenCalledWith({
                    records: dummyRecords
                });
            });
        });
    });
});

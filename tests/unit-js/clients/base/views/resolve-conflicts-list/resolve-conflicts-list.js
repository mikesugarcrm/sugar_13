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
describe('Resolve Conflicts List View', function() {
    var view, app,
        module = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadComponent('base', 'view', 'flex-list');
        view = SugarTest.createView('base', module, 'resolve-conflicts-list');
    });

    afterEach(function() {
        view.dispose();

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('_populateMissingDataFromDatabase', function() {
        it('should populate missing data from the database data', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module);

            databaseModel.set('foo', 'bar');

            view._populateMissingDataFromDatabase(clientModel, databaseModel);

            expect(clientModel.get('foo')).toBe('bar');
        });

        it('should not copy over from the database data if the value already exists in the client bean', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module);

            clientModel.set('one', 'foo');
            databaseModel.set('one', 'bar');

            view._populateMissingDataFromDatabase(clientModel, databaseModel);

            expect(clientModel.get('one')).toBe('foo');
        });

        it('should copy over from the database data if the value exists but has not changed from the default value', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module);

            clientModel._defaults = {
                one: 'foo'
            };

            clientModel.set('one', 'foo');
            databaseModel.set('one', 'bar');

            view._populateMissingDataFromDatabase(clientModel, databaseModel);

            expect(clientModel.get('one')).toBe('bar');
        });

        it('should not copy over from the database data if the value exists and has changed from the default value', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module);

            clientModel._defaults = {
                one: '123'
            };

            clientModel.set('one', 'foo');
            databaseModel.set('one', 'bar');

            view._populateMissingDataFromDatabase(clientModel, databaseModel);

            expect(clientModel.get('one')).toBe('foo');
        });
    });

    describe('_getFieldViewDefinition', function() {
        it('should get record view field view definition', function() {
            SugarTest.testMetadata.addViewDefinition('record', {
                "panels": [{
                    "fields":[{
                        "name":"first_name"
                    }, {
                        "name":"last_name"
                    }, {
                        "name":"phone"
                    }]
                }]
            }, module);

            var actual = view._getFieldViewDefinition(['first_name', 'phone']);

            expect(actual.length).toBe(2);
            expect(actual[0].name).toBe('first_name');
            expect(actual[1].name).toBe('phone');
        });

        it('should get record view field view definition in fieldsets', function() {
            SugarTest.testMetadata.addViewDefinition('record', {
                "panels": [{
                    "fields":[{
                        "fields":[{
                            "name":"first_name"
                        }, {
                            "name":"last_name"
                        }]
                    }, {
                        "name":"phone"
                    }]
                }]
            }, module);

            var actual = view._getFieldViewDefinition(['first_name', 'phone']);

            expect(actual.length).toBe(2);
            expect(actual[0].name).toBe('first_name');
            expect(actual[1].name).toBe('phone');
        });
    });

    describe('_buildFieldDefinitions', function() {
        it('should build columns based on record view field definitions', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module),
                getFieldViewDefinitionStub = sinon.stub(view, '_getFieldViewDefinition').callsFake(function() {
                    return [{
                        name: 'first_name'
                    }, {
                        name: 'last_name'
                    }]
                });

            view._buildFieldDefinitions(clientModel, databaseModel);

            expect(view._fields.visible.length).toBe(3);
            expect(view._fields.all.length).toBe(3);

            getFieldViewDefinitionStub.restore();
        });

        it('should have modified by column as the first column', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module),
                getFieldViewDefinitionStub = sinon.stub(view, '_getFieldViewDefinition').callsFake(function() {
                    return [{
                        name: 'first_name'
                    }, {
                        name: 'last_name'
                    }]
                });

            view._buildFieldDefinitions(clientModel, databaseModel);

            expect(view._fields.visible[0].name).toBe('_modified_by');
            expect(view._fields.all[0].name).toBe('_modified_by');

            getFieldViewDefinitionStub.restore();
        });

        it('should remove modified by name column', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module),
                getFieldViewDefinitionStub = sinon.stub(view, '_getFieldViewDefinition').callsFake(function() {
                    return []
                });

            clientModel.set('modified_by_name', 'foo');
            databaseModel.set('modified_by_name', 'bar');

            view._buildFieldDefinitions(clientModel, databaseModel);

            expect(_.isArray(getFieldViewDefinitionStub.args[0][0])).toBe(true);
            expect(getFieldViewDefinitionStub.args[0][0].length).toBe(0);

            getFieldViewDefinitionStub.restore();
        });

        it('should set all columns to not sort', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module),
                getFieldViewDefinitionStub = sinon.stub(view, '_getFieldViewDefinition').callsFake(function() {
                    return [{
                        name: 'first_name'
                    }, {
                        name: 'last_name'
                    }];
                });

            view._buildFieldDefinitions(clientModel, databaseModel);

            expect(view._fields.visible[0].sortable).toBe(false);
            expect(view._fields.visible[1].sortable).toBe(false);
            expect(view._fields.visible[2].sortable).toBe(false);

            getFieldViewDefinitionStub.restore();
        });

        it('should hide date modified column', function() {
            var clientModel = app.data.createBean(module),
                databaseModel = app.data.createBean(module),
                getFieldViewDefinitionStub = sinon.stub(view, '_getFieldViewDefinition').callsFake(function() {
                    return [{
                        name: 'first_name'
                    }, {
                        name: 'last_name'
                    }, {
                        name: 'date_modified'
                    }]
                });

            view._buildFieldDefinitions(clientModel, databaseModel);

            expect(view._fields.visible.length).toBe(3);
            expect(view._fields.all.length).toBe(4);

            expect(view._fields.visible[0].selected).toBe(true);
            expect(view._fields.visible[1].selected).toBe(true);
            expect(view._fields.visible[2].selected).toBe(true);

            getFieldViewDefinitionStub.restore();
        });
    });

    describe('_buildList', function() {
        it('should have two rows of data', function() {
            app.data.declareModels();

            var clientModel = app.data.createBean(module, {
                    id: 1
                }),
                dataInDatabase = {
                    id: 1
                },
                buildFieldDefinitionsStub = sinon.stub(view, '_buildFieldDefinitions'),
                populateMissingDataFromDatabaseStub = sinon.stub(view, '_populateMissingDataFromDatabase');

            view.context.set('modelToSave', clientModel);
            view.context.set('dataInDb', dataInDatabase);

            view._buildList();

            expect(view.collection.length).toBe(2);

            buildFieldDefinitionsStub.restore();
            populateMissingDataFromDatabaseStub.restore();
        });

        it('should change the models to have different IDs', function() {
            app.data.declareModels();

            var clientModel = app.data.createBean(module, {
                    id: 1
                }),
                dataInDatabase = {
                    id: 1
                },
                buildFieldDefinitionsStub = sinon.stub(view, '_buildFieldDefinitions'),
                populateMissingDataFromDatabaseStub = sinon.stub(view, '_populateMissingDataFromDatabase');

            view.context.set('modelToSave', clientModel);
            view.context.set('dataInDb', dataInDatabase);

            view._buildList();

            expect(view.collection.at(0).id).toBe('1-client');
            expect(view.collection.at(1).id).toBe('1-database');

            buildFieldDefinitionsStub.restore();
            populateMissingDataFromDatabaseStub.restore();
        });

        it('should indicate which models are which', function() {
            app.data.declareModels();

            var clientModel = app.data.createBean(module, {
                    id: 1
                }),
                dataInDatabase = {
                    id: 1
                },
                buildFieldDefinitionsStub = sinon.stub(view, '_buildFieldDefinitions'),
                populateMissingDataFromDatabaseStub = sinon.stub(view, '_populateMissingDataFromDatabase');

            view.context.set('modelToSave', clientModel);
            view.context.set('dataInDb', dataInDatabase);

            view._buildList();

            expect(view.collection.at(0).get('_dataOrigin')).toBe('client');
            expect(view.collection.at(1).get('_dataOrigin')).toBe('database');

            buildFieldDefinitionsStub.restore();
            populateMissingDataFromDatabaseStub.restore();
        });

        it('should populate _modified_by column', function() {
            app.data.declareModels();

            var clientModel = app.data.createBean(module, {
                    id: 1
                }),
                dataInDatabase = {
                    id: 1,
                    modified_by_name: 'foo'
                },
                buildFieldDefinitionsStub = sinon.stub(view, '_buildFieldDefinitions'),
                populateMissingDataFromDatabaseStub = sinon.stub(view, '_populateMissingDataFromDatabase');

            view.context.set('modelToSave', clientModel);
            view.context.set('dataInDb', dataInDatabase);

            view._buildList();

            expect(view.collection.at(0).get('_modified_by')).toBe('LBL_YOU');
            expect(view.collection.at(1).get('_modified_by')).toBe('foo');

            buildFieldDefinitionsStub.restore();
            populateMissingDataFromDatabaseStub.restore();
        });

        it('should not use modelToSave in the collection but instead use a copy of it', function() {
            app.data.declareModels();

            var clientModel = app.data.createBean(module, {
                    id: 1
                }),
                dataInDatabase = {
                    id: 1
                },
                buildFieldDefinitionsStub = sinon.stub(view, '_buildFieldDefinitions'),
                populateMissingDataFromDatabaseStub = sinon.stub(view, '_populateMissingDataFromDatabase');

            view.context.set('modelToSave', clientModel);
            view.context.set('dataInDb', dataInDatabase);

            view._buildList();

            expect(view.collection.at(0)).not.toBe(clientModel);

            buildFieldDefinitionsStub.restore();
            populateMissingDataFromDatabaseStub.restore();
        });
    });
});

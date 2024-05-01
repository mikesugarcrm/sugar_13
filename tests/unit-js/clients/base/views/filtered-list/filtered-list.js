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
describe('Base.View.FilteredListView', function() {

    var view, app, parentLayout;

    beforeEach(function() {
        app = SUGAR.App;
        parentLayout = app.view.createLayout({type: 'base'});
        SugarTest.loadComponent('base', 'view', 'list');
        view = SugarTest.createView('base', 'Accounts', 'filtered-list', {}, false, false, parentLayout);
        view.collection = new Backbone.Collection();
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        parentLayout.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        parentLayout = null;
    });

    it('should initiate the searchable fields', function() {
        var fields = {
            'field_name': {
                name: 'field_name',
                filter: 'startsWith'
            },
            'date_created': {
                name: 'date_created',
                filter: 'endsWith'
            },
            'name': {
                name: 'name',
                filter: 'contains'
            },
            'no_filter': {
                name: 'no_filter'
            }
        };
        sinon.stub(view, 'getFields').callsFake(function() {
            return fields;
        });
        view._initFilter();
        var actual = view._filter,
            expected = ['field_name', 'date_created', 'name'];
        expect(_.size(actual)).toBe(_.size(expected));
        _.each(actual, function(field, index) {
            expect(field.name).toBe(expected[index]);
        }, this);
    });

    describe('Search filter', function() {
        beforeEach(function() {
            var collection = [
                {
                    'id': '1',
                    'field_name': '123 abc',
                    'value': '11 112 34a bc'
                },
                {
                    'id': '2',
                    'field_name': ' 12',
                    'value': '1234'
                },
                {
                    'id': '3',
                    'field_name': '23ab12',
                    'value': 'ab1234as'
                },
                {
                    'id': '4',
                    'field_name': 'tab',
                    'value': 'Foo boo'
                }
            ];
            view.collection = new Backbone.Collection(collection);
        });
        using('Available filters', [
            {
                fields: {
                    'field_name': {
                        name: 'field_name',
                        filter: 'startsWith'
                    }
                },
                term: '12',
                expected: ['1']
            },
            {
                fields: {
                    'field_name': {
                        name: 'value',
                        filter: 'contains'
                    }
                },
                term: '123',
                expected: ['2', '3']
            },
            {
                fields: {
                    'field_name': {
                        name: 'field_name',
                        filter: 'endsWith'
                    }
                },
                term: 'ab',
                expected: ['4']
            }
        ], function(value) {
            it('should filter the collection that matches search term', function() {

                sinon.stub(view, 'getFields').callsFake(function() {
                    return value.fields;
                });
                view._initFilter();
                view.searchTerm = value.term;
                view.filterCollection();

                var actual = view.filteredCollection;
                expect(_.size(actual)).toBe(_.size(value.expected));
                _.each(value.expected, function(expectedValue) {
                    var filteredModel = _.find(actual, function(model) {return model.id == expectedValue;});
                    expect(filteredModel).toBeDefined();
                }, this);
            });
        });
    });
});

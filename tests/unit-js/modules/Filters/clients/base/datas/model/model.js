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
describe('Data.Base.FiltersBean', function() {
    var app, filter, prototype, filterModuleName = 'Accounts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.declareData('base', 'Filters');

        prototype = app.data.getBeanClass('Filters').prototype;
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        filter = null;
    });

    describe('getFilterableFields', function() {

        var varDefs = {}, filterDefs = {};

        beforeEach(function() {
            sinon.stub(app.metadata, 'getFilterOperators').returns({
                'text': {},
                'date': {},
                'varchar': {}
            });
            sinon.stub(app.metadata, 'getModule')
                .withArgs(filterModuleName).returns(
                {
                    fields: varDefs,
                    filters: {
                        'default': {
                            meta: {
                                default_filter: 'all_records',
                                fields: filterDefs
                            }
                        }
                    }
                }
            );
        });

        it('should extend the vardefs with the filter defs', function() {
            varDefs.name = {
                name: 'name',
                type: 'varchar',
                vname: 'LBL_ACCOUNT_NAME',
                len: 100
            };
            filterDefs.name = {
                vname: 'LBL_CUSTOM_ACCOUNT_NAME'
            };

            var fields = prototype.getFilterableFields(filterModuleName);
            expect(fields.name).toEqual({
                name: 'name',
                type: 'varchar',
                vname: 'LBL_CUSTOM_ACCOUNT_NAME',
                len: 100
            });
        });

        it('should validate that an operator is available for this field', function() {
            varDefs.date_modified = {
                name: 'date_modified',
                options: 'date_range_search_dom',
                type: 'datetime',
                vname: 'LBL_DATE_MODIFIED'
            };
            varDefs.another = {
                name: 'another',
                type: 'invalidType'
            };
            filterDefs.date_modified = {};
            filterDefs.another = {};

            var fields = prototype.getFilterableFields(filterModuleName);
            expect(fields.date_modified).toBeDefined();
            expect(fields.another).toBeUndefined();
        });

        it('should return predefined filters', function() {
            filterDefs.$favorite = {
                predefined_filter: true
            };
            var fields = prototype.getFilterableFields(filterModuleName);
            expect(fields.$favorite).toBeDefined();
        });

        it('should validate that the user has access to read this field', function() {
            varDefs.name = {
                name: 'name',
                type: 'varchar',
                vname: 'LBL_ACCOUNT_NAME',
                len: 100
            };
            filterDefs.name = {};

            sinon.stub(app.acl, 'hasAccess').returns(false);
            var fields = prototype.getFilterableFields(filterModuleName);
            expect(fields.name).toBeUndefined();
        });
    });

    describe('populateFilterDefinition', function() {

        using('different filters', [
            // no populate object
            {
                filterDef: [
                    {'aField': {$in: ['aValue']}}
                ],
                expectedFilterDef: [
                    {'aField': {$in: ['aValue']}}
                ]
            },
            // value is not empty
            {
                filterDef: [
                    {'aField': 'aValue'}
                ],
                populateObj: {'aField': 'anotherValue'},
                expectedFilterDef: [
                    {'aField': 'aValue'}
                ]
            },
            // value is not empty and is a number
            {
                filterDef: [
                    {'aField': 1}
                ],
                populateObj: {'aField': 2},
                expectedFilterDef: [
                    {'aField': 1}
                ]
            },
            // value is empty
            {
                filterDef: [
                    {'aField': {$in: []}}
                ],
                populateObj: {'aField': ['aValue']},
                expectedFilterDef: [
                    {'aField': {$in: ['aValue']}}
                ]
            },
            // value is empty and operator is $equals
            {
                filterDef: [
                    {'aField': ''}
                ],
                populateObj: {'aField': 'aValue'},
                expectedFilterDef: [
                    {'aField': 'aValue'}
                ]
            }
        ], function(dataSet) {
            it('should parse the filter definition and fill empty values', function() {
                var filterDef = prototype.populateFilterDefinition(dataSet.filterDef, dataSet.populateObj);
                expect(filterDef).toEqual(dataSet.expectedFilterDef);
            });
        });
    });

    describe('getModuleQuickSearchMeta', function() {

        var filtersMetadata = {};

        beforeEach(function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs(filterModuleName).returns({
                    filters: filtersMetadata
                });
            filtersMetadata.meta1 = {
                'meta': {
                    'quicksearch_field': 'test1',
                    'quicksearch_priority': 0
                }
            };
            sinon.spy(prototype, '_getQuickSearchMetaByPriority');
        });

        using('different metadata', [
            {
                templateObjects: {
                    meta2: {
                        meta: {
                            'quicksearch_field': ['test2'],
                            'quicksearch_priority': 3
                        }
                    },
                    meta3: {
                        meta: {
                            'quicksearch_field': ['test3'],
                            'quicksearch_priority': 2
                        }
                    }
                },
                expected: {
                    fieldNames: ['test2'],
                    splitTerms: false
                }
            },
            {
                templateObjects: {
                    meta2: {
                        meta: {
                            'quicksearch_field': ['first_name', 'last_name'],
                            'quicksearch_split_terms': true,
                            'quicksearch_priority': 3
                        }
                    }
                },
                expected: {
                    fieldNames: ['first_name', 'last_name'],
                    splitTerms: true
                }
            }
        ], function(dataSet) {
            it('should retrieve and cache the quick search metadata of a module', function() {
                filtersMetadata.meta2 = dataSet.templateObjects.meta2;
                filtersMetadata.meta3 = dataSet.templateObjects.meta3;

                var quickSearchMetadata = prototype.getModuleQuickSearchMeta(filterModuleName);
                expect(quickSearchMetadata).toEqual(dataSet.expected);
                expect(prototype._getQuickSearchMetaByPriority).toHaveBeenCalled();

                // verify it is saved in memory
                prototype._moduleQuickSearchMeta = prototype._moduleQuickSearchMeta || {};
                expect(prototype._moduleQuickSearchMeta[filterModuleName]).toBeDefined();

                // reset spy
                prototype._getQuickSearchMetaByPriority.resetHistory();

                // verify we get the metadata from memory
                quickSearchMetadata = prototype.getModuleQuickSearchMeta(filterModuleName);
                expect(quickSearchMetadata).toEqual(dataSet.expected);
                expect(prototype._getQuickSearchMetaByPriority).not.toHaveBeenCalled();
            });
        });
    });

    describe('buildSearchTermFilter', function() {
        using('different search terms to match a contact "Luis Filipe Madeira Caeiro Figo"', [
            {
                'case': 'First part of first name',
                searchValue: 'Luis',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Luis'}},
                        {last_name: {$starts: 'Luis'}}
                    ]
                }]
            }, {
                'case': 'First 2 parts of first name',
                searchValue: 'Luis Filipe',
                expectedFilter: [{
                    $or: [
                        {
                            $and: [
                                {first_name: {$starts: 'Luis'}},
                                {last_name: {$starts: 'Filipe'}}
                            ]
                        },
                        {first_name: {$starts: 'Luis Filipe'}},
                        {last_name: {$starts: 'Luis Filipe'}}
                    ]
                }]
            }, {
                'case': 'First name',
                searchValue: 'Luis Filipe Madeira',
                expectedFilter: [{
                    $or: [
                        {
                            $and: [
                                {first_name: {$starts: 'Luis'}},
                                {last_name: {$starts: 'Filipe Madeira'}}
                            ]
                        }, {
                            $and: [
                                {first_name: {$starts: 'Luis Filipe'}},
                                {last_name: {$starts: 'Madeira'}}
                            ]
                        },
                        {first_name: {$starts: 'Luis Filipe Madeira'}},
                        {last_name: {$starts: 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                'case': 'First part of last name',
                searchValue: 'Caeiro',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Caeiro'}},
                        {last_name: {$starts: 'Caeiro'}}
                    ]
                }]
            }, {
                'case': 'Last name',
                searchValue: 'Caeiro Figo',
                expectedFilter: [{
                    $or: [
                        {
                            $and: [
                                {first_name: {$starts: 'Caeiro'}},
                                {last_name: {$starts: 'Figo'}}
                            ]
                        },
                        {first_name: {$starts: 'Caeiro Figo'}},
                        {last_name: {$starts: 'Caeiro Figo'}}
                    ]
                }]
            }],
            function(test) {
                it('should search by ' + test.case, function() {
                    var filterDef;

                    sinon.stub(prototype, 'getModuleQuickSearchMeta')
                        .returns({fieldNames: [['first_name', 'last_name']]});

                    filterDef = prototype.buildSearchTermFilter('Contacts', test.searchValue);

                    expect(filterDef).toEqual(test.expectedFilter);
                });
            });

        using('different quicksearch_field metadata', [
            {
                'case': 'Undefined',
                meta: undefined,
                expectedFilter: []
            }, {
                'case': '1 Simple Field',
                meta: ['simpleField1'],
                expectedFilter: [{'simpleField1': {'$starts': 'Luis Filipe Madeira'}}]
            }, {
                'case': '2 Simple Fields',
                meta: ['simpleField1', 'simpleField2'],
                expectedFilter: [
                    {
                        '$or': [
                            {'simpleField1': {'$starts': 'Luis Filipe Madeira'}},
                            {'simpleField2': {'$starts': 'Luis Filipe Madeira'}}
                        ]
                    }
                ]
            }, {
                'case': '1 Split Term Field',
                meta: [['splitField1']],
                expectedFilter: [{'splitField1': {'$starts': 'Luis Filipe Madeira'}}]
            }, {
                'case': '1 Split Term Field composed of 2 Fields',
                meta: [['first_name', 'last_name']],
                expectedFilter: [{
                    $or: [
                        {
                            $and: [
                                {first_name: {$starts: 'Luis'}},
                                {last_name: {$starts: 'Filipe Madeira'}}
                            ]
                        }, {
                            $and: [
                                {first_name: {$starts: 'Luis Filipe'}},
                                {last_name: {$starts: 'Madeira'}}
                            ]
                        },
                        {first_name: {$starts: 'Luis Filipe Madeira'}},
                        {last_name: {$starts: 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                'case': '1 Simple Field, 1 Split Term Field',
                meta: ['simpleField1', ['splitField1']],
                expectedFilter: [{
                    '$or': [
                        {'simpleField1': {'$starts': 'Luis Filipe Madeira'}},
                        {'splitField1': {'$starts': 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                'case': '1 Simple Field, 1 Split Term Field composed of 2 Fields',
                meta: ['simpleField1', ['first_name', 'last_name']],
                expectedFilter: [{
                    '$or': [
                        {'simpleField1': {'$starts': 'Luis Filipe Madeira'}},
                        {
                            '$or': [
                                {
                                    $and: [
                                        {first_name: {$starts: 'Luis'}},
                                        {last_name: {$starts: 'Filipe Madeira'}}
                                    ]
                                },
                                {
                                    $and: [
                                        {first_name: {$starts: 'Luis Filipe'}},
                                        {last_name: {$starts: 'Madeira'}}
                                    ]
                                },
                                {first_name: {$starts: 'Luis Filipe Madeira'}},
                                {last_name: {$starts: 'Luis Filipe Madeira'}}
                            ]
                        }
                    ]
                }]
            }, {
                'case': '2 Split Term Fields',
                meta: [['first_name', 'last_name'], ['splitField3', 'splitField4']],
                expectedFilter: [{
                    $or: [
                        {
                            $and: [
                                {first_name: {$starts: 'Luis'}},
                                {last_name: {$starts: 'Filipe Madeira'}}
                            ]
                        }, {
                            $and: [
                                {first_name: {$starts: 'Luis Filipe'}},
                                {last_name: {$starts: 'Madeira'}}
                            ]
                        },
                        {first_name: {$starts: 'Luis Filipe Madeira'}},
                        {last_name: {$starts: 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                'case': '1 Split Term Field composed of 3 Fields',
                meta: [['splitField1', 'splitField2', 'splitField3']],
                expectedFilter: []
            }
        ], function(test) {
            var searchTerm = 'Luis Filipe Madeira';

            it('should be valid with ' + test.case, function() {
                var filterDef;

                sinon.stub(prototype, 'getModuleQuickSearchMeta')
                    .returns({fieldNames: test.meta});

                filterDef = prototype.buildSearchTermFilter('Accounts', searchTerm);

                expect(filterDef).toEqual(test.expectedFilter);
            });
        });

        it('should augment the filter of Users and Employees module', function() {
            sinon.stub(prototype, 'getModuleQuickSearchMeta').returns({
                fieldNames: ['first_name', 'last_name'],
                splitTerms: true
            });
            var filterDef = prototype.buildSearchTermFilter('Users', 'Test');
            expect(filterDef).toEqual([{
                $and: [
                    {status: {$not_equals: 'Inactive' }},
                    {$or: [
                        {first_name: {$starts: 'Test'}},
                        {last_name: {$starts: 'Test'}}
                    ]}
                ]
            }]);
        });

        it('should select the filter with the highest priority among multiple quick search filters', function() {
            var expectedFilterFields = ['first_name', 'last_name'];
            var unexpectedFilterFields = ['document_name', 'bazooka'];
            var expectedSearchTerm = 'Blah';

            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    filters: {
                        basic: {
                            meta: {
                                quicksearch_field: ['name']
                            },
                            quicksearch_priority: 1
                        },
                        person: {
                            meta: {
                                quicksearch_field: expectedFilterFields,
                                quicksearch_priority: 10 // Higher priority filter will be populated
                            }
                        },
                        _default: {
                            meta: {
                                quicksearch_field: unexpectedFilterFields,
                                quicksearch_priority: 2
                            }
                        }
                    }
                };
            });

            var actualFilter = prototype.buildSearchTermFilter('Accounts', expectedSearchTerm);
            _.each(actualFilter, function(filter) {
                expect(filter['$or']).toBeDefined();
                expect(filter['$or'].length).toBe(expectedFilterFields.length);
                _.each(filter['$or'], function(search_filter) {
                    _.each(search_filter, function(term, field) {
                        expect(_.indexOf(expectedFilterFields, field) >= 0).toBeTruthy();
                        expect(_.indexOf(unexpectedFilterFields, field) >= 0).toBeFalsy();
                        var actualTerm = term['$starts'];
                        expect(actualTerm).toBeDefined();
                        expect(actualTerm).toBe(expectedSearchTerm);
                    });
                });
            }, this);
        });
    });

    describe('combineFilterDefinitions', function() {

        using('different filters', [
            {
                expectedFilterDef: []
            },
            {
                baseFilter: [{status: {$not_equals: 'Inactive' }}],
                expectedFilterDef: [{status: {$not_equals: 'Inactive' }}]
            },
            {
                searchTermFilter: [{aField: {$starts: 'test' }}],
                expectedFilterDef: [{aField: {$starts: 'test' }}]
            },
            {
                baseFilter: [{status: {$not_equals: 'Inactive' }}],
                searchTermFilter: [{aField: {$starts: 'test' }}],
                expectedFilterDef: [{
                    $and: [
                        {status: {$not_equals: 'Inactive'}},
                        {aField: {$starts: 'test'}}
                    ]
                }]
            }
        ], function(dataSet) {
            it('should combine them and return a single filter', function() {
                var filterDef = prototype.combineFilterDefinitions(dataSet.baseFilter, dataSet.searchTermFilter);
                expect(filterDef).toEqual(dataSet.expectedFilterDef);
            });
        });
    });

    describe('_joinFilterDefs', function() {
        var sampleFilterDef1 = {'name': {'$starts': 'value'}};
        var sampleFilterDef2 = {
            '$or': [
                {'first_name': {'$starts': 'first name'}},
                {'last_name': {'$starts': 'last name'}}
            ]
        };

        it('should successfully join filter definitions passed in as individual parameters', function() {
            var filterDef = prototype._joinFilterDefs('$or', sampleFilterDef1, sampleFilterDef2);
            expect(filterDef).toEqual({
                '$or': [
                    sampleFilterDef1,
                    sampleFilterDef2
                ]
            });
        });

        it('should successfully join filter definitions passed in as an array', function() {
            var filterList = [sampleFilterDef1, sampleFilterDef2];
            var filterDef = prototype._joinFilterDefs('$or', filterList);
            expect(filterDef).toEqual({
                '$or': [
                    sampleFilterDef1,
                    sampleFilterDef2
                ]
            });
        });
    });
});

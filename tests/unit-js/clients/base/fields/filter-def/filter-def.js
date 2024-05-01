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
describe('Base.Fields.filterDef', function() {

    var field;
    var app;

    beforeEach(function() {

        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.set();

        field = SugarTest.createField('base', 'my_filter-def', 'filter-def', 'list',
            {}, 'Accounts');
    });

    afterEach(function() {
        sinon.restore();
        if (field) {
            field.dispose();
        }
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('format', function() {
        using('filter definitions', [
            {before: '[{"name":{"$equals":"Test"}}]' ,
                after: 'Name exactly matches Test'},
            {before: '[{"name":{"$equals":"Test"}},{"industry":{"$in":["Apparel","Banking"]}}]' ,
                after: 'Name exactly matches Test,\n' +
                    'Industry is any of Apparel or Banking'},
            {before: '[{"name":{"$equals":"Test"}},{"industry":{"$in":["Apparel","Banking"]}},' +
                    '{"date_entered":{"$lt":"2020-10-15"}}]' ,
                after: 'Name exactly matches Test,\n' +
                    'Industry is any of Apparel or Banking,\n' +
                    'Date Created before 2020-10-15'},
            {before: '[{"name":{"$equals":"Test"}},{"industry":{"$in":["Apparel","Banking"]}},' +
                    '{"date_entered":{"$lt":"2020-10-15"}},{"deleted":"1"}]' ,
                after: 'Name exactly matches Test,\n' +
                    'Industry is any of Apparel or Banking,\n' +
                    'Date Created before 2020-10-15,\n' +
                    'Deleted is true'},
        ], function(provider) {
            it('should convert the json filter def into a human readable string', function() {
                // Define function stub for metadata.getModule()
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return true;
                });

                // Define function stub for data.getBeanClass.prototype.getFilterableFields()
                sinon.stub(app.data, 'getBeanClass').callsFake(function() {
                        return {prototype: {
                                getFilterableFields: function() {
                                    return {
                                        name: {
                                            name: 'name',
                                            type: 'name',
                                            vname: 'LBL_NAME'
                                        },
                                        industry: {
                                            name: 'industry',
                                            type: 'enum',
                                            vname: 'LBL_INDUSTRY'
                                        },
                                        date_entered: {
                                            name: 'date_entered',
                                            type: 'datetime',
                                            vname: 'LBL_DATE_ENTERED'
                                        },
                                        deleted: {
                                            name: 'deleted',
                                            type: 'bool',
                                            vname: 'LBL_DELETED'
                                        }
                                    };
                                }
                            }
                        };
                    }
                );

                // Define function stub for metadata.getFilterOperators()
                sinon.stub(app.metadata, 'getFilterOperators').callsFake(function() {
                    return {
                        name: {$equals: 'LBL_OPERATOR_MATCHES'},
                        date: {$lt: 'LBL_OPERATOR_BEFORE'},
                        enum: {$in: 'LBL_OPERATOR_CONTAINS'},
                        bool: {$equals: 'LBL_OPERATOR_IS'}
                    };
                });

                // Define function stub for utils.FilterOptions().keyValueFilterDef()
                sinon.stub(app.utils, 'FilterOptions').callsFake(function() {
                    return {
                        keyValueFilterDef: function(key, value, _) {
                            if (key === 'name') {
                                return ['name', {$equals: 'Test'}];
                            }
                            if (key === 'industry') {
                                return ['industry', {$in: ['Apparel', 'Banking']}];
                            }
                            if (key === 'date_entered') {
                                return ['date_entered', {$lt: '2020-10-15'}];
                            }
                            if (key === 'deleted') {
                                return ['deleted', {$equals: '1'}];
                            }
                        }
                    };
                });

                // Define function stub for lang.get()
                sinon.stub(app.lang, 'get').callsFake(function(vname, module) {
                    // When called with key
                    if (vname === 'LBL_NAME') {
                        return 'Name';
                    }
                    if (vname === 'LBL_INDUSTRY') {
                        return 'Industry';
                    }
                    if (vname === 'LBL_DATE_ENTERED') {
                        return 'Date Created';
                    }
                    if (vname === 'LBL_DELETED') {
                        return 'Deleted';
                    }

                    // When called with operator
                    if (vname === 'LBL_OPERATOR_MATCHES') {
                        return 'exactly matches';
                    }
                    if (vname === 'LBL_OPERATOR_CONTAINS') {
                        return 'is any of';
                    }
                    if (vname === 'LBL_OPERATOR_BEFORE') {
                        return 'before';
                    }
                    if (vname === 'LBL_OPERATOR_IS') {
                        return 'is';
                    }
                });

                // Render the filter-def field
                field.render();

                // Expect that we should get a human readable string in the correct format returned
                expect(field.format(provider.before)).toEqual(provider.after);
            });
        });
    });
});

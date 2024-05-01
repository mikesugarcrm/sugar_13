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

const User = require('../../../src/core/user');
const Language = require('../../../src/core/language');

describe('Core/Language', function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
    });

    describe('get', function() {
        using('different label keys, modules, and contexts', [
            {
                key: 'LBL_THAT_DOESNT_EXIST',
                expected: 'LBL_THAT_DOESNT_EXIST'
            },
            // Not a label string that equals to a module name
            {
                key: 'Contacts',
                expected: 'Contacts'
            },
            // Exists only in `app_strings`.
            {
                key: 'DATA_TYPE_DUE',
                module: 'Accounts',
                expected: 'Due'
            },
            // Exists in Accounts.
            {
                key: 'LBL_MEMBER_ORG',
                module: ['Accounts', 'Contacts'],
                expected: 'Member Organizations'
            },
            // Exists in Contacts but not Accounts.
            {
                key: 'LBL_TEAM',
                module: ['Accounts', 'Contacts'],
                expected: 'Teams'
            },
            // Passing in a straight value for the context.
            {
                key: 'ERROR_TEST',
                context: 0,
                expected: 'Some error string 0'
            },
            // Exists only in `app_strings` and has an object passed in the context.
            {
                key: 'LBL_CREATE',
                module: 'Accounts',
                context: {name: 'Account'},
                expected: 'Create Account'
            },
            // Should ignore the context, since the label has no template.
            {
                key: 'LBL_ASSIGNED_TO_NAME',
                module: 'Contacts',
                context: {name: 'John Conner'},
                expected: 'Assigned to'
            }
        ], function(value) {
            it('should return a label from `mod_strings` or `app_strings`, or just the key', function() {
                var result = Language.get(value.key, value.module, value.context);
                expect(result).toEqual(value.expected);
            });
        });
    });

    describe('getAppListStrings', function() {
        using('different label keys', [
            {
                key: 'case_priority_default_key',
                expected: 'P2'
            },
            {
                key: 'merge_operators_dom',
                expected: {
                    'like': 'Contains',
                    'exact': 'Exactly',
                    'start': 'Starts With'
                }
            },
            {
                key: 'key_that_doesnt_exist',
                expected: {}
            },
            {
                key: 'test_tuple_array',
                expected: {
                    theKey: 'theValue'
                }
            },
            {
                key: 'test_triple_array',
                expected: {}
            }
        ], function(value) {
            it('should return an appropriate value from `app_list_strings`', function() {
                var result = Language.getAppListStrings(value.key);
                expect(result).toEqual(value.expected);
            });
        });
    });

    describe('setLanguage', function() {
        it('should not sync with noSync', function() {
            var syncStub = sinon.stub(app, 'sync');
            var updateLanguageStub = sinon.stub(Language, 'updateLanguage');

            Language.setLanguage('en_us', null, { noSync: true});

            expect(syncStub).not.toHaveBeenCalled();
            expect(updateLanguageStub).toHaveBeenCalled();
        });
        it('should sync by default', function() {
            var syncStub = sinon.stub(app, 'sync');

            Language.setLanguage('en_us');

            expect(syncStub).toHaveBeenCalled();
        });
        it('should filter Handlebars templates properly', function() {
            var syncStub = sinon.stub(app, 'sync');

            Handlebars.templates = {
                'lang.test1': {},
                'test2': {}
            };

            Language.setLanguage('en_us');

            expect(Handlebars.templates).toEqual({
                'test2': {}
            });
        });
    });

    describe('getAppListKeys', function() {
        using('different label keys', [
            {
                key: 'case_priority_default_key',
                expected: []
            },
            {
                key: 'case_priority_dom',
                expected: ['P1', 'P2', 'P3']
            },
            {
                key: 'test_tuple_array',
                expected: ['theKey']
            },
            {
                key: 'test_triple_array',
                expected: {}
            }
        ], function(value) {
            it('should return an appropriate value from `app_list_strings`', function() {
                var result = Language.getAppListKeys(value.key);
                expect(result).toEqual(value.expected);
            });
        });
    });

    describe('getAppString', function() {
        using('different label keys and contexts', [
            {
                label: 'LBL_CREATE',
                context: {name: 'Contact'},
                expected: 'Create Contact'
            },
            {
                label: 'LBL_CREATE',
                expected: 'Create {{name}}'
            },
            {
                label: 'LBL_THAT_DOESNT_EXIST',
                expected: undefined
            }
        ], function(value) {
            it('should return a translated value if the key exists in `app_strings`', function() {
                var string = Language.getAppString(value.label, value.context);
                expect(string).toEqual(value.expected);
            });
        });
    });

    describe('getModString', function() {
        using('different values', [
            {
                key: 'LBL_MODULE_NAME',
                module: 'Contacts',
                expected: 'CustomContacts'
            },
            {
                key: 'LBL_FIRST_NAME',
                module: ['Accounts', 'Contacts', 'Cases'],
                expected: 'First Name'
            },
            {
                key: 'LBL_THAT_DOESNT_EXIST',
                module: 'Contacts',
                expected: undefined
            }
        ], function(value) {
            it('should return the translated module string', function() {
                expect(Language.getModString(value.key, value.module)).toBe(value.expected);
            });
        });
    });

    describe('getModuleName', function() {
        using('different values', [
            {
                module: 'Contacts',
                expected: 'CustomContact'
            },
            {
                module: 'Contacts',
                options: {
                    plural: true
                },
                expected: 'CustomContacts'
            },
            {
                module: 'undefinedModule',
                options: {
                    defaultValue: 'Module'
                },
                expected: 'Module'
            },
            {
                module: 'undefinedModule',
                options: {
                    plural: true
                },
                expected: 'undefinedModule'
            },
            {
                module: 'undefinedModule',
                options: {
                    plural: true,
                    defaultValue: 'Module'
                },
                expected: 'Module'
            },
            {
                module: 'undefinedModule',
                expected: 'undefinedModule'
            }
        ], function(value) {
            it('should return the plural/singular module name, or the default value', function() {
                var result = Language.getModuleName(value.module, value.options);
                expect(result).toBe(value.expected);
            });
        });
    });

    describe('Changing language settings', function() {

        it('should update the language setting', function() {
            sinon.spy(app, 'trigger');
            sinon.stub(User, 'setPreference');

            var british = 'en_UK';
            Language.updateLanguage(british);

            expect(Language.direction).toBe('ltr');
            expect(Language.getLanguage()).toBe(british);
            expect(app.cache.get('lang')).toBe(british);
            expect(User.setPreference).toHaveBeenCalledWith('language', british);
            expect(app.trigger).toHaveBeenCalledWith('app:locale:change', british);
        });

        it('should set and get the default language', function() {
            Language.setDefaultLanguage('en_us');
            expect(Language.getDefaultLanguage()).toBe('en_us');
        });

        it('should toggle `Language.direction` based on the language', function() {
            Language.setDirection('en_us');
            expect(Language.direction).toBe('ltr');

            //Only enable rtl when Hebrew is selected
            sinon.spy(app, 'trigger');
            Language.setDirection('he_IL');
            expect(Language.direction).toBe('rtl');
            expect(app.trigger).toHaveBeenCalledWith('lang:direction:change');
        });
    });
});


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
describe('Base.Field.TextArea', function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('textarea', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
        fieldDef = {
            settings: {
                max_display_chars: 8
            }
        };
        field = SugarTest.createField('base', fieldName, 'textarea', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.restore();
    });

    describe('initialize', function() {
        it('should initialize settings to the values in `this.def` appropriately', function() {
            fieldDef.settings.collapsed = false;
            var testField = SugarTest.createField('base', fieldName, 'textarea', 'detail', fieldDef, module);

            expect(testField._settings.max_display_chars).toEqual(fieldDef.settings.max_display_chars);
            expect(testField._settings.collapsed).toEqual(fieldDef.settings.collapsed);
            expect(testField.collapsed).toEqual(testField._settings.collapsed);
        });
    });

    describe('format', function() {
        beforeEach(function() {
            // FIXME will be moved back to action once SC-2608 is done
            field.tplName = 'detail';
        });

        using('various field actions', [
            {
                action: 'list',
                parent: null,
                longExists: true
            },
            {
                action: 'edit',
                parent: null,
                longExists: false
            },
            {
                action: 'disabled',
                parent: null,
                longExists: true
            },
            {
                action: 'disabled',
                parent: {},
                longExists: true
            },
            {
                action: 'disabled',
                parent: {action: 'detail'},
                longExists: true
            },
            {
                action: 'detail',
                parent: null,
                longExists: true
            }
        ], function(value) {
            it('should set a `long` value for all modes except edit', function() {
                field.parent = value.parent;
                // FIXME will be moved back to action once SC-2608 is done
                field.tplName = value.action;
                var returnVal = field.format('testvalue');

                expect(returnVal.hasOwnProperty('long')).toBe(value.longExists);
            });
        });

        // max_display_chars was kept to '8' for this test.
        using('various field values', [
            {
                fieldVal: 'testvalue',
                expectedShortValue: ''
            },
            {
                fieldVal: '',
                expectedShortValue: ''
            },
            {
                fieldVal: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                expectedShortValue: 'Lorem '
            },
            {
                fieldVal: '結葉鮮敬。対好速残',
                expectedShortValue: ''
            },
            {
                fieldVal: '結葉鮮敬。対好\n速残',
                expectedShortValue: '結葉鮮敬。対好\n'
            },
            {
                fieldVal: '結葉鮮敬。対\n好速残',
                expectedShortValue: '結葉鮮敬。対\n'
            }
        ], function(value) {
            it('should set a proper `short` value if the field value exceeds `max_display_chars`', function() {
                var returnVal = field.format(value.fieldVal);

                expect(returnVal.hasOwnProperty('short')).toBe(true);
                expect(returnVal.hasOwnProperty('long')).toBe(true);
                expect(returnVal.hasOwnProperty('defaultValue')).toBe(true);
                expect(returnVal.long.toString()).toEqual(value.fieldVal);
                expect(returnVal.defaultValue.toString()).toEqual(value.fieldVal);
                expect(returnVal.short.toString()).toEqual(value.expectedShortValue);
            });
        });

        using('various field actions', [
            {
                action: 'list',
                parent: null,
                rowCount: 1
            },
            {
                action: 'edit',
                parent: null,
                rowCount: 2
            },
            {
                action: 'disabled',
                parent: null,
                rowCount: 1
            },
            {
                action: 'disabled',
                parent: {},
                rowCount: 1
            },
            {
                action: 'disabled',
                parent: {action: 'detail'},
                rowCount: 1
            },
            {
                action: 'detail',
                parent: null,
                rowCount: 1
            }
        ], function(value) {
            it('should set a row count for all field actions', function() {
                var countStub = sinon.stub(field, '_countRows').callsFake(function() {
                    return value.rowCount;
                });

                field.parent = value.parent;
                field.def.rows = 1;
                field.rowCount = field.def.rows;
                // FIXME will be moved back to action once SC-2608 is done
                field.tplName = value.action;
                var returnVal = field.format(
                    'Lorem Ipsum is simply dummy text of the printing and typesetting industry. \n' +
                    'Lorem Ipsum is simply dummy text of the printing and typesetting industry'
                );

                expect(field.rowCount).toBe(value.rowCount);
            });
        });
    });

    describe('setMode', function() {
        using('different actions and modes', [
            {action: 'list', mode: 'edit', expectedMode: 'list'},
            {action: 'list', mode: 'disabled', expectedMode: 'list'},
            {action: 'list', mode: 'detail', expectedMode: 'detail'},
            {action: 'list', mode: 'list', expectedMode: 'list'},
            {action: 'detail', mode: 'edit', expectedMode: 'edit'},
            {action: 'detail', mode: 'disabled', expectedMode: 'disabled'},
            {action: 'detail', mode: 'list', expectedMode: 'list'},
            {action: 'detail', mode: 'detail', expectedMode: 'detail'},
            {action: 'edit', mode: 'detail', expectedMode: 'detail'},
            {action: 'edit', mode: 'list', expectedMode: 'list'},
            {action: 'edit', mode: 'disabled', expectedMode: 'disabled'},
            {action: 'edit', mode: 'edit', expectedMode: 'edit'},
            {action: 'disabled', mode: 'detail', expectedMode: 'detail'},
            {action: 'disabled', mode: 'list', expectedMode: 'list'},
            {action: 'disabled', mode: 'edit', expectedMode: 'edit'},
            {action: 'disabled', mode: 'disabled', expectedMode: 'disabled'}
        ], function(value) {
            it('should call the parent `setMode` with the appropriate mode', function() {
                var superStub = sinon.stub(field, '_super');
                // FIXME will be moved back to action once SC-2608 is done
                field.tplName = value.action;
                field.setMode(value.mode);

                expect(superStub).toHaveBeenCalledWith('setMode', [value.expectedMode]);
            });
        });
    });

    describe('toggleCollapsed', function() {
        using('values', [true, false], function(value) {
            it('should toggle the value of `collapsed` and call render', function() {
                var renderStub = sinon.stub(field, 'render');

                field.collapsed = value;
                field.toggleCollapsed();

                expect(field.collapsed).toBe(!value);
                expect(renderStub).toHaveBeenCalled();
            });
        });
    });

    describe('getShortComment', function() {
        it('should truncate a comment if it is longer than max chars', function() {
            field._settings.max_display_chars = 10;
            var comment = 'This comment is longer than 10 chars';
            var shortened = field.getShortComment(comment);
            expect(shortened).toEqual('This ');
        });

    });

    describe('insertHtmlLinks', function() {
        it('replaces text links with html links', function() {
            var comment = 'www.sugarcrm.com';
            var result = field.insertHtmlLinks(comment);
            var expected =
                '<a href="http://www.sugarcrm.com" target="_blank" rel="nofollow noopener noreferrer">' +
                'www.sugarcrm.com</a>';
            expect(result).toEqual(expected);
        });
    });
});

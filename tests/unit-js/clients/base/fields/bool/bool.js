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
describe('Base.Field.Bool', function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        app.cache.cutAll();
        sinon.restore();
        Handlebars.templates = {};
    });

    describe('format & unformat', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField('base', 'my_bool', 'bool', 'detail');
        });

        afterEach(function() {
            field.dispose();
        });

        using('valid values',
            [['0', false], ['1', true], [false, false], [true, true]],
            function(value, result) {
                it('should format the value', function() {
                    expect(field.format(value)).toEqual(result);
                });
            });

        using('valid values',
            [['0', false], ['1', true], [false, false], [true, true]],
            function(value, result) {
                it('should unformat the value', function() {
                    expect(field.unformat(value)).toEqual(result);
                });
        });
    });

    describe('bindDomChange', function() {
        var field;
        beforeEach(function() {
            SugarTest.testMetadata.init();
        });

        afterEach(function() {
            field.dispose();
            SugarTest.testMetadata.dispose();
        });

        it('should update the model on checkbox value change', function() {
            SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'edit');
            SugarTest.testMetadata.set();
            field = SugarTest.createField('base', 'my_bool', 'bool', 'edit');

            field.render();
            var modelSpy = sinon.spy(field.model, 'set');
            field.$(field.fieldTag).prop('checked', true).trigger('change');
            expect(modelSpy).toHaveBeenCalledWith('my_bool', true);
            field.$(field.fieldTag).prop('checked', false).trigger('change');
            expect(modelSpy).toHaveBeenCalledWith('my_bool', false);
        });

        it('should update the model on dropdown value change', function() {
            SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'dropdown');
            SugarTest.testMetadata.set();
            field = SugarTest.createField('base', 'my_bool', 'bool', 'massupdate');

            field.render();
            var modelSpy = sinon.spy(field.model, 'set');
            field.$(field.select2fieldTag).val('1').trigger('change');
            expect(modelSpy).toHaveBeenCalledWith('my_bool', true);
            field.$(field.select2fieldTag).val('0').trigger('change');
            expect(modelSpy).toHaveBeenCalledWith('my_bool', false);
        });
    });

    describe('bindDataChange', function() {
        var field;
        beforeEach(function() {
            SugarTest.testMetadata.init();
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
        });

        it('should toggle "checked" property upon model change', function() {
            field = SugarTest.createField('base', 'my_bool', 'bool', 'edit');
            field.render();

            field.model.set('my_bool', true);
            expect(field.$(field.fieldTag).val()).toNotEqual('true');
            expect(field.$(field.fieldTag).prop('checked')).toBeTruthy();

            field.model.set('my_bool', false);
            expect(field.$(field.fieldTag).val()).toNotEqual('false');
            expect(field.$(field.fieldTag).prop('checked')).toBeFalsy();

            field.dispose();
        });

        it('should update dom value to "1" or "0" upon model change if action is massupdate', function() {
            field = SugarTest.createField('base', 'my_bool', 'bool', 'massupdate');
            field.render();

            field.model.set('my_bool', true);
            expect(field.$(field.select2fieldTag).val()).toEqual('1');

            field.model.set('my_bool', false);
            expect(field.$(field.select2fieldTag).val()).toEqual('0');

            field.dispose();
        });
    });

    describe('render', function() {
        describe('render detail', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'detail');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'my_bool', 'bool', 'detail');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should render as a disabled checkbox and toggle according to the value', function() {
                field.def.default = false;
                field.render();
                expect(field.$(field.fieldTag)).toHaveAttr('disabled');
                expect(field.$(field.fieldTag)).not.toHaveAttr('checked');
                field.model.set(field.name, true);
                expect(field.$(field.fieldTag)).toHaveAttr('checked');
            });
        });

        describe('render edit', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'edit');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'my_bool', 'bool', 'edit');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should render with default values in def', function() {
                field.def.text = 'text';
                field.def.tabindex = 1;
                field.render();
                expect(field.$('label')).toExist();
                expect(field.$(field.fieldTag)).toHaveAttr('tabindex');
            });

            it('should render with no default values in def', function() {
                field.render();
                expect(field.$('label')).not.toExist();
            });

        });

        describe('render dropdown', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'dropdown');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'my_bool', 'bool', 'massupdate');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should render select2 without searchbox on massupdate', function() {
                field.render();
                expect(field.$('.select2-search-hidden')).not.toBeEmpty();
            });

            it('should fall back to the dropdown template if attempting to render the massupdate template', function() {
                var getFieldSpy = sinon.spy(app.template, 'getField');
                field.render();
                expect(getFieldSpy).toHaveBeenCalledWith('bool', 'massupdate', undefined, 'dropdown');
            });
        });

        describe('render disabled', function() {
            var field;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('bool', 'field', 'base', 'edit');
                SugarTest.testMetadata.set();
                field = SugarTest.createField('base', 'my_bool', 'bool', 'disabled');
            });

            afterEach(function() {
                field.dispose();
                SugarTest.testMetadata.dispose();
            });

            it('should render disabled when the action is disabled', function() {
                field.render();
                expect(field.action).toEqual('disabled');
                expect(field.$(field.fieldTag)).toHaveAttr('disabled');
            });
        });
    });
});

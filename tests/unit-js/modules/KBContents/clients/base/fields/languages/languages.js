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
describe('modules.kbcontents.clients.base.fields.languages', function() {
	var app, field, sandbox,
        module = 'KBContents',
        fieldName = 'languages',
        fieldType = 'languages',
        model;

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        model.set(fieldName, [{
            'en': 'English'
        }]);
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {
            'searchBarThreshold': 5,
            'label': 'Available languages',
            'default': false,
            'enabled': true,
            'view': 'edit'
        }, module, model, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('should be called render when model attribute changed', function() {
        var render = sandbox.spy(field, 'render');
        model.set(fieldName, [{
            lang1: 'Lang 1',
            primary: true
        }]);
        expect(render).toHaveBeenCalled();
    });

    it('should be called unformat/format when item added', function() {
        field.render();
        var unformat = sandbox.spy(field, 'unformat');
        var format = sandbox.spy(field, 'format');
        expect(field.value.length).toEqual(1);
        field.$('[data-action=add-field]').click();
        expect(unformat).toHaveBeenCalled();
        expect(format).toHaveBeenCalled();
    });

    it('should be called format when item changed', function() {
        field.render();
        var format = sandbox.spy(field, 'format');
        field.$('input[type="text"]').first().val('testlang1').change();
        field.$('input[type="text"]').last().val('Test Lang 1').change();
        expect(format).toHaveBeenCalled();
        expect(_.first(model.get(fieldName))).toEqual({
            'primary': false,
            'testlang1': 'Test Lang 1'
        });
    });

    it('should return correct results when format method called', function() {
        field.render();
        var result = field.format([
            {
                lang1: 'Lang 1'
            },
            {
                lang2: 'Lang 2'
            }
        ]);

        expect(result).toEqual(jasmine.any(Object));
        expect(result.length).toEqual(2);
        expect(result[0].items.lang1).toEqual('Lang 1');
        expect(result[1].items.lang2).toEqual('Lang 2');
    });

    it('should return correct results when unformat method called', function() {
        field.render();
        var result = field.unformat([
            {
                items: [
                    {
                        lang1: 'Lang 1'
                    }
                ],
                primary: false
            }
        ]);

        expect(result).toEqual(jasmine.any(Array));
        expect(result.length).toEqual(1);
        expect(_.first(result)).toEqual({
            0 : {
                'lang1' : 'Lang 1'
            },
            primary: false
        });
    });

    it('should be able to set primary item', function() {
        model.set(fieldName, [{
            lang1: 'Lang 1',
            primary: true
        },
        {
            lang2: 'Lang 2',
            primary: false
        }]);

        field.render();
        var setPrimary = sandbox.spy(field, 'setPrimary'),
            unformat = sandbox.spy(field, 'unformat');
        field.$('[data-action="set-primary-field"]').last().click();

        expect(setPrimary).toHaveBeenCalledWith(1);
        expect(unformat).toHaveBeenCalled();
        expect(_.last(model.get(fieldName)).primary).toEqual(true);
    });

    it('should add item to list when add-field button clicked', function() {
        field.render();
        expect(field.value.length).toEqual(1);
        field.$('[data-action=add-field]').click();
        expect(field.value.length).toEqual(2);
        field.$('[data-action=add-field]').click();
        expect(field.value.length).toEqual(2);
    });

    it('should remove item from list when remove-field button clicked', function() {
        sandbox.stub(app.alert, 'show').callsFake(function(name, options) {
            options.onConfirm();
        });
        field.render();
        expect(field.value.length).toEqual(1);
        field.$('[data-action=add-field]').click();
        expect(field.value.length).toEqual(2);
        field.$('[data-action=remove-field]').click();
        expect(field.value.length).toEqual(1);
    });

});

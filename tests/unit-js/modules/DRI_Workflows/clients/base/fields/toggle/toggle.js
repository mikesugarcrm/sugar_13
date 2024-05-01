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

describe('View.Fields.Base.DRIWorkflows.Toggle', function() {
    let app;
    let field;
    let model;
    let initOptions;
    let module = 'DRI_Workflows';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'toggle');

        model = app.data.createBean(module);
        field = SugarTest.createField(
            'base',
            'toggle',
            'toggle',
            'detail',
            {},
            module,
            model,
            null,
            true
        );
        context = new app.Context();
        initOptions = {
            context: context,
        };
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        model.dispose();
        sinon.restore();
    });

    describe('initialize', function() {
        it('Should initialize the toggle', function() {
            sinon.stub(field, '_super');
            field.initialize(initOptions);
            expect(field._super).toHaveBeenCalled();
        });
    });

    describe('toggleValue', function() {
        it('Should highlight Hide when toggle is done', function() {
            sinon.stub($.fn, 'addClass').returns(true);
            sinon.stub(field.model, 'get').returns(true);
            field.toggleValue();
            expect(field.model.get).toHaveBeenCalled();
            expect($.fn.addClass).toHaveBeenCalled();
        });

        it('Should highlight Show when toggle is done', function() {
            sinon.stub($.fn, 'addClass').returns(true);
            sinon.stub(field.model, 'get').returns(false);
            field.toggleValue();
            expect(field.model.get).toHaveBeenCalled();
            expect($.fn.addClass).toHaveBeenCalled();
        });
    });

    describe('format', function() {
        using('values', [
            {
                input: true,
                expected_value: 'Hide',
            },
            {
                input: false,
                expected_value: 'Show',
            },
        ],
        function(values) {
            it('Formatting value to Show or Hide in record view', function() {
                field.action = 'detail';
                sinon.stub(app.lang, 'get').returns(values.expected_value);
                expect(field.format(values.input)).toEqual(values.expected_value);
            });

            it('Formatting value to bold selected option in edit view', function() {
                field.action = 'edit';
                sinon.stub($.fn, 'addClass').returns(true);
                sinon.stub($.fn, 'removeClass').returns(true);
                expect(field.format(values.input)).toEqual(values.input);
                expect($.fn.addClass).toHaveBeenCalled();
                expect($.fn.removeClass).toHaveBeenCalled();
            });
        });
    });
});

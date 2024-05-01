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
describe('ConsoleConfiguration.Fields.FreezeFirstColumn', function() {
    let app;
    let field;
    let fieldName;
    let model;
    let module;
    let context;

    beforeEach(function() {
        app = SugarTest.app;
        fieldName = 'freeze_first_column';
        module = 'ConsoleConfiguration';
        model = app.data.createBean(module);

        context = app.context.getContext();
        context.set({
            model: model,
            collection: app.data.createBeanCollection(module)
        });
        context.prepare();

        field = SugarTest.createField(
            'base',
            fieldName,
            'freeze-first-column',
            'edit',
            {},
            module,
            model,
            context,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        model = null;
        field = null;
    });

    describe('initialize', function() {
        let options;
        beforeEach(function() {
            options = {};
            sinon.stub(field, '_super');
            sinon.stub(field, 'setupField');
        });

        it('should call the _super initialize method', function() {
            field.initialize(options);

            expect(field._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call the setupField method', function() {
            field.initialize(options);

            expect(field.setupField).toHaveBeenCalled();
        });
    });

    describe('setupField', function() {
        beforeEach(function() {
            sinon.stub(field.model, 'get').returns('Cases');
            sinon.stub(field.model, 'set');
        });

        it('should set field value as true if freeze first column is not defiend', function() {
            model.get = sinon.stub().returns({});
            sinon.stub(field.context, 'get')
                .withArgs('consoleId').returns('123')
                .withArgs('model').returns(model);
            field.setupField();

            expect(field.model.set).toHaveBeenCalledWith('freeze_first_column', true);
        });

        it('should set field value as true if freeze first column is defiend', function() {
            sinon.stub(field.context, 'get')
                .withArgs('consoleId').returns('123')
                .withArgs('model').returns({
                get: function() {
                    return {
                        '123': {
                            'Cases': true
                        }
                    };
                }
            });
            // field.value = false;
            // field.collection = {
            //     models: [],
            //     off: $.noop
            // };
            field.setupField();

            expect(field.model.set).toHaveBeenCalledWith('freeze_first_column', true);
        });
    });
});

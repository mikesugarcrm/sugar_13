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

describe('CJ_Forms.Views.Create', function() {
    let app;
    let model;
    let view;
    let layout;
    let context;
    let viewName = 'create';
    let module = 'CJ_Forms';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        context = app.context.getContext({
            module: module,
            model: model,
            create: true
        });
        context.prepare(true);

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', viewName);

        layout = SugarTest.createLayout(
            'base',
            module,
            viewName,
            {},
            null,
            false
        );
        view = SugarTest.createView(
            'base',
            module,
            viewName,
            null,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();

        layout = null;
        context = null;
    });

    describe('_renderSpecificField', function() {
        it('should render the field on view', function() {
            let object = {
                render: sinon.stub()
            };
            sinon.stub(view, 'getField').returns(object);
            view._renderSpecificField('Points');
            expect(view.getField).toHaveBeenCalled();
        });
    });

    describe('hideOrShowFields', function() {
        it('should show or hide fields on the base of main trigger type', function() {
            let ele = {
                show: sinon.stub(),
                hide: sinon.stub()
            };
            let data = {
                closest: sinon.stub().returns(ele)
            };
            let object = {
                getFieldElement: sinon.stub().returns(data)
            };
            sinon.stub(view, 'getField').returns(object);
            view.hideOrShowFields();
            expect(view.getField).toHaveBeenCalled();
        });
    });

    describe('validateModelWaterfall', function() {
        it('should call view getField, getFields and model doValidate functions', function() {
            let fields = {
                description: {
                    name: 'description',
                    type: 'varchar',
                    required: false,
                    len: 255,
                },
            };
            let customField = {
                addedFieldsDefs: {
                    is_empty: {
                        name: 'is_empty',
                        type: 'bool',
                        required: false,
                        default: 0,
                    },
                },
                _validateField: sinon.stub(),
            };

            sinon.stub(view, 'getFields').returns(fields);
            sinon.stub(view, 'getField').returns(customField);
            sinon.stub(view.model, 'doValidate').returns(true);
            view.validateModelWaterfall();

            expect(view.getFields).toHaveBeenCalled();
            expect(view.getField).toHaveBeenCalled();
            expect(view.model.doValidate).toHaveBeenCalled();
        });
    });
});

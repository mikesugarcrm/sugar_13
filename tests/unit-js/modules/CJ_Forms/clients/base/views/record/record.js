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

describe('CJ_Forms.Views.Record', function() {
    let app;
    let model;
    let view;
    let layout;
    let context;
    let viewName = 'record';
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

    describe('hasUnsavedChanges', function() {
        using('input', [
            {
                fields: [
                    {
                        fields: [
                            {
                                name: 'is_empty',
                                span: 6,
                            },
                            {
                                name: 'is_parent',
                                span: 6,
                            },
                        ]
                    },
                ],
                result: true,
                resavingAfterMetadataSync: false,
            },
            {
                fields: [
                    {
                        name: 'populate_fields',
                        span: 6,
                    },
                ],
                result: false,
                resavingAfterMetadataSync: false,
            },
            {
                fields: [],
                result: false,
                resavingAfterMetadataSync: true,
            },
        ],

        function(input) {
            it('changedAttributes should be called and hasUnsavedChanges response should match with input result',
                function() {
                    sinon.stub(view.model, 'changedAttributes').returns({is_parent: 'true'});
                    sinon.stub(view, 'getField').returns({populate_fields: 'true'});

                    view.meta.panels = [{
                        newTab: false,
                        name: 'Test Panel',
                        placeholders: 1,
                        fields: input.fields,
                    }];
                    view.noEditFields = {};
                    view.resavingAfterMetadataSync = input.resavingAfterMetadataSync;

                    expect(view.hasUnsavedChanges()).toBe(input.result);
                    expect(view.model.changedAttributes).toHaveBeenCalled();
                }
            );
        });
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

    describe('saveClicked', function() {
        using('input', [
            {
                fields: [
                    {
                        name: 'description',
                        type: 'varchar',
                        required: false,
                        len: 255,
                    },
                ],
                shouldCall: true,
            },
            {
                fields: [],
                shouldCall: false,
            },
        ],

        function(input) {
            it('should call view getField, getFields and model doValidate functions', function() {
                let populateFields = {
                    addedFieldsDefs: {
                        name: 'populate_fields',
                        type: 'varchar',
                        required: false,
                        len: 255,
                    },
                };

                sinon.stub(view.model, 'doValidate');
                sinon.stub(view, 'getField').returns(populateFields);
                sinon.stub(view, 'getFields').returns(input.fields);
                sinon.stub(app.acl, 'hasAccessToModel').returns('true');
                view.model.set('_erased_fields', {});
                view.saveClicked();

                if (input.shouldCall) {
                    expect(app.acl.hasAccessToModel).toHaveBeenCalled();
                } else {
                    expect(app.acl.hasAccessToModel).not.toHaveBeenCalled();
                }

                expect(view.getField).toHaveBeenCalled();
                expect(view.getFields).toHaveBeenCalled();
                expect(view.model.doValidate).toHaveBeenCalled();
            });
        });
    });
});


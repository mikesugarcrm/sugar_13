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
describe('ProductBundles.Base.Views.QuoteDataGroupFooter', function() {
    var app;
    var view;
    var viewMeta;
    var viewContext;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var layoutGroupId;

    beforeEach(function() {
        app = SugarTest.app;

        viewContext = app.context.getContext();
        viewContext.set({
            module: 'ProductBundles'
        });
        viewContext.prepare();

        layoutGroupId = 'layoutGroupId1';
        viewLayoutModel = new Backbone.Model({
            id: layoutGroupId
        });
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'ProductBundles', 'default', layoutDefs);
        layout.model = viewLayoutModel;
        layout.listColSpan = 3;
        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };

        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-footer',
            viewMeta, viewContext, true, layout);
        sinon.stub(view, 'setElement');
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should have the same model as the layout', function() {
            expect(view.model).toBe(viewLayoutModel);
        });

        it('should set listColSpan to be the layout listColSpan + 1', function() {
            expect(view.listColSpan).toBe(layout.listColSpan + 1);
        });

        it('should set el to be the layout el', function() {
            expect(view.el).toBe(layout.el);
        });

        describe('when initializing', function() {
            var initOptions;

            beforeEach(function() {
                initOptions = {
                    context: viewContext,
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    layout: {
                        listColSpan: 2,
                        model: viewLayoutModel
                    }
                };

                view.initialize(initOptions);
            });

            afterEach(function() {
                initOptions = null;
            });

            it('should call setElement', function() {
                expect(view.setElement).toHaveBeenCalled();
            });
        });
    });
});

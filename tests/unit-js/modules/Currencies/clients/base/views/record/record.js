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

describe('Currencies.Base.Views.Record', function() {
    var app;
    var layout;
    var view;
    var options;
    var sinonSandbox;
    var context;
    var model;

    afterEach(function() {
        sinonSandbox.restore();
    });

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        options = {
            meta: {
                buttons: [
                    {
                        name: 'main_dropdown',
                        buttons: [
                            {
                                name: 'edit_button',
                                css_class: ''
                            }
                        ]
                    }
                ],

                panels: [
                    {
                        fields: [
                            {
                                name: 'name'
                            }
                        ]
                    }
                ]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        SugarTest.seedMetadata(true, './fixtures');

        context = app.context.getContext();

        model = app.data.createBean('Currencies');
        var tmpModel = new Backbone.Model();
        model.getRelatedCollection = function() { return tmpModel; };
        sinonSandbox.stub(tmpModel, 'fetch').callsFake(function() {});

    });

    describe('_checkIfBaseCurrency', function() {
        it('should not change anything when not the base', function() {
            context.set({
                model: model,
                module: 'Currencies'
            });
            context.prepare();
            context.set('modelId', '1');

            layout = SugarTest.createLayout('base', 'Currencies', 'record', {});
            view = SugarTest.createView('base', 'Currencies', 'record', options.meta, context, true, layout);

            expect(view.meta.buttons[0].buttons[0].css_class).toEqual('');
        });

        it('should set the css_class to disabled for the delete button', function() {
            context.set({
                model: model,
                module: 'Currencies'
            });
            context.prepare();
            context.set('modelId', '-99');

            layout = SugarTest.createLayout('base', 'Currencies', 'record', {});
            view = SugarTest.createView('base', 'Currencies', 'record', options.meta, context, true, layout);

            expect(view.meta.buttons[0].buttons[0].css_class).toEqual(' disabled');
        });
    });
});

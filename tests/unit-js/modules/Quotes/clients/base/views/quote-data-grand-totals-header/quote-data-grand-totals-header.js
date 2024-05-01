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
describe('Quotes.Base.Views.QuoteDataGrandTotalsHeader', function() {
    var app;
    var view;
    var viewMeta;
    var viewModel;
    var ctx;

    beforeEach(function() {
        app = SugarTest.app;
        viewModel = new Backbone.Model({
            deal_tot: '100',
            new_sub: '50'
        });
        ctx = app.context.getContext();
        ctx.set('model', viewModel);
        viewMeta = {
            buttons: [
                {
                    type: 'quote-data-actiondropdown',
                    name: 'panel_dropdown',
                    no_default_action: true,
                    buttons: [
                        {
                            name: 'create_qli_button',
                            type: 'button',
                            icon: 'sicon-plus',
                            label: 'LBL_CREATE_QLI_BUTTON_LABEL'
                        },
                        {
                            name: 'create_comment_button',
                            type: 'button',
                            icon: 'sicon-plus',
                            label: 'LBL_CREATE_COMMENT_BUTTON_LABEL'
                        },
                        {
                            name: 'create_group_button',
                            type: 'button',
                            icon: 'sicon-plus',
                            label: 'LBL_CREATE_GROUP_BUTTON_LABEL'
                        }
                    ]
                }
            ],
            panels: [{
                fields: [{
                    name: 'deal_tot',
                    label: 'LBL_DEAL_TOT'
                },
                {
                    name: 'new_sub',
                    label: 'LBL_NEW_SUB'
                }]
            }]
        };
        sinon.stub(app.currency, 'formatAmountLocale').callsFake(function() {
            return '$0.00';
        });
        view = SugarTest.createView('base', 'Quotes', 'quote-data-grand-totals-header', viewMeta, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set className', function() {
            expect(view.className).toBe('quote-data-grand-totals-header-wrapper quote-totals-row');
        });
    });

    describe('_onCreateGroupBtnClicked()', function() {
        it('should trigger quotes:group:create event', function() {
            sinon.spy(view.context, 'trigger');
            view._onCreateGroupBtnClicked();

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create');
        });
    });
});

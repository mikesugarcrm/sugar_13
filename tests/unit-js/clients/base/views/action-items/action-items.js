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
describe('View.Views.Base.ActionItemsView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        view = SugarTest.createView('base', '', 'action-items', {});
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('addItems', function() {
        beforeEach(function() {
            view.items = null;
        });

        using('different metadata', [
            {
                items: null,
                itemsToAdd: [],
                expected: []
            },
            {
                items: [],
                itemsToAdd: [
                    {
                        icon: 'sicon',
                        tooltip: 'LBL_DESC',
                        label: 'LBL_NAME',
                        href: '#link',
                    }
                ],
                expected: [
                    {
                        icon: 'sicon',
                        tooltip: 'LBL_DESC',
                        label: 'LBL_NAME',
                        href: '#link',
                    }
                ]
            },
            {
                items: [
                    {
                        icon: 'sicon',
                        tooltip: 'LBL_DESC',
                        label: 'LBL_NAME',
                        href: '#link',
                    }
                ],
                itemsToAdd: [
                    {
                        icon: 'sicon_2',
                        tooltip: 'LBL_DESC_2',
                        label: 'LBL_NAME_2',
                        href: '#link-2',
                    }
                ],
                expected: [
                    {
                        icon: 'sicon',
                        tooltip: 'LBL_DESC',
                        label: 'LBL_NAME',
                        href: '#link',
                    },
                    {
                        icon: 'sicon_2',
                        tooltip: 'LBL_DESC_2',
                        label: 'LBL_NAME_2',
                        href: '#link-2',
                    }
                ]
            }
        ], function(values) {
            it('should add items', function() {
                view.items = values.items;
                view.addItems(values.itemsToAdd);
                expect(view.items).toEqual(values.expected);
            });
        });
    });
});

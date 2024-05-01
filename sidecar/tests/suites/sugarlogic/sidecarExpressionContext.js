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
const PluginManager = require('../../../src/core/plugin-manager');
const BeanCollection = require('../../../src/data/bean-collection');

describe('SugarLogic Sidecar Expression Context', function () {
    let sandbox;
    let model;
    let collection;
    let context;
    let view;
    let app;

    beforeEach(function () {
        sandbox = sinon.createSandbox();
        SugarTest.seedMetadata(true);
        app = SugarTest.app;

        model = app.data.createBean('Contacts', {
            first_name: 'Foo',
            last_name: 'Bar',
        });

        PluginManager.register('SugarLogic', 'view', SUGAR.expressions.plugin);

        model.fields = fixtures.metadata.modules.Contacts.fields;
        collection = new BeanCollection([model]);
        view = SugarTest.createComponent('View', {
            context: new SUGAR.App.Context({
                url: 'someurl',
                module: 'Contacts',
                model: model,
                collection: collection,
            }),
            name: 'sugarlogictestview',
            platform: 'base',
        });
    });

    afterEach(function () {
        sandbox.restore();
        PluginManager.plugins = {
            view: {},
            field: {},
            layout: {},
            model: {},
            collection: {}
        };
    });

    describe('Plugin Initialization', function () {
        it('should return all fields used in dependencies that are not fields of type link', function () {
            sandbox.stub(view, 'getApplicableDeps').callsFake(function () {
                return [
                    {
                        name: 'name_vis',
                        hooks: ['all'],
                        trigger: 'true',
                        triggerFields: ['accounts', 'name'],
                        relatedFields: [],
                        onload: true,
                        isRelated: false,
                        actions: [
                            {
                                action: 'SetVisibility',
                                params: { target: 'name', value: 'contains(related($accounts,\"name\"), $last_name)' },
                            },
                        ],
                        notActions: [],
                    },
                    {
                        name: 'vis2',
                        hooks: ['all'],
                        trigger: 'true',
                        triggerFields: ['parent_id'],
                        relatedFields: [],
                        onload: true,
                        isRelated: false,
                        actions: [
                            {
                                action: 'SetVisibility',
                                params: { target: 'description', value: 'greaterThan(2, $parent_id)' },
                            },
                        ],
                        notActions: [],
                    },
                ];
            });

            expect(view._getDepFields()).toEqual(['last_name', 'parent_id']);
        });
    });

    describe('_getUniqueFieldsList', function() {
        var sec;
        var fields;

        beforeEach(function() {
            sec = new SUGAR.expressions.SidecarExpressionContext(view, model, collection);
            fields = [
                {
                    type: 'rollupCurrencySum',
                    link: 'product_bundles',
                    relate: 'new_sub'
                },
                {
                    type: 'rollupCurrencySum',
                    link: 'product_bundles',
                    relate: 'taxable_subtotal'
                },
                {
                    type: 'rollupCurrencySum',
                    link: 'product_bundles',
                    relate: 'new_sub'
                }
            ];
        });

        afterEach(function() {
            sec = null;
            fields = null;
        });

        it('should remove duplicate fields', function() {
            expect(sec._getUniqueFieldsList(fields)).toEqual(
                [
                    {
                        type: 'rollupCurrencySum',
                        link: 'product_bundles',
                        relate: 'new_sub'
                    },
                    {
                        type: 'rollupCurrencySum',
                        link: 'product_bundles',
                        relate: 'taxable_subtotal'
                    }
                ]
            );
        });

        it('should consider two fields different if they a have non-identical attributes set', function() {
            fields[2].extraAttribute = 'extra_attribute';
            expect(sec._getUniqueFieldsList(fields)).toEqual(
                [
                    {
                        type: 'rollupCurrencySum',
                        link: 'product_bundles',
                        relate: 'new_sub'
                    },
                    {
                        type: 'rollupCurrencySum',
                        link: 'product_bundles',
                        relate: 'taxable_subtotal'
                    },
                    {
                        type: 'rollupCurrencySum',
                        link: 'product_bundles',
                        relate: 'new_sub',
                        extraAttribute: 'extra_attribute'
                    },
                ]
            );
        });
    });
});

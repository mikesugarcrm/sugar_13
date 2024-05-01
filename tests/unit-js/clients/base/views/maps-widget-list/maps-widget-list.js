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
describe('Base.Views.MapsWidgetList', function() {
    var app;
    var view;
    var viewName = 'maps-widget-list';
    var context;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        SugarTest.loadComponent('base', 'view', viewName);

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module: 'Accounts',
            model: app.data.createBean('Accounts'),
            collection: app.data.createBeanCollection('Accounts'),
        });

        sinon.stub(app.controller, 'context').value(context);

        initOptions = {
            type: 'maps-widget-list',
            name: 'maps-widget-list',
            def: {
                view: 'maps-widget-list'
            },
            module: 'Accounts',
            context: context,
            meta: {
                panels: [
                    {
                        'fields': [
                            {
                                name: 'name',
                                label: 'LBL_NAME',
                                type: 'name'
                            },
                            {
                                name: 'maps_distance',
                                label: 'LBL_MAPS_DISTANCE',
                                type: 'text'
                            }
                        ]
                    }
                ]
            }
        };
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

    describe('initialize()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, null, context);
            view.initialize(initOptions);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set context atttributes properly', function() {
            expect(view._defaultMetaFields).toEqual(
                [{name: 'name', label: 'LBL_NAME', type: 'name'},
                {name: 'maps_distance', label: 'LBL_MAPS_DISTANCE', type: 'text'}]
            );
        });
    });

    describe('_initOrderBy()', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, null, context);
            view.initialize(initOptions);
        });

        afterEach(function() {
            view.dispose();
        });

        it('will set context atttributes properly', function() {
            var orderBy = view._initOrderBy();

            expect(orderBy.field).toEqual('maps_distance');
            expect(orderBy.direction).toEqual('asc');
        });
    });
});

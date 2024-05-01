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
describe('Base.Views.MapsWidgetDropdowns', function() {
    var app;
    var view;
    var viewName = 'maps-widget-dropdowns';
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.declareData('base', 'Filters');

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        sinon.stub(app.api, 'call').callsFake(function() {});

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module: 'Accounts',
            model: app.data.createBean('Accounts')
        });

        initOptions = {
            type: 'maps-widget-dropdowns',
            name: 'maps-widget-dropdowns',
            def: {
                view: 'maps-widget-dropdowns'
            },
            module: 'Accounts',
            context: context,
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

    describe('render()', function() {
        beforeEach(function() {
            setupView();
            view.render();
        });

        it('should properly create select2 controllers', function() {
            expect(view._select2).toNotEqual(null);
            expect(view._select2.modules).toNotEqual(undefined);
            expect(view._select2.filterBy).toNotEqual(undefined);
            expect(view._select2.unitType).toNotEqual(undefined);
            expect(view._select2.radius).toNotEqual(undefined);
        });
    });

    describe('moduleChanged()', function() {
        beforeEach(function() {
            setupView();
            view.render();
        });

        it('should properly change data', function() {
            view.moduleChanged({
                currentTarget: {
                    value: 'Contacts',
                }
            });

            expect(view.model.get('modules')).toEqual('Contacts');
            expect(view.model.get('filterBy')).toEqual('');
        });
    });

    describe('filterByChanged()', function() {
        beforeEach(function() {
            setupView();
            view.render();
        });

        it('should properly change data', function() {
            view.filterByChanged({
                currentTarget: {
                    value: 'favorites',
                }
            });

            expect(view.model.get('filterBy')).toEqual('Favorites');
        });
    });

    describe('unitTypeChanged()', function() {
        beforeEach(function() {
            setupView();
            view.render();
        });

        it('should properly change data', function() {
            view.unitTypeChanged({
                currentTarget: {
                    value: 'km',
                }
            });

            expect(view.model.get('unitType')).toEqual('LBL_MAP_UNIT_TYPE_KM');
            expect(view.model.get('radius')).toEqual('5');
        });
    });

    describe('radiusChanged()', function() {
        beforeEach(function() {
            setupView();
            view.render();
        });

        it('should properly change data', function() {
            view.radiusChanged({
                currentTarget: {
                    value: '250',
                }
            });

            expect(view.model.get('radius')).toEqual('250');
        });
    });

    describe('dispose', function() {
        beforeEach(function() {
            setupView();

            view.render();
        });

        afterEach(function() {
            view.dispose();
        });

        it('should properly call dispose function', function() {
            expect(view._select2.modules).toNotEqual(undefined);
            expect(view._select2.filterBy).toNotEqual(undefined);
            expect(view._select2.unitType).toNotEqual(undefined);
            expect(view._select2.radius).toNotEqual(undefined);

            view.dispose();

            expect(view._select2.modules).toEqual(undefined);
            expect(view._select2.filterBy).toEqual(undefined);
            expect(view._select2.unitType).toEqual(undefined);
            expect(view._select2.radius).toEqual(undefined);
        });
    });

    function setupView() {
        view = SugarTest.createView('base', '', viewName, null, context);

        sinon.stub(view, '_getAvailableModules').callsFake(function() {
            return {'Accounts': 'Accounts'};
        });

        view._dropdowns.filterBy.options = {
            'myAccounts': 'MyAccounts',
            'favorites': 'Favorites'
        };
    }
});

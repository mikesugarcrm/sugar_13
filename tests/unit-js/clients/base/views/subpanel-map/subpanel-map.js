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
describe('Base.Views.SubpanelMap', function() {
    var app;
    var view;
    var viewName = 'subpanel-map';
    var initOptions;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        sinon.stub(app.api, 'call').callsFake(function() {});

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'subpanel-map');
        SugarTest.loadComponent('base', 'view', 'list-map');
        SugarTest.loadComponent('base', 'view', viewName);

        SugarTest.loadComponent('base', 'field', 'bing-map');

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module: 'Accounts',
            model: app.data.createBean('Accounts')
        });

        app.config.maps = {
            modulesData: {
                'Accounts': {}
            }
        };

        initOptions = {
            type: 'subpanel-map',
            name: 'subpanel-map',
            def: {
                view: viewName
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

    describe('createMap', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, 'subpanel-map');

            setupMapField();
            view.initialize(initOptions);
            view.render();
        });

        afterEach(function() {
            view.dispose();
        });

        it('should properly create locations', function() {
            const locationKey = _.chain(view._locations)
                                .keys()
                                .first()
                                .value();

            expect(Object.keys(view._locations).length).toNotEqual(0);
            expect(view._locations[locationKey].latitude).toEqual('45.302321');
            expect(view._locations[locationKey].longitude).toEqual('32.378383');
            expect(view._locations[locationKey].name).toEqual('Geocode A');
            expect(view._locations[locationKey].geocoded).toEqual(true);
        });

        it('should properly create the map controller', function() {
            expect(view._mapController).toNotEqual(null);
            expect(view._mapController.type).toEqual('bing-map');
        });
    });

    describe('dispose', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, 'subpanel-map');

            setupMapField();
            view.initialize(initOptions);
            view.render();
        });

        afterEach(function() {
            view.dispose();
        });

        it('should properly call the _disposeMap function', function() {
            expect(view._mapController).toNotEqual(null);
            view.dispose();
            expect(view._mapController).toEqual(null);
        });
    });

    function setupMapField() {
        sinon.stub(view, '_fetchRecordsLocationData').callsFake(function() {
            const modelA = app.data.createBean('Geocode', {
                name: 'Geocode A',
                longitude: '32.378383',
                latitude: '45.302321',
                geocoded: true
            });
            const geocodeCollection = app.data.createBeanCollection('Geocode', [modelA]);

            view._buildMap({}, 1, geocodeCollection);
        });
    }
});

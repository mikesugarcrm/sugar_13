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
describe('Base.Views.MapsDashlet', function() {
    var app;
    var view;
    var layout;
    var context;
    var moduleName = 'Accounts';
    var viewName = 'maps-dashlet';
    var initOptions;
    var layoutName = 'dashlet-grid-wrapper';
    var sandbox = sinon.createSandbox();

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        sinon.stub(app.api, 'call').callsFake(function() {});

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', viewName);
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'noaccess');
        SugarTest.loadComponent('base', 'view', 'list-map');
        SugarTest.loadComponent('base', 'view', viewName);

        SugarTest.loadComponent('base', 'field', 'bing-map');

        app.config.maps = {
            modulesData: {
                'Accounts': {}
            }
        };

        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                'panels': [
                    {
                        fields: ['maps_display_type']
                    }
                ]
            }
        );
        SugarTest.loadPlugin('Dashlet');

        SugarTest.testMetadata.set();

        const accountModel = app.data.createBean('Accounts', {
            name: 'Geocoded Account'
        });

        context = app.context.getContext();
        context.parent = new Backbone.Model();

        context.parent.set('module', moduleName);
        context.prepare();

        const collection = app.data.createBeanCollection(moduleName, [accountModel]);

        context.set({
            module: moduleName,
            model: app.data.createBean(moduleName),
            collection: collection
        });

        initOptions = {
            type: viewName,
            name: viewName,
            def: {
                view: viewName
            },
            module: moduleName,
            context: context,
            collection: collection,
            meta: {
                panels: [
                    {
                        fields: ['maps_display_type']
                    }
                ],
                config: true,
            }
        };

        layout = app.view.createLayout({
            name: layoutName,
            type: layoutName,
            context: context
        });

        if (app && app.user) {
            let licenses = app.user.get('licenses');

            if (licenses && _.isArray(licenses)) {
                licenses.push('MAPS');
            } else {
                licenses = ['MAPS'];
            }

            app.user.set('licenses', licenses);
        }
    });

    afterEach(function() {
        sandbox.restore();
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinon.restore();

        view = null;
        app = null;
    });

    describe('createMap', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', '', viewName, null, context, null, layout);

            sinon.stub(view, '_noAccessTemplate').callsFake(function() { return 'maps-dashlet.noaccess';});

            setupMapField();
            view.initialize(initOptions);
            view._render();
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
            view = SugarTest.createView('base', '', viewName, null, context, null, layout);
            view.meta = {};
            view.meta.config = false;

            sinon.stub(view, '_noAccessTemplate').callsFake(function() { return 'maps-dashlet.noaccess';});

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

        sinon.stub(view, '_hasMapAccess').callsFake(function() {
            return true;
        });
    }
});

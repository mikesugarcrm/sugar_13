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
describe('Base.Views.MapsManualGeocodingView', function() {
    var app;
    var view;
    var viewName = 'maps-manual-geocoding';
    var button;
    var initOptions;
    let context;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();

        sinon.stub(app.api, 'call').callsFake(function() {});

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);

        SugarTest.loadComponent('base', 'field', 'bing-map');

        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module: 'Accounts',
            model: app.data.createBean('Accounts')
        });

        initOptions = {
            type: 'maps-manual-geocoding',
            name: 'maps-manual-geocoding',
            def: {
                view: 'maps-manual-geocoding'
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

    describe('_storeLocationDataAndCreateMap', function() {
        beforeEach(function() {
            setupView();
            setupMapField();
            view.initialize(initOptions);
            view.render();
        });

        afterEach(function() {
            view.dispose();
        });

        it('should properly get geocode record', function() {
            expect(view._geocodeRecord).toNotEqual(null);
            expect(view._geocodeRecord.get('latitude')).toEqual('45.302321');
            expect(view._geocodeRecord.get('longitude')).toEqual('32.378383');
        });

        it('should properly create the map controller', function() {
            expect(view._mapController).toNotEqual(null);
            expect(view._mapController.type).toEqual('bing-map');
        });
    });

    describe('render()', function() {
        beforeEach(function() {
            setupView();
            view.render();
        });

        it('should properly create map controller', function() {
            expect(view._select2).toNotEqual(null);
            expect(view._rendered).toEqual(true);
            expect(view._select2['search-by-address']).toNotEqual(undefined);
        });
    });

    describe('onMapClick()', function() {
        beforeEach(function() {
            setupView();
            setupMapField();

            view.initialize(initOptions);
            view.render();
            sinon.stub(view, '_createLocation').callsFake(function() {});
            view._mapController._searchManager = {
                reverseGeocode: function(opts) {
                    if (opts.callback) {
                        opts.callback({
                            location: {
                                latitude: opts.location.latitude,
                                longitude: opts.location.longitude,
                            },
                            address: {
                                postalCode: '010120',
                                formattedAddress: 'test address',
                            }
                        });
                    }
                }
            };

            view.onMapClick({
                location: {
                    latitude: '123.146321',
                    longitude: '134.688421'
                }
            });
        });

        it('should properly create map controller', function() {
            expect(view._geocodeRecord.get('latitude')).toEqual('123.146321');
            expect(view._geocodeRecord.get('longitude')).toEqual('134.688421');
        });
    });

    describe('dispose', function() {
        beforeEach(function() {
            setupView();
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
        sinon.stub(view, '_fetchRecordLocationData').callsFake(function() {
            const modelA = app.data.createBean('Geocode', {
                name: 'Geocode A',
                longitude: '32.378383',
                latitude: '45.302321',
                geocoded: true
            });
            const geocodeCollection = app.data.createBeanCollection('Geocode', [modelA]);

            view._storeLocationDataAndCreateMap(geocodeCollection);
        });
    }

    function setupView() {
        view = SugarTest.createView('base', '', viewName);

        button = SugarTest.createField({
            client: 'base',
            name: 'save_button',
            type: 'button',
            viewName: 'detail',
            fieldDef: {
                label: 'LBL_SAVE_BUTTON_LABEL'
            }
        });

        sinon.stub(button, 'setDisabled').withArgs(true).returns(true);

        var fieldName = 'save_button';

        view.fields[fieldName] = button;
    }
});

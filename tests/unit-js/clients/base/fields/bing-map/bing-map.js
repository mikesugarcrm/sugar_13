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
describe('Base.Field.BingMap', function() {
    var app;
    var field;
    var sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        window.Microsoft = {
            Maps: {
                Map: function Map() {
                    return {
                        getMapLoadedEvent: function getMapLoadedEvent() {
                            return {
                                addOne: function addOne() {
                                    return;
                                }
                            };
                        },
                    };
                },
                loadModule: function() { },
                Events: {
                    addHandler: function addHandler() {

                    },
                },
                Location: function Location() {
                    return {};
                },
                Pushpin: function Pushpin(location, pushpinMeta) {
                    return {};
                },
                MapTypeId: function MapTypeId() {
                    return {};
                },
                NavigationBarMode: function NavigationBarMode() {
                    return {};
                },
            }
        };

        if (!app.router) {
            app.router = {
                buildRoute: function() {
                    return '';
                }
            };
        }

        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate('bing-map', 'field', 'base', 'main-map-container');
        SugarTest.loadComponent('base', 'field', 'bing-map');

        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        delete window.Microsoft;
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'testField', 'bing-map', 'main-map-container');
        });

        afterEach(function() {
            field.dispose();
        });

        it('should initialize properties', function() {
            expect(field._map).toEqual(null);
            expect(field._searchManager).toEqual(null);
            expect(field._locations).toEqual([]);
            expect(field._pushPins).toEqual([]);
        });
    });

    describe('_buildMap', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'testField', 'bing-map', 'main-map-container');
            field.render();
            field._buildMap('bingMapCredentialTest');
        });

        afterEach(function() {
            field.dispose();
        });

        it('should properly create Microsoft Map controller', function() {
            expect(field._map).toNotEqual(null);
        });
    });

    describe('createLocation', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'testField', 'bing-map', 'main-map-container');
            field.render();
        });

        afterEach(function() {
            field.dispose();
        });

        it('should add locations', function() {
            expect(field._locations.length).toEqual(0);

            field.createLocation({
                latitude: '31.334567',
                longitude: '39.842633',
                address: 'testAddress',
                assignedUserName: 'userName',
                name: 'Calafat'
            });

            expect(field._locations.length).toEqual(1);
        });
    });

    describe('createPushPins', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'testField', 'bing-map', 'main-map-container');
            field.render();

            field.createLocation({
                latitude: '31.334567',
                longitude: '39.842633',
                address: 'testAddress',
                assignedUserName: 'userName',
                name: 'Calafat'
            });
        });

        afterEach(function() {
            field.dispose();
        });

        it('should create pushpins from locations', function() {
            expect(field._pushPins.length).toEqual(0);

            field.createPushPins();

            expect(field._pushPins.length).toEqual(1);
        });
    });
});

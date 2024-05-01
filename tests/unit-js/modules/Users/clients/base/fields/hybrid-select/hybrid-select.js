
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
describe('Base.Users.HybridSelect', function() {
    var app;
    var field;
    var model;
    var module = 'Users';
    var mockDrawerCount;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('hybrid-select', 'field', 'base', 'edit', 'Users');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField(
            'base',
            'select_test',
            'hybrid-select',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

        drawerBefore = app.drawer;
        app.drawer = {
            count: function() {
                return mockDrawerCount;
            },
            reset: sinon.stub(),
            open: sinon.stub()
        };
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('initialize', function() {
        it('should init the mass collection with an empty collection', function() {
            // check that the mass collection is defined
            expect(field.massCollection).toBeDefined();

            // type checking the mass collection
            expect(field.massCollection).toEqual(jasmine.any(app.data.beanCollection));

            // check that it has a 'models' property and it is an array
            expect(field.massCollection.models).toEqual(jasmine.any(Array));

            // check the models array is empty
            expect(field.massCollection.models.length).toBe(0);
        });
    });

    describe('render', function() {
        it('should call the setSelect function', function() {
            var setSelectStub = sinon.stub(field, 'setSelect');
            field.render();
            // expect for the setSelect function to be called
            expect(setSelectStub).toHaveBeenCalled();
        });
    });

    describe('openDrawer', function() {
        it('should open the multi-selection-list drawer', function() {
            field.openDrawer(new Event('click'));
            // expect for the drawer 'open function to be called once'
            expect(app.drawer.open.callCount).toBe(1);

            // expect to be called with this arguments
            expect(app.drawer.open.lastCall.args[0]).toEqual({
                context: {
                    module: field.selectModule,
                    isMultiSelect: true,
                    mass_collection: field.massCollection,
                    loadModule: field.selectModule,
                },
                layout: 'multi-selection-list',
            });
        });
    });
});

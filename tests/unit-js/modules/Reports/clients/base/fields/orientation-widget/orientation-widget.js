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
describe('Reports.Fields.OrientationWidget', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            client: 'base',
            name: 'orientation-widget',
            type: 'orientation-widget',
            viewName: 'detail',
            module: 'Reports',
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('initialize', function() {
        it('should set properties appropriately', function() {
            field.initialize({});

            expect(field.HORIZONTAL).toEqual('horizontal');
            expect(field.VERTICAL).toEqual('vertical');
            expect(field._orientation).toEqual('horizontal');
        });
    });

    describe('changeOrientation', function() {
        it('should change button state', function() {
            field.changeOrientation({
                currentTarget: {
                    id: 'vertical',
                },
            });

            expect(field._orientation).toEqual('vertical');
        });
    });

    describe('setOrientation', function() {
        it('should change orientation from outside the controller', function() {
            field.setOrientation({
                direction: 'vertical',
            });

            expect(field._orientation).toEqual('vertical');
        });
    });
});

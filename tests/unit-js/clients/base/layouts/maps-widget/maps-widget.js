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
describe('Base.Layout.MapsWidget', function() {
    var app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize()', function() {
        var testLayout;
        var initOptions;

        beforeEach(function() {
            var context = app.context.getContext();
            context.set({
                module: 'Accounts',
                layout: 'maps-widgets',
            });

            context.prepare();

            initOptions = {
                context: context,
            };

            testLayout = SugarTest.createLayout('base', '', 'maps-widget', {});

            sinon.stub(testLayout, '_getMapsContextModule').callsFake(function() {
                return 'Accounts';
            });

            testLayout.initialize(initOptions);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('will set context atttributes properly', function() {
            expect(testLayout.context.get('module')).toEqual('Accounts');
        });
    });
});

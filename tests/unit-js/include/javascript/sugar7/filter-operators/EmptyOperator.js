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
describe('FilterOperators.EmptyOperator', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadFile('../include/javascript/sugar7/filter-operators', 'EmptyOperator', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();
    });

    describe('EmptyOperator registration', function() {
        it('should have been added to the `app.filterOperators` registry', function() {
            expect(typeof app.filterOperators.EmptyOperator).toEqual('function');
        });
    });
});

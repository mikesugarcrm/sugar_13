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
describe('Data.Base.CalendarBean', function() {
    var app;
    var prototype;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.declareData('base', 'Calendar', true, false);

        prototype = app.data.getBeanClass('Calendar').prototype;
    });

    afterEach(function() {
        sinon.restore();

        SugarTest.testMetadata.dispose();
    });

    describe('calculateMatchScore()', function() {
        var module = 'Calls';

        beforeEach(function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_CALENDAR_START_DATE', module)
                .returns('Start Date');
            sinon.stub(prototype, 'get')
                .withArgs('calendar_module')
                .returns(module);
        });

        it('should calculate a score for a field used in calendar based on target module fields', function() {
            var textInput = 'startdate';
            var fieldDefInput = {
                name: 'date_start',
                vname: 'LBL_CALENDAR_START_DATE'
            };

            var result = prototype.calculateMatchScore(textInput, fieldDefInput);
            expect(result).toBe(1);
        });
    });
});

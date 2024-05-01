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

describe('Notifications html field', function() {
    var app;
    var field;
    var langStub;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'description', 'html', 'detail', null, 'Notifications', null, null, true);
        field.model.set({created_by: 1, created_by_name: 'Admin'});
        langStub = sinon.stub(app.lang, 'get');
        langStub.withArgs('LBL_YOU_HAVE_BEEN_MENTIONED_BY', 'Notifications').returns('You have been mentioned by');
        app.routing.start();
    });

    afterEach(function() {
        field.dispose();
        sinon.restore();
        app.routing.stop();
    });

    describe('format', function() {
        it('should translate LBL_YOU_HAVE_BEEN_MENTIONED_BY', function() {
            var value = 'LBL_YOU_HAVE_BEEN_MENTIONED_BY';
            var formattedValue = field.format(value);
            var expected = 'You have been mentioned by ' +
                '<a href="#bwc/index.php?module=Employees&action=DetailView&record=1">Admin</a>';
            expect(formattedValue).toEqual(expected);
        });
    });
})

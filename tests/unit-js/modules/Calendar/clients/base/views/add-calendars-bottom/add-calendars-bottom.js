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
describe('View.Views.Base.Calendar.AddCalendarsBottomView', function() {
    var app;
    var view;
    var model;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'add-calendars-bottom', module);
        SugarTest.declareData('base', module, true, true);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        view = SugarTest.createView(
            'base',
            module,
            'add-calendars-bottom',
            null,
            context,
            true,
            model,
            true
        );
    });

    afterEach(function() {
        view.dispose();
        model = null;
        view = null;
    });

    describe('setShowMoreLabel', function() {
        it('should set a label for show more link', function() {
            var modStringMock = sinon.stub(app.lang, 'getModString').returns('Show More');

            view.setShowMoreLabel();

            expect(view.showMoreLabel).toEqual('Show More');

            modStringMock.restore();
        });
    });
});

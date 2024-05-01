
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
describe('Base.Calendar.ModuleMenuView', function() {
    var app;
    var view;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        context = app.context.getContext();

        context.set({
            module: module,
            model: new Backbone.Model(),
        });

        context.prepare();

        view = SugarTest.createView('base', 'Calendar', 'module-menu', null, context, true);
    });

    afterEach(function() {
        view.dispose();
        view = null;
    });

    describe('populateMenu()', function() {
        var populateStub;

        beforeEach(function() {
            populateStub = sinon.stub(view, 'populate');
        });

        it('should call populate', function() {
            view.populateMenu();
            expect(view.populate).toHaveBeenCalled();
            expect(populateStub.getCall(0).args[0]).toEqual('calendars');
        });
    });

    describe('getCollection(tplName)', function() {
        beforeEach(function() {
            view._collections = {};
        });

        it('should initialize a list', function() {
            var collection = view.getCollection('calendars');
            expect(collection).toEqual([]);
        });
    });
});

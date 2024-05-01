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
describe('Base.View.Attachments', function() {
    var app, view, moduleName = 'Contacts', viewName = 'attachments', layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', moduleName, 'dashboard');
        view = SugarTest.createView('base', moduleName, viewName, {}, null, null, layout);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        sinon.restore();
        SugarTest.testMetadata.dispose();
    });

    describe('dispose safe', function() {
        it('should dispose interval safe', function() {
            view.timerId = 'fakeID';
            var intervalStub = sinon.stub(window, 'clearInterval');
            expect(intervalStub).not.toHaveBeenCalled();

            view.dispose();
            expect(intervalStub).toHaveBeenCalled();
        });
    });

    describe('_getBaseModel', function() {
        using('different modules and names',
            [
                {module: 'Accounts'},
                {module: 'Contacts'},
                {module: 'Opportunities'},
                {module: 'Leads'},
                {module: 'Cases'},
                {module: 'RevenueLineItems'},
                {module: 'Bugs'},
            ],
            function(input) {
                it('should get model from parent context', function() {
                    var layout = SugarTest.createLayout(
                        'base',
                        'Notes',
                        'dashboard'
                    );
                    var view = SugarTest.createView(
                        'base',
                        'Notes',
                        'attachments',
                        {},
                        null,
                        null,
                        layout
                    );

                    var testModel = app.data.createBean(input.module);
                    testModel.set('_module', input.module);

                    var parentContext = app.context.getContext();
                    parentContext.set({
                        module: input.module,
                        rowModel: testModel,
                    });

                    var mainContext = app.context.getContext();
                    mainContext.set({parentModule: input.module});
                    mainContext.parent = parentContext;

                    var model = view._getBaseModel({
                        context: mainContext,
                    });

                    expect(model).toEqual(testModel);
                });
            }
        );
    });
});

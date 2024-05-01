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
describe('Portal Signup View', function() {

    var view;
    var app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('signup', 'view', 'portal');
        SugarTest.testMetadata.addViewDefinition('signup', {
            panels: [
                {
                    fields: [
                        {
                            name: 'first_name'
                        },
                        {
                            name: 'last_name'
                        },
                        {
                            name: 'company_name'
                        },
                        {
                            name: 'email'
                        },
                        {
                            name: 'user_name'
                        },
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        view = SugarTest.createView('portal','Signup', 'signup');
        app = SUGAR.App;
        sinon.stub(app.metadata, 'getLogoUrl').returns('#');
        view.render();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        sinon.restore();
    });

    describe('Declare Sign Up Bean', function() {

        it('should have declared a Bean with the fields metadata', function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(view.model.fields.first_name).toBeDefined();
            expect(view.model.fields.last_name).toBeDefined();
            expect(view.model.fields.company_name).toBeDefined();
            expect(view.model.fields.email).toBeDefined();
            expect(view.model.fields.user_name).toBeDefined();
        });
    });
});

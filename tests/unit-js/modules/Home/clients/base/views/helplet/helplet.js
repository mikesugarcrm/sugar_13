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
describe('Base.Home.View.Helplet', function() {
    var view;
    var app = SUGAR.App;

    beforeEach(function() {
        sinon.stub(app.help, 'get');
        SugarTest.loadComponent('base', 'view', 'helplet', 'Home');
        view = SugarTest.createView('base', 'Home', 'helplet', {}, null, true);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        Handlebars.templates = {};
        sinon.restore();
    });

    describe('createHelpObject', function() {
        it('should set plural_module_name if it is a console', function() {
            var langStub = sinon.stub(app.lang, 'get');
            sinon.stub(app.controller.context, 'get').returns('c108bb4a-775a-11e9-b570-f218983a1c3e');
            view.createHelpObject();
            expect(langStub).toHaveBeenCalled();
        });
    });

    describe('sanitizeUrlParams', function() {
        it('should set params if it is a console', function() {
            sinon.stub(app.controller.context, 'get').returns('c108bb4a-775a-11e9-b570-f218983a1c3e');
            var actual = view.sanitizeUrlParams({route: 'asdf'});
            var expected = {
                module: 'ServiceConsole'
            };
            expect(actual.module).toEqual(expected.module);
            expect(actual.route).toBeUndefined();
        });
    });
});

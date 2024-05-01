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
describe('View.Views.Base.Home.AboutHeaderpaneView', function() {
    var platform = 'base';
    var moduleName = 'Home';
    var viewName = 'about-headerpane';
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadComponent(platform, 'view', viewName, moduleName);

        // This is a strange method signature, in that if you pass a module name,
        // it doesn't mean anything to the component loader. In fact, to load a
        // component from the module, you have to specify a boolean true for the
        // sixth argument.
        //
        // For reference, the argument list is...
        // client, module, viewName, meta, context, loadFromModule, layout, loadComponent
        view = SugarTest.createView(platform, moduleName, viewName, {}, null, true);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    using('different values for marketing version',[{
        version: '',
        expect: ''
    },{
        version: 'Fall \'18',
        expect: ' (Fall \'18)'
    },{
        version: '  FooBar   ',
        expect: ' (FooBar)'
    },{
        version: '   ',
        expect: ''
    },{
        version: null,
        expect: ''
    }], function(meta) {
        it('should return the expected value', function() {
            var result = view._getMarketingVersion(meta.version);
            expect(result).toBe(meta.expect);
        });
    });
});

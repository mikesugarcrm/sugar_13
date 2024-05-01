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
describe("BaseLanguageActionsView", function() {

    var view, app, languageListStub;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", undefined, "language-actions");
        app = SUGAR.App;

        languageListStub = sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {
                '': '',
                en_us: 'English (US)',
                fr_FR: 'French',
                it_it: 'Italiano',
                nl_NL: 'Nederlands'
            };
        });
    });

    afterEach(function() {
        languageListStub.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    describe("formatLanguageList", function() {

        it("should format an array of language objects", function() {
            var expected = [
                { key: 'en_us', value: 'English (US)'},
                { key: 'fr_FR', value: 'French'},
                { key: 'it_it', value: 'Italiano'},
                { key: 'nl_NL', value: 'Nederlands'}
            ];
            expect(view.formatLanguageList()).toEqual(expected);
        });

    });

});

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
describe('DocumentMerges.View.TagBuilderModule', function() {
    var app;
    var sinonSandbox;
    var view;

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadComponent('base', 'view', 'tag-builder-module', 'DocumentMerges');

        SugarTest.testMetadata.set();
        app = SugarTest.app;

        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView('base', 'DocumentMerges', 'tag-builder-module', null, null, true);
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('render', function() {
        beforeEach(function() {
            initializeDropDownStub = sinonSandbox.stub(view, 'initializeDropDown');
        });
        it('should set current module', function() {
            view.render();
            expect(initializeDropDownStub).toHaveBeenCalled();
            expect(view.context.get('currentModule')).toBe(null);
        });
    });
});

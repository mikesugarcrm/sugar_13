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

describe('DRI_Workflow_Templates.Views.RecdordList', function() {
    let app;
    let view;
    let moduleName = 'DRI_Workflow_Templates';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        sinon.stub(app.CJBaseHelper, 'invalidLicenseError');
        sinon.stub(app.user, 'hasAutomateLicense').returns(false);
        sinon.stub(Backbone.history, 'getFragment').returns(moduleName);
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        SugarTest.testMetadata.dispose();

        app = null;
        view = null;
    });

    describe('initialize', function() {
        it('should call hasAutomateLicense and CJBaseHelper invalidLicenseError', function() {
            view = SugarTest.createView(
                'base',
                moduleName,
                'recordlist'
            );

            expect(app.user.hasAutomateLicense).toHaveBeenCalled();
            expect(app.CJBaseHelper.invalidLicenseError).toHaveBeenCalled();
        });
    });
});

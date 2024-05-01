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

describe('Base.Layout.ActivityFilter', function() {
    var layout;

    beforeEach(function() {
        layout = SugarTest.createLayout('base', 'layout', 'activity-filter', {});
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.updateModuleMetadata('Calls', {});
        SugarTest.testMetadata.set();
        app = SugarTest.app;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.restore();
        layout.dispose();
        layout = null;
        app.cache.cutAll();
        app.view.reset();
    });

    describe('getFilterList', function() {

        it('should get filter module list', function() {
            layout.module = 'Cases';
            sinon.stub(layout.context, 'get').withArgs('enabledModules').returns(['Calls']);
            let getModuleStub = sinon.stub(app.metadata, 'getModule');
            getModuleStub.withArgs('Cases').returns({isAudited: true});
            getModuleStub.withArgs('Calls').returns(true);

            const expected = [
                {
                    id: 'all_modules',
                    text: 'LBL_LINK_ALL',
                },
                {
                    id: 'Audit',
                    text: 'LBL_MODULE_NAME_SINGULAR LBL_UPDATES',
                },
                {
                    id: 'Calls',
                    text: 'LBL_MODULE_NAME',
                },
            ];

            expect(layout.getFilterList()).toEqual(expected);
        });
    });
});

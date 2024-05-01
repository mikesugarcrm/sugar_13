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
describe('Base.Layout.SelectionListModuleSwitch', function() {
    var app, layout, langGetStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        langGetStub = sinon.stub(app.lang, 'get').callsFake(function(label, module) {
            return module;
        });

        layout = SugarTest.createLayout('base', 'Contacts', 'selection-list-module-switch');
    });

    afterEach(function() {
        layout.dispose();
        langGetStub.restore();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
    });

    describe('_buildModuleSwitchList', function() {
        it('Should return a list of modules as objects of ID and text', function() {
            var result = layout._buildModuleSwitchList(['Accounts', 'Contacts', 'Leads']);

            expect(result).toEqual([{
                id: 'Accounts',
                text: 'Accounts'
            }, {
                id: 'Contacts',
                text: 'Contacts'
            }, {
                id: 'Leads',
                text: 'Leads'
            }]);
        });

        it('Should only return modules that user has access to', function() {
            var result,
                hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function(action, module) {
                    return (module !== 'Contacts');
                });

            result = layout._buildModuleSwitchList(['Accounts', 'Contacts', 'Leads']);

            expect(result).toEqual([{
                id: 'Accounts',
                text: 'Accounts'
            }, {
                id: 'Leads',
                text: 'Leads'
            }]);

            hasAccessStub.restore();
        });
    });
});

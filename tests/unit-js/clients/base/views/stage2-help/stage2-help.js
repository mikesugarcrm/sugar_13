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
describe('Base.View.Stage2Help', function() {
    var app;
    var view;
    var module;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'Home';

        app.hint = {
            isDarkMode: function() {},
        };

        sinon.stub(app.user, 'hasHintLicense').callsFake(function() {
            return true;
        });

        view = SugarTest.createView('base', module, 'stage2-help');
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should have conctacts attributes', function() {
            expect(_.contains(view._detectAttrsContacts, 'first_name')).toBeTruthy();
            expect(_.contains(view._detectAttrsContacts, 'last_name')).toBeTruthy();
        });

        it('should have leads attributes', function() {
            expect(_.contains(view._detectAttrsLeads, 'first_name')).toBeTruthy();
            expect(_.contains(view._detectAttrsLeads, 'last_name')).toBeTruthy();
            expect(_.contains(view._detectAttrsLeads, 'account_name')).toBeTruthy();
            expect(_.contains(view._detectAttrsLeads, 'website')).toBeTruthy();
            expect(_.contains(view._detectAttrsLeads, 'title')).toBeTruthy();
            expect(_.contains(view._detectAttrsLeads, 'phone_work')).toBeTruthy();
        });

        it('should have related modules', function() {
            expect(_.contains(view._relatedModules, 'Contacts')).toBeTruthy();
            expect(_.contains(view._relatedModules, 'Leads')).toBeTruthy();
            expect(_.contains(view._relatedModules, 'Accounts')).toBeTruthy();
        });
    });
});

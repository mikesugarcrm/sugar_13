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
describe("Leads.Views.ConvertResults", function() {
    var app, view, createBeanStub,
        accountId = '123',
        accountName = 'acc',
        contactId = '456',
        contactName = 'con',
        opportunityId = '789',
        opportunityName = 'opp';
    var convertedOpportunityName = 'opp';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('convert-results', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'convert-results');
        SugarTest.loadComponent('base', 'view', 'convert-results', 'Leads');

        SugarTest.testMetadata.set();

        createBeanStub = sinon.stub(app.data, 'createBean').callsFake(function(moduleName, attributes) {
            return new app.Bean(attributes);
        });

        view = SugarTest.createView('base', 'Leads', 'convert-results', null, null, true);
        view.model.set('converted', true);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        createBeanStub.restore();
    });

    it("should have account model with name in collection", function() {
        view.model.set({
            account_id: accountId,
            account_name: accountName
        });
        expect(view.associatedModels.length).toEqual(1);
        expect(view.associatedModels.get(accountId).get('name')).toEqual(accountName);
    });

    it("should have account model with no name in collection", function() {
        view.model.set({
            account_id: accountId
        });
        expect(view.associatedModels.length).toEqual(1);
        expect(view.associatedModels.get(accountId).get('name')).toBeUndefined();
    });

    it("should have account and contact models in collection", function() {
        view.model.set({
            account_id: accountId,
            account_name: accountName,
            contact_id: contactId,
            contact_name: contactName
        });
        expect(view.associatedModels.length).toEqual(2);
        expect(view.associatedModels.get(accountId).get('name')).toEqual(accountName);
        expect(view.associatedModels.get(contactId).get('name')).toEqual(contactName);
    });

    it("should have account, contact and opportunity models in collection", function() {
        view.model.set({
            account_id: accountId,
            account_name: accountName,
            contact_id: contactId,
            contact_name: contactName,
            opportunity_id: opportunityId,
            opportunity_name: opportunityName,
            converted_opp_name: convertedOpportunityName
        });
        expect(view.associatedModels.length).toEqual(3);
        expect(view.associatedModels.get(accountId).get('name')).toEqual(accountName);
        expect(view.associatedModels.get(contactId).get('name')).toEqual(contactName);
        expect(view.associatedModels.get(opportunityId).get('name')).toEqual(convertedOpportunityName);
    });

    it("should not have any models in the collection if lead is not converted, even if contact is related", function() {
        view.model.set({
            converted: false,
            contact_id: contactId,
            contact_name: contactName
        });
        expect(view.associatedModels.length).toEqual(0);
    });
});

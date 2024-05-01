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
describe('DataPrivacy MarkForEraseButton', function() {
    var app;
    var field;
    var context;
    var hasAccessStub;
    var mockAccess;
    var moduleName = 'DataPrivacy';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.testMetadata.set();

        // Mocking app.acl.hasAccess. Has access to Leads and DataPrivacy
        mockAccess = {
            Leads: true,
            DataPrivacy: true
        };
        hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function(access, module) {
            return (_.isUndefined(mockAccess[module])) ? false : mockAccess[module];
        });

        // Mocking app.metadata.etModule. One field has PII field
        getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function(module) {
            return {my_field: {type: 'varchar', pii: true}};
        });

        context = app.context.getContext({module: 'Leads'});
        context.parent = app.context.getContext({module: moduleName});
        context.parent.set('model', app.data.createBean(moduleName, {
            id: 'test',
            name: 'testName',
            type: 'Request to Erase Information',
            source: 'aaa',
            status: 'Open'
        }));

        var def = {};
        var model = app.data.createBean('Leads', {
            id: 'test',
            name: 'testName'
        });

        // create the custom field to be tested
        field = SugarTest.createField(
            'base',
            'dataprivacy-erase',
            'dataprivacyerase',
            'detail',
            def,
            null,
            model,
            context,
            true
        );
        field.module = 'Leads';
    });

    afterEach(function() {
        getModuleStub.restore();
        hasAccessStub.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field = null;
        context = null;
    });

    it('should hide if no access to related module', function() {
        hasAccessStub.restore();
        mockAccess = {
            Leads: false,
            DataPrivacy: true
        };
        hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function(access, module) {
            return (_.isUndefined(mockAccess[module])) ? false : mockAccess[module];
        });
        field._render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should hide if no access to DataPrivacy', function() {
        hasAccessStub.restore();
        mockAccess = {
            Leads: true,
            DataPrivacy: false
        };
        hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function(access, module) {
            return (_.isUndefined(mockAccess[module])) ? false : mockAccess[module];
        });
        field._render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should hide if no PII fields', function() {
        getModuleStub.restore();
        getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function(module) {
            return {my_field: {type: 'varchar'}};
        });
        field._render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should hide if status is not Open', function() {
        context.parent.get('model').set('status', 'Closed');
        field._render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should hide if type is not Request to Erase Information', function() {
        context.parent.get('model').set('type', 'Request for Data Privacy Policy');
        field._render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should show if all conditions are met', function() {
        field._render();
        expect(field.isHidden).toBeFalsy();
    });
});

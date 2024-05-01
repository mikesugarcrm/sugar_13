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
describe('Close Button Field', function() {
    var app, field, sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'list');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'closebutton');
        SugarTest.testMetadata.set();
        app.data.declareModels();

        field = SugarTest.createField('base', 'record-close', 'closebutton', 'list', {
            'name': 'record-close',
            'type': 'closebutton',
            'acl_action': 'edit',
            'closed_status': 'Completed'
        }, 'Tasks', app.data.createBean('Tasks'));

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
    });

    it('should show the button if the record is not closed', function() {
        field.model.set('status', 'Not Started');
        expect(field.isVisible()).toBe(true);
    });

    it('should hide the button if the record is closed', function() {
        field.model.set('status', 'Completed');
        expect(field.isVisible()).toBe(false);
    });

    it('should set record to completed when closeClicked() is called', function() {
        var saveStub = sandbox.stub(field.model, 'save');

        field.model.set('status', 'Not Started');
        field.closeClicked();

        expect(saveStub.calledOnce).toBe(true);
        expect(field.model.get('status')).toBe('Completed');
    });

    it('should revert status to previous value on error', function() {
        var saveStub = sandbox.stub(field.model, 'save').callsFake(function(dummy, callbacks) {
            callbacks.error();
        });

        field.model
            .set('status', 'Not Started')
            .trigger('sync');

        field.closeClicked();

        expect(saveStub.calledOnce).toBe(true);
        expect(field.model.get('status')).toBe('Not Started');
    });

    it('should call method to open drawer to create a new record when closeNewClicked() is called', function() {
        var openDrawerToCreateNewRecordStub = sandbox.stub(field, 'openDrawerToCreateNewRecord'),
            showSuccessMessageStub = sandbox.stub(field, 'showSuccessMessage');

        sandbox.stub(field.model, 'save').callsFake(function(dummy, callbacks) {
            callbacks.success();
        });

        field.closeNewClicked();

        expect(openDrawerToCreateNewRecordStub.calledOnce).toBe(true);
        expect(showSuccessMessageStub.calledOnce).toBe(true);
    });

    it('should show success message using app string value for status', function() {
        var langGetStub = sandbox.stub(app.lang, 'get');
        sandbox.stub(app.alert, 'show');
        sandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {fields: {status: {options: 'status_dom'}}};
        });
        sandbox.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {'Completed': 'Foo'};
        });

        field.showSuccessMessage();
        expect(langGetStub).toHaveBeenCalledWith('TPL_STATUS_CHANGE_SUCCESS', 'Tasks', {
            moduleSingular: 'Tasks',
            status: 'foo'
        });
    });

    it('should show success message using closed status value for status', function() {
        var langGetStub = sandbox.stub(app.lang, 'get');
        sandbox.stub(app.alert, 'show');
        sandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {fields: {status: {/* no options */}}};
        });
        sandbox.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {'Completed': 'Foo'};
        });

        field.showSuccessMessage();
        expect(langGetStub).toHaveBeenCalledWith('TPL_STATUS_CHANGE_SUCCESS', 'Tasks', {
            moduleSingular: 'Tasks',
            status: 'completed'
        });
    });
});

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
describe('Delete Recurrences Field', function() {
    var app, field, sinonSandbox,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.testMetadata.set();
        app.data.declareModels();

        sinonSandbox = sinon.createSandbox();

        field = SugarTest.createField({
            name: 'delete-recurrence-button',
            type: 'deleterecurrencesbutton',
            viewName: 'detail',
            fieldDef: {},
            module: module,
            model: app.data.createBean('Meetings')
        });
        field.model.module = 'Meetings';
        app.routing.start();

    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinonSandbox.restore();
        sinon.restore();
        field.model = null;
        field = null;
        app.router.stop();
    });

    it('should show the button if the meeting is a recurring meeting', function() {
        field.model.set('repeat_type', 'Weekly');
        expect(field.isVisible()).toBe(true);
    });

    it('should hide the button if the record is not a recurring meeting', function() {
        field.model.set('repeat_type', '');
        expect(field.isVisible()).toBe(false);
    });

    it('should hide the button if acl denies access', function() {
        var hasAccessStub = sinonSandbox.stub(app.view.fields.BaseButtonField.prototype, 'hasAccess').returns(false);
        field.model.set('repeat_type', 'Weekly');

        expect(field.isVisible()).toBe(false);
        hasAccessStub.restore();
    });

    describe('Hiding the button for non-recurring events', function() {
        var hideStub;

        beforeEach(function() {
            hideStub = sinonSandbox.stub(field, 'hide');
        });

        it('should not hide button when repeat_type field is set', function() {
            field.model.set('repeat_type', 'Weekly');
            expect(hideStub).not.toHaveBeenCalled();
        });

        it('should hide button when repeat_type field is not set', function() {
            field.model.set('repeat_type', '');
            expect(hideStub).toHaveBeenCalled();
        });
    });

    describe('Warning delete recurrences', function() {
        var alertShowStub, navigateStub, refreshStub, apiCallStub;

        beforeEach(function() {
            navigateStub = sinonSandbox.stub(app.router, 'navigate');
            refreshStub = sinonSandbox.stub(app.router, 'refresh');
            alertShowStub = sinonSandbox.stub(app.alert, 'show');
            apiCallStub = sinonSandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks, options) {
                callbacks.success();
            });
        });

        it('should prompt the user for deleting all recurrences of a meeting', function() {
            field.rowActionSelect();
            expect(navigateStub).not.toHaveBeenCalled();
            expect(refreshStub).not.toHaveBeenCalled();
            expect(alertShowStub).toHaveBeenCalled();
            expect(alertShowStub.lastCall.args[0]).toEqual('delete_recurrence_confirmation');
        });

        it('should refresh the route when the history fragment route is the same as the destination route', function() {
            sinonSandbox.stub(Backbone.history, 'getFragment').callsFake(function() {
                return module;
            });

            field.deleteRecurrences();
            expect(refreshStub).toHaveBeenCalled();
            expect(navigateStub).not.toHaveBeenCalled();
        });

        it('should navigate the user to the module route when history fragement route is not same as destination route', function() {
            sinonSandbox.stub(Backbone.history, 'getFragment').callsFake(function() {
                return 'Meetings/123456679708FAED12';
            });

            field.deleteRecurrences();
            expect(refreshStub).not.toHaveBeenCalled();
            expect(navigateStub).toHaveBeenCalled();
        });
    });
});

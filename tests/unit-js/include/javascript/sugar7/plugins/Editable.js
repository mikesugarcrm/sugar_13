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
describe("Editable Plugin", function() {
    var moduleName = 'Accounts',
        sinonSandbox, view, app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'businesscard');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", "description","case_number","type","created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.createSandbox();
        view = SugarTest.createView("base", moduleName, 'record', null, null);
        app.routing.start();
    });

    afterEach(function() {
        view.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        view = null;
        app.router.stop();
    });

    it("Should toggle a single field to edit modes", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });

        var keys = _.keys(view.fields),
            randomFieldIndex = parseInt(Math.random() * (keys.length - 1), 10),
            randomField = view.fields[keys[randomFieldIndex]];

        var hasChangedStub = sinon.stub(randomField, 'hasChanged').callsFake(function() {
            return false;
        });

        expect(randomField.tplName).toBe(view.action);
        view.toggleField(randomField, true);
        expect(randomField.tplName).toBe('edit');
        view.toggleField(randomField);
        expect(randomField.tplName).toBe(view.action);
        hasChangedStub.restore();
    });

    it("Should switch back to the previous mode when it triggers editableHandleMouseDown", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });
        app.drawer = {
            _components: []
        };

        var keys = _.keys(view.fields),
            randomFieldIndex = parseInt(Math.random() * (keys.length - 1), 10),
            randomField = view.fields[keys[randomFieldIndex]];

        var hasChangedStub = sinon.stub(randomField, 'hasChanged').callsFake(function() {
            return false;
        });

        view.toggleField(randomField, true);
        expect(randomField.tplName).toBe('edit');
        view.editableHandleMouseDown({target: null}, randomField);
        expect(randomField.tplName).toBe(view.action);
        hasChangedStub.restore();

        delete app.drawer;
    });

    it("Should toggle all selected fields to edit modes", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });
        _.each(view.fields, function(field) {
            expect(field.tplName).toBe(view.action);
        });

        view.toggleFields(_.values(view.fields), true);

        waitsFor(function() {
            var last = _.last(_.keys(view.fields));
            return view.fields[last].tplName == 'edit';
        }, 'it took too long to wait switching view', 1000);

        runs(function() {
            _.each(view.fields, function(field) {
                expect(field.tplName).toBe('edit');
            });
        });
    });

    it('Should call the callback function when all fields have been toggled', function() {
        var callbackStub = sinonSandbox.stub();

        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });

        view.toggleFields(_.values(view.fields), true, callbackStub);

        waitsFor(function() {
            return callbackStub.calledOnce;
        }, 'Callback did not get called in time.', 1000);

        runs(function() {
            _.each(view.fields, function(field) {
                expect(field.tplName).toBe('edit');
            });
        });
    });

    it('should trigger editable:toggle_fields on the view when all fields have been toggled', function() {
        var spy = sinonSandbox.spy();

        view.on('editable:toggle_fields', spy);
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });

        view.toggleFields(_.values(view.fields), true);

        waitsFor(function() {
            return spy.calledOnce;
        }, 'The event did not get triggered in time.', 1000);

        runs(function() {
            _.each(view.fields, function(field) {
                expect(field.tplName).toBe('edit');
            });
        });
    });

    describe('beforeViewChange', function() {
        using('different arguments', [null, {not: 'a callback'}, {callback: 'string not a function'}], function(arg) {
            it('should warn if there is no callback provided', function() {
                var errorStub = sinonSandbox.stub(app.logger, 'error');
                expect(view.beforeViewChange(arg)).toBe(true);
                expect(errorStub).toHaveBeenCalledWith('Custom unsavedchange must contain callback function.');
            });
        });

        it('should call the callback after confirming the unsaved changes warning', function() {
            var warnUnsavedChangesStub = sinonSandbox.stub(view, 'warnUnsavedChanges');
            warnUnsavedChangesStub.callsArg(0);
            warnUnsavedChangesStub.returns(false);
            var stubCallback = sinonSandbox.stub();
            var params = {callback: stubCallback, message: 'I am a custom message'};
            expect(view.beforeViewChange(params)).toBe(false);
            expect(stubCallback).toHaveBeenCalled();
        });
    });

    describe("Warning unsaved changes", function() {
        var alertShowStub;
        beforeEach(function() {
            alertShowStub = sinonSandbox.stub(app.alert, "show");
            sinonSandbox.stub(Backbone.history, "getFragment");
            sinonSandbox.stub(app.router, 'navigate');
        });

        afterEach(function() {
            sinonSandbox.restore();
        });

        it("should not alert warning message if unsaved changes are empty", function() {
            app.routing.triggerBefore('route', {});
            expect(alertShowStub).not.toHaveBeenCalledOnce();

            sinonSandbox.stub(view, 'hasUnsavedChanges').returns(false);
            app.routing.triggerBefore('route', {});
            expect(alertShowStub).not.toHaveBeenCalledOnce();
        });

        it("should warn unsaved changes if router is changed with unsaved values", function() {
            sinonSandbox.stub(view, 'hasUnsavedChanges').returns(true);
            app.routing.triggerBefore('route', {});
            expect(alertShowStub).toHaveBeenCalledOnce();
        });

        it("should warn unsaved changes if custom unsaved logic is applied with unsaved values", function() {
            sinonSandbox.stub(view, 'hasUnsavedChanges').returns(true);
            view.triggerBefore('unsavedchange', {callback: _.noop});
            expect(alertShowStub).toHaveBeenCalledOnce();
        });

        it("ALL EDITABLE VIEWS MUST DISPOSE IN JASMINE TEST", function() {
            sinonSandbox.stub(view, 'hasUnsavedChanges').returns(true);
            view.dispose();
            app.routing.triggerBefore('route', {});
            expect(alertShowStub).not.toHaveBeenCalledOnce();
        });
    });

    describe('getEditableFields', function() {
        it('should return a list of editable fields', function() {
            view.render();
            var noEditFields = ['case_number', 'type'];
            view.model.set('locked_fields', ['name', 'description']);

            var editableFields = view.getEditableFields(view.fields, noEditFields);
            expect(editableFields.length).toBe(4);
            expect(editableFields[0].name).toBe('created_by');
            expect(editableFields[1].name).toBe('date_entered');
            expect(editableFields[2].name).toBe('date_modified');
            expect(editableFields[3].name).toBe('modified_user_id');
        });

        it('should return a doubly-linked list', function() {
            view.render();
            var noEditFields = ['name', 'case_number', 'created_by', 'date_modified', 'modified_user_id'];

            var editableFields = view.getEditableFields(view.fields, noEditFields);
            /**
             * linked list in the form of: `description` <=> `type` <=> `date_entered`
             */
            expect(editableFields[0].nextField.name).toBe('type');
            expect(editableFields[0].prevField.name).toBe('date_entered');
            expect(editableFields[1].nextField.name).toBe('date_entered');
            expect(editableFields[1].prevField.name).toBe('description');
            expect(editableFields[2].nextField.name).toBe('description');
            expect(editableFields[2].prevField.name).toBe('type');
        });
    });
});

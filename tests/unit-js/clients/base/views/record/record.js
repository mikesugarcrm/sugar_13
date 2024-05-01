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
describe("Record View", function () {
    var moduleName = 'Cases',
        app,
        viewName = 'record',
        sinonSandbox,
        view,
        createListCollection,
        buildGridsFromPanelsMetadataStub;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('record-decor', 'field', 'base', 'record-decor');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'businesscard');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'field', 'record-decor');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "buttons": [
                {
                    "type": "button",
                    "name": "cancel_button",
                    "label": "LBL_CANCEL_BUTTON_LABEL",
                    "css_class": "btn-invisible btn-link",
                    "showOn": "edit"
                },
                {
                    "type": "actiondropdown",
                    "name": "main_dropdown",
                    "buttons": [
                        {
                            "type": "rowaction",
                            "event": "button:edit_button:click",
                            "name": "edit_button",
                            "label": "LBL_EDIT_BUTTON_LABEL",
                            "primary": true,
                            "showOn": "view",
                            "acl_action":"edit"
                        },
                        {
                            "type": "rowaction",
                            "event": "button:save_button:click",
                            "name": "save_button",
                            "label": "LBL_SAVE_BUTTON_LABEL",
                            "primary": true,
                            "showOn": "edit",
                            "acl_action":"edit"
                        },
                        {
                            "type": "rowaction",
                            "name": "delete_button",
                            "label": "LBL_DELETE_BUTTON_LABEL",
                            "showOn": "view",
                            "acl_action":"delete"
                        },
                        {
                            "type": "rowaction",
                            "name": "duplicate_button",
                            "label": "LBL_DUPLICATE_BUTTON_LABEL",
                            "showOn": "view",
                            'acl_module': moduleName
                        }
                    ]
                }
            ],
            "panels": [
                {
                    "name": "panel_header",
                    "header": true,
                    "fields": [{name: "name", span: 8, labelSpan: 4}],
                    "labels": true
                },
                {
                    "name": "panel_body",
                    "label": "LBL_PANEL_2",
                    "columns": 1,
                    "labels": true,
                    "labelsOnTop": false,
                    "placeholders": true,
                    "fields": [
                        {name: "description", type: "base", label: "description", span: 8, labelSpan: 4},
                        {
                            name: 'case_number',
                            type: 'float',
                            label: 'case_number',
                            span: 8,
                            labelSpan: 4,
                            readonly: true
                        },
                        {name: 'type', type: 'text', label: 'type', span: 8, labelSpan: 4},
                        {
                            name: 'commentlog',
                            type: 'commentlog',
                            label: 'Comment Log',
                            span: 8,
                            labelSpan: 4,
                            fields: [
                                'entry',
                                'date_entered',
                                'created_by_name',
                            ],
                        },
                    ]
                },
                {
                    "name": "panel_hidden",
                    "hide": true,
                    "columns": 1,
                    "labelsOnTop": false,
                    "placeholders": true,
                    "fields": [
                        {name: "created_by", type: "date", label: "created_by", span: 8, labelSpan: 4},
                        {name: "date_entered", type: "date", label: "date_entered", span: 8, labelSpan: 4},
                        {name: "date_modified", type: "date", label: "date_modified", span: 8, labelSpan: 4},
                        {name: "modified_user_id", type: "date", label: "modified_user_id", span: 8, labelSpan: 4}
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.routing.start();
        app.data.declareModels();
        sinonSandbox = sinon.createSandbox();

        sinon.stub(app.metadata, 'getDropdownViews');

        view = SugarTest.createView("base", moduleName, "record", null, null);

        buildGridsFromPanelsMetadataStub = sinon.stub(view, '_buildGridsFromPanelsMetadata').callsFake(
            function(panels) {
                view.hiddenPanelExists = true;

                // The panel grid contains references to the actual fields found in panel.fields, so the fields must
                // be modified to include the field attributes that would be calculated during a normal render
                // operation and then added to the grid in the correct row and column.
                panels[0].grid = [[panels[0].fields[0]]];
                panels[1].grid = [
                    [panels[1].fields[0]],
                    [panels[1].fields[1]],
                    [panels[1].fields[2]]
                ];
                panels[2].grid = [
                    [panels[2].fields[0]],
                    [panels[2].fields[1]],
                    [panels[2].fields[2]],
                    [panels[2].fields[3]]
                ];
            }
        );
    });

    afterEach(function () {
        sinonSandbox.restore();
        sinon.restore();
        app.router.stop();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('initExtraMeta', function() {
        let options;

        beforeEach(function() {
            view.meta = {
                panels: [
                    {
                        name: 'normal_panel_1',
                        fields: [
                            {
                                name: 'normal_field_1'
                            }
                        ]
                    }
                ]
            };

            sinon.stub(app.metadata, 'getView').returns({
                panels: [
                    {
                        name: 'extra_panel_1',
                        fields: [
                            {
                                name: 'extra_field_1'
                            },
                            {
                                name: 'extra_field_2'
                            }
                        ]
                    },
                    {
                        name: 'extra_panel_2',
                        fields: [
                            {
                                name: 'extra_field_3'
                            }
                        ]
                    }
                ]
            });
        });

        it('should add any fields from extra panels to the list of fields to fetch', function() {
            view._initExtraMeta();
            expect(view.context.get('fields')).toEqual(['extra_field_1', 'extra_field_2', 'extra_field_3']);
        });

        it('should add the extra panels to the view metadata', function() {
            view._initExtraMeta();
            expect(view.meta.panels.length).toEqual(3);
            expect(view.meta.panels[0].name).toEqual('normal_panel_1');
            expect(view.meta.panels[1].name).toEqual('extra_panel_1');
            expect(view.meta.panels[2].name).toEqual('extra_panel_2');
        });
    });

    describe('handleAclChange', function() {
        beforeEach(function() {
            this.oldAction = view.action;
            this.oldNoEditFields = view.noEditFields;
        });

        afterEach(function() {
            view.action = this.oldAction;
            view.noEditFields = this.oldNoEditFields;
        });

        it('should set the editable fields and toggle the pencils', function() {
            sinonSandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit', view.model).returns(true);
            sinonSandbox.stub(view, 'setEditableFields').callsFake(function() {
                this.noEditFields = [];
            });
            var pencilStubElement = {'I am a': 'pencil stub'};
            var recordCellJqueryStub = {toggleClass: sinonSandbox.stub()};
            var pencilJqueryStub = {
                closest: sinonSandbox.stub().withArgs('.record-cell').returns(recordCellJqueryStub),
                data: sinonSandbox.stub().withArgs('name').returns('i_now_have_no_acls'),
                toggleClass: sinonSandbox.stub(),
            };
            sinonSandbox.stub(view, '$').withArgs('[data-wrapper=edit]').returns([pencilStubElement]);
            sinonSandbox.stub(window, '$').withArgs(pencilStubElement).returns(pencilJqueryStub);

            view.action = 'edit';
            view.handleAclChange({'i_now_have_no_acls': true});

            expect(view.setEditableFields).toHaveBeenCalled();
            expect(pencilJqueryStub.toggleClass).toHaveBeenCalledWith('hide', false);
            expect(recordCellJqueryStub.toggleClass).toHaveBeenCalledWith('edit', true);
        });
    });

    describe('noEditFields handling', function() {
        it('should be able to put a collection in noEditFields', function() {
            // everything is editable, except commentlog
            var hasAccessToModelStub = sinonSandbox.stub(app.acl, 'hasAccessToModel');
            hasAccessToModelStub
                .withArgs('edit', view.model, 'commentlog')
                .returns(false);
            hasAccessToModelStub
                .withArgs('edit', view.model, sinon.match(function(f) { return f !== 'commentlog'; }))
                .returns(true);

            view.handleAclChange({commentlog: true});

            expect(view.noEditFields).toEqual(['case_number', 'commentlog']);
        });
    });

    describe('Pencil icon handling', function() {
        var fieldName;
        var descriptionField;
        var pencilIcon;

        beforeEach(function() {
            view.render();
            fieldName = 'description';

            descriptionField = view.getField(fieldName);
            pencilIcon = view.$('[data-name=' + fieldName + '].record-edit-link-wrapper');
        });

        it('should properly handle pencil icon on field enabling/disabling if the field is editable', function() {
            expect(pencilIcon.hasClass('hide')).toBe(false);

            descriptionField.setDisabled(true, {trigger: true});

            expect(pencilIcon.hasClass('hide')).toBe(true);

            descriptionField.setDisabled(false, {trigger: true});

            expect(pencilIcon.hasClass('hide')).toBe(false);
        });

        it('should properly handle pencil icon on field enabling/disabling if the field is NOT editable', function() {
            descriptionField.setDisabled(true, {trigger: true});
            expect(pencilIcon.hasClass('hide')).toBe(true);

            sinonSandbox.stub(app.acl, 'hasAccessToModel').callsFake(function(action, model) {
                return false;
            });

            // When the user does not have edit action on the model, the pencil
            // icon should not be toggle on.
            descriptionField.setDisabled(false, {trigger: true});

            expect(pencilIcon.hasClass('hide')).toBe(true);

            app.acl.hasAccessToModel.restore();

            // When the user has edit action on the model, but the field is
            // considered not editable by the view, the pencil icon should not
            // be toggle on.
            view.noEditFields.push(fieldName);

            descriptionField.setDisabled(false, {trigger: true});

            expect(pencilIcon.hasClass('hide')).toBe(true);
        });
    });

    describe('Render', function() {
        it('Should render 7 editable fields and 2 buttons', function() {
            view.render();
            view.model.set({
                name: 'Name',
                description: 'Description'
            });

            expect(_.keys(view.editableFields).length).toBe(7);
            expect(_.keys(view.buttons).length).toBe(2);
        });

        it('Should hide 4 editable fields', function() {
            var hiddenFields = 0;
            view.hidePanel = true; //setting directly instead of using togglePlugin
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            _.each(view.editableFields, function (field) {
                if ((field.$el.closest('.panel_hidden.hide').length === 1)) {
                    hiddenFields++;
                }
            });

            expect(hiddenFields).toBe(4);
        });

        it("Should place name field in the header", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.getField('name').$el.closest('.headerpane').length === 1).toBe(true);
        });

        it("Should not render any fields when a user doesn't have access to the data", function () {
            sinonSandbox.stub(SugarTest.app.acl, 'hasAccessToModel').returns(false);
            sinonSandbox.stub(SugarTest.app.error, 'handleRenderError').callsFake($.noop());

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(_.size(view.fields)).toBe(0);
        });

        it('should hide the record-edit-link-wrapper for permanently readonly fields', function() {
            view.render();
            expect(view.$('.record-edit-link-wrapper[data-name=case_number]').hasClass('hide')).toBeTruthy();
        });

        it('should hide the record-edit-link-wrapper for fields blocked by ACLs', function() {
            sinon.stub(app.acl, 'hasAccessToModel').returns(true);
            app.acl.hasAccessToModel.withArgs('edit', view.model, 'description').returns(false);
            view.render();
            expect(view.$('.record-edit-link-wrapper[data-name=description]').hasClass('hide')).toBeTruthy();
        });

        it("should call clearValidationErrors when Cancel is clicked", function () {
            var clock = sinon.useFakeTimers();
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var stub = sinon.stub(view, "clearValidationErrors");
            view.cancelClicked();
            //Use sinon clock to delay expectations since decoration is deferred
            clock.tick(20);
            expect(stub.calledOnce).toBe(true);
            stub.restore();
            clock.restore();
        });

        it('Should display all 7 editable fields when more link is clicked', function() {
            var hiddenFields = 0,
                visibleFields = 0;

            view.render();
            view.model.set({
                name: 'Name',
                description: 'Description'
            });

            view.$('.more').click();
            _.each(view.editableFields, function (field) {
                if (field.$el.parents('.panel_hidden.hide').length === 1) {
                    hiddenFields++;
                } else {
                    visibleFields++;
                }

            });

            expect(hiddenFields).toBe(0);
            expect(visibleFields).toBe(7);
        });

        it("Should not be editable when this field is in the noEditFields array", function () {
            var noEditFields = ["name", "created_by", "date_entered", "date_modified", "case_number"];

            _.each(view.meta.panels, function (panel) {
                _.each(panel.fields, function (field) {
                    if (_.indexOf(noEditFields, field.name) >= 0) {
                        view.noEditFields.push(field.name);
                    }
                }, this);
            }, this);

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            view.$('.more').click();

            var editableFields = 0;
            _.each(view.editableFields, function (field) {
                if (field.$el.closest(".record-cell").find(".record-edit-link-wrapper").length === 1) {
                    editableFields++;
                }
            });

            expect(editableFields).toBe(3);
            expect(_.size(view.editableFields)).toBe(3);
        });

        it('Should define view `hashSync` settings `true` by default', function() {
            view.render();
            expect(view.meta.hashSync).toBeTruthy();
        });
    });

    describe('Edit', function () {
        it("Should toggle to an edit mode when a user clicks on the inline edit icon", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var field = view.getField('name');
            sinon.stub(field, 'hasChanged').callsFake(function() {
                return false;
            });

            expect(field.options.viewName).toBe(view.action);

            field.$el.closest('.record-cell').find('a.record-edit-link').click();

            expect(field.options.viewName).toBe('edit');
        });

        it("Should toggle all editable fields to edit modes when a user clicks on the edit button", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            _.each(view.editableFields, function (field) {
                expect(field.options.viewName).toBe(view.action);
            });

            view.context.trigger('button:edit_button:click');

            waitsFor(function () {
                return (_.last(view.editableFields)).options.viewName === 'edit';
            }, 'it took too long to wait switching view', 1000);

            runs(function () {
                _.each(view.editableFields, function (field) {
                    expect(field.options.viewName).toBe('edit');
                });
            });
        });

        it('Should ask the model to revert if cancel clicked', function() {
            view.render();
            var revertStub = sinon.stub(view.model, 'revertAttributes');

            view.context.trigger('button:edit_button:click');
            view.model.set({
                name: 'Bar'
            });

            view.context.trigger('button:cancel_button:click');
            expect(revertStub).toHaveBeenCalled();
        });

        describe('Hash synchronisation with record/button state', function() {
            var navigateStub;

            beforeEach(function() {
                navigateStub = sinon.stub(app.router, 'navigate');
                view.model.set('id', 'my-case-id');
            });

            it('Should enter in edit mode if typed url is like /:record/edit', function() {
                let toggleStub = sinon.stub(view, 'toggleEdit');
                let setButtonStatesStub = sinon.stub(view, 'setButtonStates');
                view.action = 'edit';
                view.render();

                expect(toggleStub).toHaveBeenCalledWith(true);
                expect(setButtonStatesStub).toHaveBeenCalledWith(view.STATE.EDIT);
            });

            using('different hashSync settings',
                [true, false],
                function(hashSyncValue) {

                    it('Should handle the url properly if edit button is clicked', function() {
                        view.render();
                        view.meta.hashSync = hashSyncValue;
                        view.context.trigger('button:edit_button:click');

                        if (view.meta.hashSync) {
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id/edit', {trigger: false});
                        } else {
                            expect(navigateStub).not.toHaveBeenCalled();
                        }
                    });

                    it('Should handle the url properly if cancel button is clicked', function() {
                        view.render();
                        view.meta.hashSync = hashSyncValue;
                        view.context.trigger('button:edit_button:click');
                        view.context.trigger('button:cancel_button:click');

                        if (view.meta.hashSync) {
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id/edit', {trigger: false});
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id', {trigger: false});
                        } else {
                            expect(navigateStub).not.toHaveBeenCalled();
                        }
                    });

                    it('Should handle the url properly if save button is clicked and unset context action', function() {

                        // need this to pretend that the model is valid for record view first pass
                        sinonSandbox.stub(view.model, 'doValidate').callsFake(function(fields, cb) {
                            cb(true);
                        });

                        // don't save the model (no need to send to the server)
                        sinonSandbox.stub(view.model, 'save');
                        sinonSandbox.stub(view.model, 'getOption');

                        view.render();
                        view.meta.hashSync = hashSyncValue;
                        view.context.trigger('button:edit_button:click');
                        view.context.trigger('button:save_button:click');

                        if (view.meta.hashSync) {
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id/edit', {trigger: false});
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id', {trigger: false});
                        } else {
                            expect(navigateStub).not.toHaveBeenCalled();
                        }
                        expect(view.context.get('action')).toBeUndefined();
                    });

                });
        });
    });

    describe('Locked Fields', function() {
        var fieldset = {
            name: 'custom_fieldset',
            type: 'fieldset',
            fields: [
                {name: 'fake_field1'},
                {name: 'fake_field2'}
            ]
        };

        beforeEach(function() {
            buildGridsFromPanelsMetadataStub.restore();
            view.meta.panels[1].fields.push(fieldset);
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description',
                fake_field1: 'fake1',
                fake_field2: 'fake2'
            });
        });

        it('should set _hasLockedFields to true when a field is locked', function() {
            view.render();
            view.model.set('locked_fields', ['name']);
            view.handleLockedFields();

            expect(view._hasLockedFields).toBe(true);
        });

        it('should set _hasLockedFields to true when a fieldset field is locked', function() {
            view.render();
            view.model.set('locked_fields', ['fake_field1']);
            view.handleLockedFields();

            expect(view._hasLockedFields).toBe(true);
        });

        it('should only show a lock if the field is locked', function() {
            view.render();
            view.model.set('locked_fields', ['case_number']);
            view.handleLockedFields();

            expect(view.$('.record-lock-link-wrapper[data-name="case_number"]').hasClass('hide')).toBe(false);
            expect(view.$('.record-lock-link-wrapper[data-name="description"]').hasClass('hide')).toBe(true);
        });

        it('should only show a lock if all fields in a fieldset are locked', function() {
            view.render();
            view.model.set('locked_fields', ['fake_field1', 'fake_field2']);
            view.handleLockedFields();

            expect(view.$('.record-lock-link-wrapper[data-name="custom_fieldset"]').hasClass('hide')).toBe(false);
        });

        it('should not show a lock if a field in a fieldset is not locked', function() {
            view.render();
            view.model.set('locked_fields', ['fake_field1']);
            view.handleLockedFields();

            expect(view.$('.record-lock-link-wrapper[data-name="custom_fieldset"]').hasClass('hide')).toBe(true);
        });
    });

    describe("build grids", function() {
        var hasAccessToModelStub;
        var readonlyFields = ['created_by', 'date_entered', 'date_modified'];
        var aclFailFields  = ['case_number'];
        var extraNoEditFields = ['status'];

        beforeEach(function() {
            buildGridsFromPanelsMetadataStub.restore();
            hasAccessToModelStub = sinon.stub(SugarTest.app.acl, 'hasAccessToModel').callsFake(
                function(method, model, field) {
                    return _.indexOf(aclFailFields, field) < 0;
                }
            );
            view.extraNoEditFields = extraNoEditFields;
        });

        afterEach(function() {
            delete view.extraNoEditFields;
            hasAccessToModelStub.restore();
        });

        it("Should convert string fields to objects", function() {
            var meta = {
                panels: [{
                    fields: ["description"]
                }]
            };
            view._buildGridsFromPanelsMetadata(meta.panels);
            expect(meta.panels[0].fields[0].name).toBe("description");
        });

        it('Should add readonly fields, and acl fail fields to the noEditFields array, as well as any extra' +
            'noEdit fields specified in the context', function() {
            var meta = {
                panels: [{
                    fields: [
                        {name: "case_number"},
                        {name: "name"},
                        {name: "description"},
                        {name: "created_by"},
                        {name: "date_entered"},
                        {name: 'date_modified'},
                        {name: 'status'}
                    ]
                }]
            };

            _.each(meta.panels, function (panel) {
                _.each(panel.fields, function (field) {
                    if (_.indexOf(readonlyFields, field.name) >= 0) {
                        field.readonly = true;
                    }
                }, this);
            }, this);

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual   = view.noEditFields,
                expected = _.union(readonlyFields, aclFailFields, extraNoEditFields);

            expect(actual.length).toBe(expected.length);
            _.each(actual, function (noEditField) {
                expect(_.indexOf(expected, noEditField) >= 0).toBeTruthy();
            });
        });

        it("Should add a field to the noEditFields array when a user doesn't have write access on the field", function () {
            var meta = {
                panels: [{
                    fields: [
                        {name: "case_number"},
                        {name: "name"},
                        {name: "description"},
                        {name: "created_by"},
                        {name: "date_entered"},
                        {name: "date_modified"}
                    ]
                }]
            };

            hasAccessToModelStub.restore();
            sinonSandbox.stub(SugarTest.app.user, 'getAcls').callsFake(function() {
                var acls = {};
                acls[moduleName] = {
                    edit: 'yes',
                    fields: {
                        case_number: {
                            create: 'no',
                        },
                        name: {
                            write: 'no',
                        },
                    },
                };
                return acls;
            });

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual   = view.noEditFields,
                expected = aclFailFields;

            expect(actual.length).toBe(expected.length);
            _.each(actual, function (noEditField) {
                expect(_.indexOf(expected, noEditField) >= 0).toBeTruthy();
            });
        });

        it("Should add a fieldset to the noEditFields array when user does not have write access to any of the child fields", function () {
            var fieldset = {
                name: 'fieldset_field',
                type: 'fieldset',
                fields: [{name: 'case_number'}]
            };
            var meta = {
                panels: [{
                    fields: [fieldset]
                }]
            };

            hasAccessToModelStub.restore();
            sinonSandbox.stub(SugarTest.app.user, 'getAcls').callsFake(function() {
                var acls = {};
                acls[moduleName] = {
                    edit: 'yes',
                    fields: {
                        case_number: {
                            create: 'no',
                        },
                    },
                };
                return acls;
            });

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual = view.noEditFields;

            expect(actual.length).toBe(1);
            expect(actual[0]).toEqual(fieldset.name);
        });

        it("Should not add a fieldset to the noEditFields array when user has write access to any child fields", function () {
            var fieldset = {
                name: 'fieldset_field',
                type: 'fieldset',
                fields: [{name: 'case_number'}, {name: 'blah'}]
            };
            var meta = {
                panels: [{
                    fields: [fieldset]
                }]
            };

            hasAccessToModelStub.restore();
            sinonSandbox.stub(SugarTest.app.user, 'getAcls').callsFake(function() {
                var acls = {};
                acls[moduleName] = {
                    edit: 'yes',
                    fields: {
                        case_number: {
                            create: 'no',
                        },
                    },
                };
                return acls;
            });

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual = view.noEditFields;

            expect(_.isEmpty(actual)).toBe(true);
        });

        using('different placements', [
                {
                    placement: 'field_on_top',
                    returnValue: true,
                },
                {
                    placement: 'field_on_side',
                    returnValue: false,
                },
            ],
            function(params) {
                it('Should set the labelsOnTop attribute', function() {
                    var meta = {
                        panels: [{
                            fields: ['description']
                        }]
                    };
                    app.user.setPreference('field_name_placement', params.placement);
                    view._buildGridsFromPanelsMetadata(meta.panels);
                    expect(meta.panels[0].labelsOnTop).toEqual(params.returnValue);
                });
            }
        );
    });

    describe('Switching to next and previous record', function () {

        beforeEach(function () {
            createListCollection = function (nbModels, offsetSelectedModel) {
                view.context.set('listCollection', new app.data.createBeanCollection(moduleName));
                view.collection = app.data.createBeanCollection(moduleName);

                var modelIds = [];
                for (var i = 0; i <= nbModels; i++) {
                    var model = new Backbone.Model(),
                        id = i + '__' + Math.random().toString(36).substr(2, 16);

                    model.set({id: id});
                    if (i === offsetSelectedModel) {
                        view.model.set(model.toJSON());
                        view.collection.add(model);
                    }
                    view.context.get('listCollection').add(model);
                    modelIds.push(id);
                }
                return modelIds;
            };
        });

        it("Should find previous and next model from list collection", function () {
            var modelIds = createListCollection(5, 3);
            view.showPreviousNextBtnGroup();
            expect(view.showPrevious).toBeTruthy();
            expect(view.showNext).toBeTruthy();
        });

        it("Should find previous model from list collection", function () {
            var modelIds = createListCollection(5, 5);
            view.showPreviousNextBtnGroup();
            expect(view.showPrevious).toBeTruthy();
            expect(view.showNext).toBeFalsy();
        });

        it("Should find next model from list collection", function () {
            var modelIds = createListCollection(5, 0);
            view.showPreviousNextBtnGroup();
            expect(view.showPrevious).toBeFalsy();
            expect(view.showNext).toBeTruthy();
        });
    });

    describe('duplicateClicked', function () {
        var triggerStub, openStub, closeStub, expectedModel = {id: 'abcd12345'};

        beforeEach(function() {
            closeStub = sinon.stub();
            triggerStub = sinon.stub(Backbone.Model.prototype, 'trigger').callsFake(function(event, model) {
                if (event === "duplicate:before") {
                    expect(model.get("name")).toEqual(view.model.get("name"));
                    expect(model.get("description")).toEqual(view.model.get("description"));
                    expect(model).toNotBe(view.model);
                }
            });
            SugarTest.app.drawer = {
                open: function () {
                },
                close: function () {
                }
            };
            openStub = sinon.stub(SugarTest.app.drawer, 'open').callsFake(function(opts, closeCallback) {
                expect(opts.context.model).toBeDefined();
                expect(opts.layout).toEqual("create");
                expect(opts.context.model.get("name")).toEqual(view.model.get("name"));
                expect(opts.context.model.get("description")).toEqual(view.model.get("description"));
                expect(opts.context.model).toNotBe(view.model);
                if (closeCallback) {
                    closeStub(expectedModel);
                }
            });
        });

        it('should trigger \'duplicate:before\' on model prior to opening create drawer', function() {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.resetHistory();

            view.duplicateClicked();
            expect(triggerStub.called).toBe(true);
            expect(triggerStub.calledWith('duplicate:before')).toBe(true);
            expect(openStub.called).toBe(true);
            expect(triggerStub.calledBefore(openStub)).toBe(true);
        });

        it('should pass model to mutate with \'duplicate:before\' event', function() {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.resetHistory();

            view.duplicateClicked();
            expect(triggerStub.called).toBe(true);
            expect(triggerStub.calledWith('duplicate:before')).toBe(true);
            //Further expectations in stub
        });

        it('should fire \'drawer:create:fire\' event with copied model set on context', function() {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.resetHistory();
            view.duplicateClicked();
            expect(openStub.called).toBe(true);
            expect(openStub.lastCall.args[0].context.model.get("name")).toEqual(view.model.get("name"));
        });

        it('should call close callback', function() {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description',
                module: "Bugs"
            });
            triggerStub.resetHistory();
            view.duplicateClicked();
            expect(closeStub.lastCall.args[0].id).toEqual(expectedModel.id);
        });
    });

    describe('copying nested collections', function() {
        var collection;

        beforeEach(function() {
            SugarTest.loadPlugin('VirtualCollection');
            app.data.declareModelClass(moduleName, app.metadata.getModule(moduleName), app.config.platform, {
                plugins: ['VirtualCollection']
            });

            collection = app.data.createBeanCollection(view.model.module, [
                app.data.createBean(view.model.module, {id: 1, name: 'aaa', status: 'aaa'}),
                app.data.createBean(view.model.module, {id: 2, name: 'bbb', status: 'bbb'}),
                app.data.createBean(view.model.module, {id: 3, name: 'ccc', status: 'aaa'})
            ]);
            collection.fieldName = 'name';
            collection.fetchAll = function(options) {
                options.success(this, options);
            };

            view.model.set(collection.fieldName, collection);
            view.model.trigger = $.noop;

            sinonSandbox.stub(view, 'getField').returns({
                def: {
                    fields: ['name', 'status']
                }
            });
        });

        afterEach(function() {
            app.data.declareModels();
        });

        it('should not call `fetchAll` when `getCollectionFieldNames` does not exist', function() {
            sinonSandbox.spy(collection, 'fetchAll');

            view.duplicateClicked();

            expect(collection.fetchAll).not.toHaveBeenCalled();
        });

        it('should copy nested collections', function() {
            var target = new app.data.createBean(view.model.module, {});
            target.set(collection.fieldName, new Backbone.Collection());

            view.model.getCollectionFieldNames = function() {
                return [collection.fieldName];
            };

            view._copyNestedCollections(view.model, target);

            expect(target.get(collection.fieldName).length).toBe(collection.length);
        });
    });

    describe('Field labels', function () {
        it("should be hidden on view for headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=name]').closest('.record-cell').hasClass('edit')).toBe(false);
        });

        it("should be shown on view for non-headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=description]').css('display')).not.toBe('none');
        });

        it("should be shown on edit for headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var field = view.getField('name');
            sinon.stub(field, 'hasChanged').callsFake(function() {
                return false;
            });

            field.$el.closest('.record-cell').find('a.record-edit-link').click();

            expect(view.$('.record-label[data-name=name]').closest('.record-cell').hasClass('edit')).toBe(true);
        });
    });

    describe('Set Button States', function () {
        it('should show buttons where the showOn states match', function() {
            // we need our buttons to be initialized before we can test them
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            }, {
                silent:true
            });

            // load up with our spies to detect nefarious activity
            _.each(view.buttons,function(button) {
                sinonSandbox.spy(button,'hide');
                sinonSandbox.spy(button,'show');
            });

            view.setButtonStates(view.STATE.EDIT);

            // with access, assume the show/hide are based solely on showOn
            _.each(view.buttons,function(button) {
                var shouldHide = !!button.def.showOn && (button.def.showOn !== view.STATE.EDIT);
                expect(button.hide.called).toEqual(shouldHide);
                expect(button.show.called).toEqual(!shouldHide);
            });
        });

    });

    describe('hasUnsavedChanges', function() {
        it('should NOT warn unsaved changes when synced values are matched with current model value', function() {
            var attrs = {
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            };
            view.model.setSyncedAttributes(attrs);
            view.model.set(attrs);
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
        });
        it('should warn unsaved changes among the synced attributes', function() {
            view.model.setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);
        });
        it('should warn unsaved changes ONLY IF the changes are editable fields', function() {
            view.model.setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description',
                non_editable: 'system value'
            });
            //un-editable field
            view.model.set({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            view.render();
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
            //Changed non-editable field
            view.model.set({
                non_editable: 'user value'
            });
            actual = view.hasUnsavedChanges();
            var editableFields = _.pluck(view.editableFields, 'name');
            expect(_.contains(editableFields, 'non_editable')).toBe(false);
            expect(actual).toBe(false);
            //Changed editable field
            view.model.set({
                description: 'Changed description'
            });
            actual = view.hasUnsavedChanges();
            expect(_.contains(editableFields, 'description')).toBe(true);
            expect(actual).toBe(true);
        });
        it('should warn unsaved changes when values inside a fieldset changes', function() {
            view.meta.panels[0].fields.push({
                name: 'foo',
                fields: [{
                    name: 'bar'
                }]
            });
            view.model.setSyncedAttributes({
                bar: 'test1'
            });
            view.model.set({
                bar: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(true);
        });
        it('should warn unsaved changes when field that contains other fields changes', function() {
            view.meta.panels[0].fields.push({
                name: 'foo',
                fields: [{
                    name: 'bar'
                }]
            });
            view.model.setSyncedAttributes({
                foo: 'test1'
            });
            view.model.set({
                foo: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(true);
        });
        it('should not warn unsaved changes when the value changed is marked as non-editable.', function() {
            view.noEditFields = ['case_number'];
            view.model.setSyncedAttributes({
                case_number: 'test1'
            });
            view.model.set({
                case_number: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(false);
        });
        it('should not warn unsaved changes when the value changed is a read-only field.', function() {
            view.meta.panels[1].fields[1].readonly = true;
            view.model.setSyncedAttributes({
                case_number: 'test1'
            });
            view.model.set({
                case_number: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(false);
        });
    });

    describe('_getCellToEllipsify', function () {
        it('should return fullname cell if it is the first cell', function() {
            var actual,
                fullname = $('<div></div>').data('type', 'fullname'),
                text = $('<div></div>').data('type', 'text');

            actual = view._getCellToEllipsify($([fullname, text]));

            expect(actual.data('type')).toBe('fullname');
        });

        it('should return hint-accounts-search-dropdown cell if it is the first cell', function() {
            let hintDropdown = $('<div></div>').data('type', 'hint-accounts-search-dropdown');
            let text = $('<div></div>').data('type', 'text');
            let actual = view._getCellToEllipsify($([hintDropdown, text]));

            expect(actual.data('type')).toBe('hint-accounts-search-dropdown');
        });

        it('should return name cell if the first cell cannot be ellipsified', function() {
            var actual,
                html = $('<div></div>').data('type', 'html'),
                name = $('<div></div>').data('type', 'name');

            actual = view._getCellToEllipsify($([html, name]));

            expect(actual.data('type')).toBe('name');
        });
    });

    it('should not return my_favorite field when calling getFieldNames', function () {
        var fields = view.getFieldNames(null, true);
        expect(_.indexOf(fields, 'my_favorite')).toEqual(-1);
    });

    it('should return my_favorite field when calling getFieldNames', function () {
        view.meta.panels[0].fields.push({name: 'favorite', type: 'favorite'});
        var fields = view.getFieldNames(null, true);
        expect(_.indexOf(fields, 'my_favorite')).toBeGreaterThan(-1);
    });

    it('should set a data view on the context', function () {
        expect(view.context.get("dataView")).toBe("record");
    });


    describe("Warning delete", function() {
        var sinonSandbox, alertShowStub, routerStub;
        beforeEach(function() {
            sinonSandbox = sinon.createSandbox();
            routerStub = sinonSandbox.stub(app.router, "navigate");
            sinonSandbox.stub(Backbone.history, "getFragment");
            alertShowStub = sinonSandbox.stub(app.alert, "show");
        });

        afterEach(function() {
            sinonSandbox.restore();
        });

        it("should not alert warning message if _modelToDelete is not defined", function() {
            app.routing.triggerBefore('route', {});
            expect(alertShowStub).not.toHaveBeenCalled();
        });
        it("should return true if _modelToDelete is not defined", function() {
            sinonSandbox.stub(view, 'warnDelete');
            expect(view.beforeRouteDelete()).toBeTruthy();
        });
        it("should return false if _modelToDelete is defined (to prevent routing to other views)", function() {
            sinonSandbox.stub(view, 'warnDelete');
            view._modelToDelete = new Backbone.Model();
            expect(view.beforeRouteDelete()).toBeFalsy();
        });
        it("should redirect the user to the targetUrl", function() {
            var unbindSpy = sinonSandbox.spy(view, 'unbindBeforeRouteDelete');
            view._modelToDelete = new Backbone.Model();
            view._currentUrl = 'Accounts';
            view._targetUrl = 'Contacts';
            view.deleteModel();
            expect(unbindSpy).toHaveBeenCalled();
            expect(routerStub).toHaveBeenCalled();
        });
    });

    describe("Check the First Panel", function() {
        var tempMeta;
        beforeEach(function() {
            tempMeta = view.meta;
            view.meta.panels = [];
        });
        afterEach(function() {
            view.meta = tempMeta;
        });

        it('should return true when calling checkFirstPanel with header', function() {
            view.meta.panels.push({header: 1});
            view.meta.panels.push({newTab: 1});

            expect(view.checkFirstPanel()).toBeTruthy();
        });

        it('should return true when calling checkFirstPanel with no header', function() {
            view.meta.panels.push({header: 0, newTab: 1});

            expect(view.checkFirstPanel()).toBeTruthy();
        });

        it('should return false when calling checkFirstPanel with header', function() {
            view.meta.panels.push({header: 1, newTab: 1});
            view.meta.panels.push({newTab: 0});

            expect(view.checkFirstPanel()).toBeFalsy();
        });

        it('should return false when calling checkFirstPanel with no header', function() {
            view.meta.panels.push({header: 0, newTab: 0});

            expect(view.checkFirstPanel()).toBeFalsy();
        });
    });

    describe('handle Field Errors', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField('base', 'myField', 'base', 'edit');
        });

        afterEach(function() {
            field.dispose();
        });

        it('should expand the `show more` panel if there is an error with a field in that panel', function() {
            var triggerStub = sinon.stub();
            sinon.stub(view, '$')
                .withArgs('.more[data-moreless]').returns({'trigger' : triggerStub});
            sinon.stub(field.$el, 'is')
                .withArgs(':hidden').returns(true);

            view.handleFieldError(field, true);

            expect(triggerStub).toHaveBeenCalledWith('click');
            expect(app.user.lastState.get(view.SHOW_MORE_KEY)).not.toEqual(view.$('.more[data-moreless]'));
        });
    });

    describe('saveClicked', function() {
        var doValidateStub;

        beforeEach(function() {
            doValidateStub = sinon.stub(view.model, 'doValidate');
            sinon.stub(app.acl, 'hasAccessToModel')
                .withArgs('edit', view.model, 'last_name').returns(true);
            sinon.stub(view, 'getFields')
                .withArgs('Cases', view.model).returns({last_name: {}});
        });

        it('should validate all fields if none of them were erased', function() {
            view.model.set('_erased_fields', []);
            view.saveClicked();
            expect(doValidateStub).toHaveBeenCalledWith({last_name: {}}, jasmine.any(Function));
        });

        it('should validate Non-Empty erased fields', function() {
            view.model.set('_erased_fields', ['last_name']);
            view.model.set('last_name', 'dummy_last_name');
            view.saveClicked();
            expect(doValidateStub).toHaveBeenCalledWith({last_name: {}}, jasmine.any(Function));
        });

        it('should not validate Empty erased fields', function() {
            view.model.set('_erased_fields', ['last_name']);
            view.saveClicked();
            expect(doValidateStub).toHaveBeenCalledWith({}, jasmine.any(Function));
        });
    });

    describe('temporaryFileFieldChecks', function() {
        it('should be able to tell if a field is a temporary file field', function() {
            expect(view.isTemporaryFileType('created_by')).toBe(false);
            expect(view.isTemporaryFileType('profile_picture_guid')).toBe(true);
        });

        it('should remove temporary file fields from a model', function() {
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description',
                profile_picture: '1aebd1d1-5380-4ebd-9b21-bb94ca71a544',
                profile_picture_guid: '1aebd1d1-5380-4ebd-9b21-bb94ca71a544'
            });

            expect(view.model.get('profile_picture_guid')).toEqual('1aebd1d1-5380-4ebd-9b21-bb94ca71a544');
            view.resetTemporaryFileFields();
            expect(view.model.get('profile_picture_guid')).toBe(undefined);
        });
    });

    describe('_getMouseTargetFields', function() {
        it('should return a list of fields', function() {
            var event = {target: 'target'};
            var target = {
                parents: function() {
                    return {
                        find: function() {
                            return 'fields';
                        }
                    };
                }
            };
            sinon.stub(view, '$').returns(target);
            expect(view._getMouseTargetFields(event)).toEqual('fields');
        });
    });

    describe('handleMouseMove', function() {
        var tooltipStub;

        beforeEach(function() {
            sinon.stub(view, '_getMouseTargetFields').returns([{
                getBoundingClientRect: function() {
                    return {
                        left: 10,
                        top: 20,
                        width: 10,
                        height: 10
                    };
                }
            }]);
            tooltipStub = sinon.stub();
            sinon.stub(window, '$').returns({
                tooltip: tooltipStub
            });
        });

        it('should show tooltip', function() {
            sinon.stub(view, '_isTooltipOn').returns(false);
            var event = {clientX: 15, clientY: 25};
            view.handleMouseMove(event);
            expect(tooltipStub).toHaveBeenCalledWith('show');
        });

        it('should not do anything', function() {
            sinon.stub(view, '_isTooltipOn').returns(true);
            var event = {clientX: 15, clientY: 25};
            view.handleMouseMove(event);
            expect(tooltipStub).not.toHaveBeenCalled();
        });

        it('should hide tooltip', function() {
            sinon.stub(view, '_isTooltipOn').returns(true);
            var event = {clientX: 25, clientY: 35};
            view.handleMouseMove(event);
            expect(tooltipStub).toHaveBeenCalledWith('hide');
        });

        it('should not do anything', function() {
            sinon.stub(view, '_isTooltipOn').returns(false);
            var event = {clientX: 25, clientY: 35};
            view.handleMouseMove(event);
            expect(tooltipStub).not.toHaveBeenCalled();
        });
    });

    describe('handleMouseLeave', function() {
        var tooltipStub;

        beforeEach(function() {
            sinon.stub(view, '_getMouseTargetFields').returns([{}]);
            tooltipStub = sinon.stub();
            sinon.stub(window, '$').returns({
                tooltip: tooltipStub
            });
        });

        it('should hide tooltip', function() {
            sinon.stub(view, '_isTooltipOn').returns(true);
            view.handleMouseLeave({});
            expect(tooltipStub).toHaveBeenCalledWith('hide');
        });

        it('should not do anything', function() {
            sinon.stub(view, '_isTooltipOn').returns(false);
            view.handleMouseLeave({});
            expect(tooltipStub).not.toHaveBeenCalled();
        });
    });

    describe('when a subpanel record preview is saved', function() {
        let preview;

        beforeEach(function() {
            preview = SugarTest.createView('base', moduleName, 'preview', null, null);
        });

        afterEach(function() {
            preview.dispose();
            preview = null;
        });

        it('should reload the record view data', function() {
            const callBackStub = sinon.stub(view.context, 'reloadData');

            preview.saveCallback(true);
            expect(callBackStub).toHaveBeenCalled();
        });
    });

    describe('_isLinkedToActiveContact', function() {
        let oldOmniConsole;

        beforeEach(function() {
            let viewModelId = view.model.get('id');
            let detailComponent = {
                getModel: function(contact, module) {
                    if (module === 'Contacts') {
                        return app.data.createBean('Contacts', {
                            id: viewModelId
                        });
                    }
                    return {
                        get: function() {
                            return false;
                        }
                    };
                }
            };
            oldOmniConsole = app.omniConsole;
            app.omniConsole = {
                getComponent: function() {
                    return detailComponent;
                }
            };

            view.sugarLiveContactModel = app.data.createBean('Contacts', {
                parent_type: 'RevenueLineItems',
                parent_id: 'my-rli-id'
            });

            view.module = 'Accounts';
        });

        afterEach(function() {
            app.omniConsole = oldOmniConsole;
        });

        it('should be marked as linked if it is a linked contact or lead', function() {
            expect(view._isLinkedToActiveContact()).toEqual(true);
        });

        it('should not be marked as linked if it is not a linked contact or lead', function() {
            view.model = app.data.createBean('Contacts', {
                id: 'my-test-id'
            });
            expect(view._isLinkedToActiveContact()).toEqual(false);
        });

        it('should be marked as linked if it is a parent record', function() {
            view.module = 'RevenueLineItems';
            view.model = app.data.createBean('RevenueLineItems', {
                id: 'my-rli-id'
            });
            expect(view._isLinkedToActiveContact()).toEqual(true);
        });

        it('should not be marked as linked if it is a not parent record', function() {
            view.module = 'Opportunities';
            view.model = app.data.createBean('Opportunities', {
                id: 'my-opp-id'
            });
            expect(view._isLinkedToActiveContact()).toEqual(false);
        });
    });

    describe('createSugarLiveLinkButton', function() {
        beforeEach(function() {
            view.sugarLiveLinkButton = null;
        });

        it('should create the button is the module is valid', function() {
            let insertStub = sinon.stub(view, '_insertSugarLiveButton');
            let handleStateSub = sinon.stub(view, 'handleSugarLiveLinkButtonState');

            view.module = 'Accounts';
            view.createSugarLiveLinkButton();

            expect(view.sugarLiveLinkButton).toNotEqual(null);
            expect(insertStub).toHaveBeenCalledWith(view.sugarLiveLinkButton);
            expect(handleStateSub).toHaveBeenCalledWith(false);
        });
    });

    describe('_isValidLinkableModule', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'getAppListKeys').withArgs('parent_type_display').returns(
                ['Accounts', 'Leads', 'Contacts', 'Opportunities']
            );
            sinon.stub(app.metadata, 'getModule').returns({
                'parent_name': {
                    'options': 'parent_type_display'
                }
            });
            view.sugarLiveContact = {
                getType: () => 'voice'
            };
        });

        it('should return false if the current module is not valid', function() {
            view.module = 'Meetings';
            expect(view._isValidLinkableModule()).toEqual(false);
        });

        it('should return true if the current module is valid', function() {
            view.module = 'Accounts';
            expect(view._isValidLinkableModule()).toEqual(true);
        });
    });
});

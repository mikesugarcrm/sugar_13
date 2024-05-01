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
describe('Base.View.Create', function() {
    var app,
        moduleName = 'Contacts',
        viewName = 'create',
        sinonSandbox, view, context,
        drawer;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'businesscard');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('actiondropdown', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('actiondropdown', 'field', 'base', 'dropdown');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels":[
                {
                    "name":"panel_header",
                    "columns": 2,
                    "labelsOnTop": true,
                    "placeholders":true,
                    "header":true,
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME",
                            "span": 6,
                            "labelSpan": 6
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME",
                            "span": 6,
                            "labelSpan": 6
                        }
                    ]
                }, {
                    "name":"panel_body",
                    "columns": 2,
                    "labelsOnTop":true,
                    "placeholders":true,
                    "fields":[
                        {
                            name: "phone_work",
                            type: "phone",
                            label: "phone_work",
                            span: 6,
                            labelSpan: 6
                        },
                        {
                            name: "email1",
                            type: "email",
                            label: "email1",
                            span: 6,
                            labelSpan: 6
                        },
                        {
                            name: "full_name",
                            type: "name",
                            label: "full_name",
                            span: 6,
                            labelSpan: 6
                        }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "template":"record",
            "buttons": [
                {
                    "name":"cancel_button",
                    "type":"button",
                    "label":"LBL_CANCEL_BUTTON_LABEL",
                    "css_class":"btn-invisible btn-link",
                    events: {
                        click: 'button:cancel_button:click'
                    }
                }, {
                    "name":"restore_button",
                    "type":"button",
                    "label":"LBL_RESTORE",
                    "css_class":"hide btn-invisible btn-link",
                    "showOn" : "select",
                    events: {
                        click: 'button:restore_button:click'
                    }
                }, {
                    "name":"save_button",
                    "type":"button",
                    "label":"LBL_SAVE_BUTTON_LABEL",
                    "primary":true,
                    "showOn":"create",
                    events: {
                        click: 'button:save_button:click'
                    }
                }, {
                    "name":"duplicate_button",
                    "type":"button",
                    "label":"LBL_IGNORE_DUPLICATE_AND_SAVE",
                    "primary":true,
                    "showOn":"duplicate",
                    events: {
                        click: 'button:save_button:click'
                    }
                }, {
                    "name":"select_button",
                    "type":"button",
                    "label":"LBL_SAVE_BUTTON_LABEL",
                    "primary":true,
                    "showOn":"select",
                    events: {
                        click: 'button:save_button:click'
                    }
                }, {
                    'type': 'actiondropdown',
                    'name': 'dropdown_button',
                    'primary': true,
                    'switch_on_click': true,
                    'showOn': 'select',
                    'buttons': [{
                        'type': 'rowaction',
                        'name': 'save_button',
                        'label': 'LBL_SAVE_BUTTON_LABEL',
                        events: {
                            click: 'button:save_button:click'
                        }
                    }]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        app.data.declareModels();

        sinonSandbox = sinon.createSandbox();

        drawer = app.drawer;
        app.drawer = {
            close: $.noop,
            reset: $.noop,
            count: $.noop,
            off: $.noop
        };

        context = app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView("base", moduleName, viewName, null, context);
        view.enableDuplicateCheck = true;
        sinonSandbox.stub(view, 'addToLayoutComponents');
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinonSandbox.restore();
        sinon.restore();
        app.drawer = drawer;
    });

    describe('Initialize', function() {
        var current_user_id = '1234567890';
        var current_user_name = 'Johnny Appleseed';
        var save_user_id;
        var save_user_name;
        var fields;  // Must be Set by each Test
        beforeEach(function() {
            save_user_id = app.user.id;
            save_user_name = app.user.attributes.full_name;

            app.user.set('id', current_user_id);
            app.user.attributes.full_name = current_user_name;

            sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
                var meta = {
                    "fields": fields,
                    favoritesEnabled: true,
                    views: [],
                    layouts: [],
                    _hash: "bc6fc50d9d0d3064f5d522d9e15968fa"
                };
                return meta;
            });
        });

        afterEach(function() {
            app.user.set('id', save_user_id);
            app.user.attributes.full_name = save_user_name;
        });

        it("Should create a record view having a Assigned-To field initialized with the Current Signed In User", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(current_user_id);
            expect(full_name).toEqual(current_user_name);

            expect(view.model.relatedAttributes.assigned_user_id).toBe(user_id);
            expect(view.model.relatedAttributes.assigned_user_name).toBe(full_name);
        });

        it("Should create a record view having a Assigned-To field initialized with the Assigned-to user of the original record if performing a copy.", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var copied_user_id = '98765',
                copied_user_name = 'John Doe',
                bean;
            var context = app.context.getContext();

            bean = app.data.createBean(moduleName, {
                "assigned_user_id" : copied_user_id,
                "assigned_user_name": copied_user_name
            });
            context.set({
                module: moduleName,
                isDuplicate: true,
                model: bean,
                create: true
            });
            context.prepare();

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(copied_user_id);
            expect(full_name).toEqual(copied_user_name);

            expect(view.model.relatedAttributes.assigned_user_id).toBe(current_user_id);
            expect(view.model.relatedAttributes.assigned_user_name).toBe(current_user_name);
        });
        it("Should create a record view having a Assigned-To field - 'id_name' is assigned_user_id", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "some_field",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(current_user_id);
            expect(full_name).toEqual(current_user_name);
        });

        it("Should create a record view having a Assigned-To field - 'name' is assigned_user_id", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "some_field",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(current_user_id);
            expect(full_name).toEqual(current_user_name);
        });


        it("Should Not create a record view having an initialized Assigned-To field - Type is not Relate", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "link",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).not.toEqual(current_user_id);
            expect(full_name).not.toEqual(current_user_name);
        });

        it("Should Not create a record view having an initialized Assigned-To field - neither id_name and name equal 'assigned_user_id' ", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "some_field",
                    "module": "Users",
                    "name": "some_field",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).not.toEqual(current_user_id);
            expect(full_name).not.toEqual(current_user_name);
        });

    });

    describe('Render', function() {
        it("Should render 5 sets of buttons and 5 fields", function() {
            sinonSandbox.stub(view, '_buildGridsFromPanelsMetadata').callsFake(function(panels) {
                // The panel grid contains references to the actual fields found in panel.fields, so the fields must
                // be modified to include the field attributes that would be calculated during a normal render
                // operation and then added to the grid in the correct row and column.
                panels[0].grid = [
                    [panels[0].fields[0], panels[0].fields[1]]
                ];
                panels[1].grid = [
                    [panels[1].fields[0], panels[1].fields[1]],
                    [panels[1].fields[2]]
                ];
            });
            var fields = 0;

            view.render();

            _.each(view.fields, function(field) {
                if (!view.buttons[field.name] && field.type !== 'button' && field.type !== 'rowaction') {
                    fields++;
                }
            });

            expect(fields).toBe(5);
            expect(_.values(view.buttons).length).toBe(6);
        });
    });

    describe('Buttons', function() {
        it("Should only show the save and cancel buttons when the form is empty", function() {
            view.render();
            _.each(view.buttons, function(button) {
                sinonSandbox.stub(button, 'show').callsFake(function() {
                    this.isHidden = false;
                });
                sinonSandbox.stub(button, 'hide').callsFake(function() {
                    this.isHidden = true;
                });
            });

            view.setButtonStates(view.STATE.CREATE);

            expect(view.buttons[view.cancelButtonName].isHidden).toBeFalsy();
            expect(view.buttons['save_button'].isHidden).toBeFalsy();
            expect(view.buttons['duplicate_button'].isHidden).toBeTruthy();
            expect(view.buttons['select_button'].isHidden).toBeTruthy();
            expect(view.buttons['dropdown_button'].isHidden).toBeTruthy();
            expect(view.buttons[view.restoreButtonName].isHidden).toBeTruthy();
        });

        it("Should show restore button, along with save and cancel, when a duplicate is selected to edit.", function() {
            var data = {
                "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                "first_name":"Foo",
                "last_name":"Bar",
                "phone_work":"1234567890",
                "email1":"foobar@test.com",
                "full_name":"Mr Foo Bar"
            };

            view.render();
            _.each(view.buttons, function(button) {
                sinonSandbox.stub(button, 'show').callsFake(function() {
                    this.isHidden = false;
                });
                sinonSandbox.stub(button, 'hide').callsFake(function() {
                    this.isHidden = true;
                });
            });
            view.setButtonStates(view.STATE.SELECT);
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });

            expect(view.buttons[view.cancelButtonName].isHidden).toBeFalsy();
            expect(view.buttons['save_button'].isHidden).toBeTruthy();
            expect(view.buttons['duplicate_button'].isHidden).toBeTruthy();
            expect(view.buttons['select_button'].isHidden).toBeFalsy();
            expect(view.buttons['dropdown_button'].isHidden).toBeFalsy();
            expect(view.buttons[view.restoreButtonName].isHidden).toBeFalsy();
        });

        it("Should set the model to selected duplicate values plus any create data for empty fields on selected duplicate", function() {
            var title = "CEO",
                selectedDuplicateAttributes = {
                    "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                    "first_name":"Foo",
                    "last_name":"Bar",
                    "phone_work":"1234567890",
                    "email1":"foobar@test.com",
                    "full_name":"Mr Foo Bar",
                    "age":42,
                    "is_cool":false
                },
                expectedAttributes = _.extend({title:title}, selectedDuplicateAttributes);

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last',
                title: title
            });
            view.context.trigger('list:dupecheck-list-select-edit:fire', app.data.createBean(moduleName, selectedDuplicateAttributes));
            expect(view.model.attributes).toEqual(expectedAttributes);
        });

        it("Should reset to the original form values when restore is clicked.", function() {
            var data = {
                "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                "first_name":"Foo",
                "last_name":"Bar",
                "phone_work":"1234567890",
                "email1":"foobar@test.com",
                "full_name":"Mr Foo Bar"
            };

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });
            view.context.trigger('list:dupecheck-list-select-edit:fire', app.data.createBean(moduleName, data));

            expect(view.model.get('first_name')).toBe('Foo');
            expect(view.model.get('last_name')).toBe('Bar');

            var render = sinonSandbox.stub(view, 'render');
            view.buttons[view.restoreButtonName].getFieldElement().click();

            expect(view.model.get('first_name')).toBe('First');
            expect(view.model.get('last_name')).toBe('Last');
            expect(view.model.isCopy()).toBeTruthy();
        });
    });

    describe('SaveModel', function() {
        it("Should retrieve custom save options and params options should be appended to request url", function() {
            var moduleName = "Contacts",
                bean,
                dm = app.data,
                ajaxSpy = sinon.spy($, 'ajax');

            bean = dm.createBean(moduleName, { id: "1234" });
            var getCustomSaveOptionsStub = sinonSandbox.stub(view, 'getCustomSaveOptions').callsFake(function() {
                return {'params': {'param1': true, 'param2': false}};
            });

            view.render();
            view.model = bean;

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", /.*rest\/v10\/Contacts\/1234.*/,
                [200, { "Content-Type": "application/json"}, JSON.stringify({})]);

            var success = function(){};
            var failure = function(){};

            view.saveModel(success, failure);

            SugarTest.server.respond();
            expect(getCustomSaveOptionsStub.calledOnce).toBeTruthy();
            getCustomSaveOptionsStub.restore();

            expect(ajaxSpy.getCall(0).args[0].url).toContain('?param1=true&param2=false&erased_fields=true&viewed=1');
            ajaxSpy.restore();
        });

        it("Should not append options to url if custom options method not overridden", function() {
            var moduleName = "Contacts",
                bean,
                dm = app.data,
                ajaxSpy = sinonSandbox.spy($, 'ajax');

            bean = dm.createBean(moduleName, { id: "1234" });

            var getCustomSaveOptionsStub = sinon.stub(view, 'getCustomSaveOptions');

            view.render();
            view.model = bean;

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", /.*rest\/v10\/Contacts\/1234.*/,
                [200, { "Content-Type": "application/json"}, JSON.stringify({})]);

            var success = function(){};
            var failure = function(){};

            view.saveModel(success, failure);

            SugarTest.server.respond();
            expect(getCustomSaveOptionsStub.calledOnce).toBeTruthy();
            getCustomSaveOptionsStub.restore();

            expect(ajaxSpy.getCall(0).args[0].url).toContain('?erased_fields=true&viewed=1');
        });

        it("Should append after_create url parameters if the model is copied and and the copied model ID is set", function() {
            var saveSpy = sinon.stub(view.model, 'save'),
                getCustomSaveOptionsStub = sinon.stub(view, 'getCustomSaveOptions');

            view.context.set('copiedFromModelId', '123');
            view.model.isCopied = true;
            view.saveModel(function(){}, function(){});

            expect(saveSpy.calledOnce).toBe(true);
            expect(saveSpy.args[0][1].params.after_create.copy_rel_from).toBe('123');

            saveSpy.restore();
            getCustomSaveOptionsStub.restore();
        });

        it("Should not append after_create url parameters if the model is not copied", function() {
            var saveSpy = sinon.stub(view.model, 'save'),
                getCustomSaveOptionsStub = sinon.stub(view, 'getCustomSaveOptions');

            view.context.set('copiedFromModelId', '123');
            view.saveModel(function(){}, function(){});

            expect(saveSpy.calledOnce).toBe(true);
            expect(saveSpy.args[0][1].params).toBeUndefined();

            saveSpy.restore();
            getCustomSaveOptionsStub.restore();
        });

        it("Should not append after_create url parameters if the copied model ID is not set", function() {
            var saveSpy = sinon.stub(view.model, 'save'),
                getCustomSaveOptionsStub = sinon.stub(view, 'getCustomSaveOptions');

            view.model.isCopied = true;
            view.saveModel(function(){}, function(){});

            expect(saveSpy.calledOnce).toBe(true);
            expect(saveSpy.args[0][1].params).toBeUndefined();

            saveSpy.restore();
            getCustomSaveOptionsStub.restore();
        });

        it("Should build correct success message if model is returned from the API", function() {
            var moduleName = 'Contacts',
                labelSpy = sinonSandbox.spy(app.lang, 'get'),
                messageContext;

            var model = app.data.createBean(moduleName, {
                attributes: {
                    id: '123',
                    name: 'foo',
                }
            });

            view.buildSuccessMessage(model);
            expect(labelSpy.calledWith('LBL_RECORD_SAVED_SUCCESS', moduleName)).toBeTruthy();
            messageContext = labelSpy.lastCall.args[2];
            expect(messageContext.id).toEqual(model.attributes.id);
            expect(messageContext.module).toEqual(moduleName);
            expect(messageContext.moduleSingularLower).toEqual('contact');
            expect(messageContext.name).toEqual(model.attributes.name);
        });

        it("Should build generic message if model is not returned from the API", function() {
            var moduleName = 'Contacts',
                labelSpy = sinonSandbox.spy(app.lang, 'get');

            view.buildSuccessMessage();
            expect(labelSpy.calledWith('LBL_RECORD_SAVED', moduleName)).toBeTruthy();
        });
    });

    describe('restoreModel()', function() {
        it('should trigger resetCollection on any child create subpanels', function() {
            var triggerSpy = sinonSandbox.spy(view.context, 'trigger'),
                child = new Backbone.Model({
                    isCreateSubpanel: true,
                    link: 'testLink'
                });
            view.context.children.push(child);
            view.hasSubpanelModels = true;

            view.restoreModel();

            expect(triggerSpy).toHaveBeenCalledWith('subpanel:resetCollection:testLink');
        });
    });

    //FIXME: SC-4136 fix this to be compatible with changes introduced in TDD-73
    xdescribe('Save', function() {
        beforeEach(function() {
            SugarTest.clock.restore();
        });

        it("Should save data when save button is clicked, form data are valid, and no duplicates are found.", function() {
            let flag = false;
            let validateStub = sinonSandbox.stub(view, 'validateModelWaterfall').callsFake(function(callback) {
                callback(null);
            });
            let validateSubpanelsStub = sinonSandbox.stub(view, 'validateSubpanelModelsWaterfall').callsFake(
                function(callback) {
                    callback(null);
                }
            );
            let checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate').callsFake(function(success) {
                success(app.data.createBeanCollection(moduleName));
            });
            let saveModelStub = sinonSandbox.stub(view, 'saveModel').callsFake(function() {
                view.model.set('id', 123);
                flag = true;
            });

            view.render();

            runs(function() {
                var saveButton = view.buttons.save_button;
                saveButton.getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'Save should have been called but timeout expired', 1000);

            runs(function() {
                expect(validateSubpanelsStub.calledOnce).toBeTruthy();
                expect(validateStub.calledOnce).toBeTruthy();
                expect(checkForDuplicateStub.calledOnce).toBeTruthy();
                expect(saveModelStub.calledOnce).toBeTruthy();
            });
        });

        describe('once save is complete', function() {
            var flag, modelId, drawerCloseStub, alertStub;
            beforeEach(function() {
                flag = false,
                    sinonSandbox.stub(view, 'validateModelWaterfall').callsFake(function(callback) {
                        callback(null);
                    });
                sinonSandbox.stub(view, 'checkForDuplicate').callsFake(function(success, error) {
                    success(app.data.createBeanCollection(moduleName));
                });
                sinonSandbox.stub(view, 'saveModel').callsFake(function(success) {
                    view.model.set('id', modelId);
                    success();
                });
                drawerCloseStub = sinonSandbox.stub(app.drawer, 'close').callsFake(function() {
                    flag = true;
                    return;
                });
                alertStub = sinonSandbox.stub(view.alerts, 'showSuccessButDeniedAccess');

                view.render();
            });
            it("Should close drawer", function() {
                modelId = 123;

                runs(function() {
                    var saveButton = view.buttons.save_button;
                    saveButton.getFieldElement().click();
                });

                waitsFor(function() {
                    return flag;
                }, 'close should have been called but timeout expired', 1000);

                runs(function() {
                    expect(drawerCloseStub.calledOnce).toBeTruthy();
                    expect(alertStub.called).toBeFalsy();
                });
            });
        });

        it("Should not save data when save button is clicked but subpanel models data are invalid", function() {
            let flag = false;
            let validateStub = sinonSandbox.stub(view, 'validateModelWaterfall').callsFake(function(callback) {
                flag = true;
                callback(true);
            });
            let validateSubpanelsStub = sinonSandbox.stub(view, 'validateSubpanelModelsWaterfall').callsFake(
                function(callback) {
                    flag = true;
                    callback(true);
                }
            );
            let checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate').callsFake(function(success) {
                flag = true;
                success(app.data.createBeanCollection(moduleName));
            });
            let saveModelStub = sinonSandbox.stub(view, 'saveModel');

            view.render();

            runs(function() {
                var saveButton = view.buttons.save_button;
                saveButton.getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'validateModelWaterfall should have been called but timeout expired', 1000);

            runs(function() {
                expect(validateSubpanelsStub.calledOnce).toBeTruthy();
                expect(validateStub.calledOnce).toBeFalsy();
                expect(checkForDuplicateStub.called).toBeFalsy();
                expect(saveModelStub.called).toBeFalsy();
            });
        });

        it("Should not save data when save button is clicked but form data are invalid", function() {
            let flag = false;
            let validateStub = sinonSandbox.stub(view, 'validateModelWaterfall').callsFake(function(callback) {
                flag = true;
                callback(true);
            });
            let checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate').callsFake(function(success) {
                flag = true;
                success(app.data.createBeanCollection(moduleName));
            });
            let saveModelStub = sinonSandbox.stub(view, 'saveModel');

            view.render();

            runs(function() {
                var saveButton = view.buttons.save_button;
                saveButton.getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'validateModelWaterfall should have been called but timeout expired', 1000);

            runs(function() {
                expect(validateStub.calledOnce).toBeTruthy();
                expect(checkForDuplicateStub.called).toBeFalsy();
                expect(saveModelStub.called).toBeFalsy();
            });
        });

        it('should save when the `save_button` click event is triggered', function() {
            var stub = sinonSandbox.stub(view, 'initiateSave');
            view.context.trigger('button:save_button:click');
            expect(stub).toHaveBeenCalled();
        });

        it("Should not save data when save button is clicked but duplicates are found", function() {
            let flag = false;
            let validateStub = sinonSandbox.stub(view, 'validateModelWaterfall').callsFake(function(callback) {
                callback(null);
            });
            let checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate').callsFake(function(success) {
                flag = true;
                let data = {
                    id: 'f360b873-b11c-4f25-0a3e-50cb8e7ad0c2',
                    first_name: 'Foo',
                    last_name: 'Bar',
                    phone_work: '1234567890',
                    email1: 'foobar@test.com',
                    full_name: 'Mr Foo Bar'
                };
                let model = app.data.createBean(moduleName, data);
                let collection = app.data.createBeanCollection(moduleName, model);

                success(collection);
            });
            let saveModelStub = sinonSandbox.stub(view, 'saveModel');

            view.render();

            runs(function() {
                var saveButton = view.buttons.save_button;
                saveButton.getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'checkForDuplicate should have been called but timeout expired', 2000);

            runs(function() {
                expect(validateStub.calledOnce).toBeTruthy();
                expect(checkForDuplicateStub.calledOnce).toBeTruthy();
                expect(saveModelStub.called).toBeFalsy();
            });
        });
    });

    //FIXME: SC-4136 fix this to be compatible with changes introduced in TDD-73
    xdescribe('Disable Duplicate Check', function() {
        it("Should save data and not run duplicate check when duplicate check is disabled", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall').callsFake(function(callback) {
                    callback(null);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate'),
                saveModelStub = sinonSandbox.stub(view, 'saveModel').callsFake(function(success) {
                    success();
                }),
                drawerCloseStub = sinonSandbox.stub(app.drawer, 'close').callsFake(function() {
                    flag = true;
                });

            view.enableDuplicateCheck = false;
            view.render();

            runs(function() {
                var saveButton = view.buttons.save_button;
                saveButton.getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'Drawer should have been closed but timeout expired', 1000);

            runs(function() {
                expect(validateStub.calledOnce).toBeTruthy();
                expect(checkForDuplicateStub.called).toBeFalsy();
                expect(saveModelStub.calledOnce).toBeTruthy();
                expect(drawerCloseStub.calledOnce).toBeTruthy();
            });
        });
    });

    describe('skipDupeCheck', function() {
        it('should skip duplicate check when duplicates are already displayed', function() {
            view.setButtonStates(view.STATE.DUPLICATE);
            expect(view.skipDupeCheck()).toEqual(true);
        });

        it('should skip duplicate check when selecting a record for edit', function() {
            view.setButtonStates(view.STATE.SELECT);
            expect(view.skipDupeCheck()).toEqual(true);
        });

        it('should not skip duplicate check when creating a record', function() {
            view.setButtonStates(view.STATE.CREATE);
            expect(view.skipDupeCheck()).toEqual(false);
        });
    });

    describe('renderDupeCheckList', function() {
        it('should set dupelisttype to dupecheck-list-edit', function() {
            view.renderDupeCheckList();
            expect(view.context.get('dupelisttype')).toEqual('dupecheck-list-edit');
        });

        it('should render dupecheck layout only if dupecheckList not already defined', function() {
            var createLayoutSpy = sinonSandbox.spy(app.view, 'createLayout');
            view.renderDupeCheckList();
            expect(createLayoutSpy).toHaveBeenCalledOnce();
            view.renderDupeCheckList();
            expect(createLayoutSpy).not.toHaveBeenCalledTwice();
        });
    });

    describe('hasUnsavedChanges', function() {

        it('should return true if attribute on model which has no default has changed', function() {
            expect(view.model.get('foo')).toBeUndefined();
            expect(view.hasUnsavedChanges()).toBeFalsy();

            view.model.set('foo', true);
            expect(view.hasUnsavedChanges()).toBeTruthy();

            view.model.set('foo', false);
            expect(view.hasUnsavedChanges()).toBeTruthy();

            view.model.set('foo', false);
            expect(view.hasUnsavedChanges()).toBeTruthy();

            view.model.set('foo', true);
            expect(view.hasUnsavedChanges()).toBeTruthy();

            view.model.setDefault('foo', true);
            expect(view.hasUnsavedChanges()).toBeFalsy();
        });

        it('should return true if attribute on model which has a default has changed', function() {
            view.model.set('foo', 'a');
            view.model.setDefault('foo', 'a');
            expect(view.hasUnsavedChanges()).toBeFalsy();

            view.model.set('foo', 'b');
            expect(view.hasUnsavedChanges()).toBeTruthy();

            view.model.set('foo', 'c');
            expect(view.hasUnsavedChanges()).toBeTruthy();
        });

        it('should return true if getDefault returns undefined', function() {
            sinonSandbox.stub(view.model, 'getDefault').returns(undefined);
            view.model.set('foo', 'a');
            expect(view.hasUnsavedChanges()).toBeTruthy();
        });

        it('should return false if "resavingAfterMetadataSync" is true', function() {
            view.model.set('foo', true);
            expect(view.hasUnsavedChanges()).toBeTruthy();

            view.resavingAfterMetadataSync = true;
            expect(view.hasUnsavedChanges()).toBeFalsy();
        });

        it('should return false if model is not new', function() {
            view.model.set('foo', true);
            expect(view.hasUnsavedChanges()).toBeTruthy();

            view.model.set('id', 'not_undefined');
            expect(view.hasUnsavedChanges()).toBeFalsy();
        });
    });

    describe('SugarLogic integration', function() {

        it('should set the default attributes once plugin is initialized', function() {
            var setDefaultStub = sinonSandbox.stub(view.model, 'setDefault');
            view.trigger('sugarlogic:initialize');
            expect(setDefaultStub).toHaveBeenCalled();
        });
    });

    describe('populateFromOmnichanel', function() {
        var oldOmniConsole;
        var consoleIsOpen;

        beforeEach(function() {
            oldOmniConsole = app.omniConsole;
            consoleIsOpen = true;
            app.omniConsole = {
                getModelPrepopulateData: sinon.stub().returns({
                    primary_contact_id: '12345',
                    primary_contact_name: 'Conner Tact'
                }),
                isOpen: function() {
                    return consoleIsOpen;
                }
            };

            sinon.stub(view.model, 'set');
        });

        afterEach(function() {
            app.omniConsole = oldOmniConsole;
        });

        it('should set the Omnichannel pre-populate values if omniPopulation is true', function() {
            view.omniPopulation = true;
            view.populateFromOmnichanel();
            expect(view.model.set).toHaveBeenCalledWith({
                primary_contact_id: '12345',
                primary_contact_name: 'Conner Tact'
            });
        });

        it('should do nothing if omniPopulation is false', function() {
            view.omniPopulation = false;
            view.populateFromOmnichanel();
            expect(view.model.set).not.toHaveBeenCalled();
        });

        it('should do nothing if the Omnichannel console is not open', function() {
            consoleIsOpen = false;
            view.populateFromOmnichanel();
            expect(view.model.set).not.toHaveBeenCalled();
        });
    });
});

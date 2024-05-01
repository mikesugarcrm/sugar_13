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
describe('Products.Base.Fields.QuoteDataEditablelistbutton', function() {
    var app;
    var field;
    var fieldDef;
    var fieldType = 'quote-data-editablelistbutton';
    var fieldModule = 'Products';

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: fieldType,
            label: 'testLbl',
            css_class: '',
            buttons: ['button1'],
            no_default_action: true
        };

        field = SugarTest.createField('base', fieldType, fieldType, 'detail',
            fieldDef, fieldModule, null, null, true);

        sinon.stub(field, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('_render()', function() {
        var addClassStub;
        var removeClassStub;

        beforeEach(function() {
            addClassStub = sinon.stub();
            removeClassStub = sinon.stub();
            sinon.stub(field, '$el').value({
                closest: function() {
                    return {
                        addClass: addClassStub,
                        removeClass: removeClassStub
                    };
                },
                find: function() {}
            });
        });

        it('should add class higher if tplName is edit', function() {
            field.tplName = 'edit';
            field._render();
            expect(addClassStub).toHaveBeenCalled();
        });

        it('should remove class higher if tplName is not edit', function() {
            field.tplName = 'not-edit';
            field._render();
            expect(removeClassStub).toHaveBeenCalled();
        });

        it('should set this.changed to true if this.changed is undefined and model is not saved', function() {
            field.changed = undefined;
            field._render();

            expect(field.changed).toBeTruthy();
        });

        it('should not set this.changed if this.changed is already set', function() {
            field.changed = false;
            field._render();

            expect(field.changed).toBeFalsy();
        });

        it('should not set this.changed if model is already saved', function() {
            sinon.stub(field.model, 'isNew').callsFake(function() { return false; });
            field.changed = undefined;
            field._render();

            expect(field.changed).toBeUndefined();
        });
    });

    describe('_loadTemplate()', function() {
        var fieldTemplate;
        beforeEach(function() {
            fieldTemplate = function fieldTemplate() {};
            sinon.stub(app.template, 'getField').callsFake(function() {
                return fieldTemplate;
            });
        });

        it('should set template to empty when in edit mode', function() {
            field.view.action = 'list';
            field.action = 'edit';
            field._loadTemplate();
            expect(field.template).toBe(fieldTemplate);
        });

        it('should set template to empty if detail', function() {
            field.view.action = 'list';
            field.action = 'detail';
            field._loadTemplate();
            expect(field.template).toBe(app.template.empty);
        });
    });

    describe('cancelEdit()', function() {
        var lastCall;
        var lastCallCtxTrigger;
        beforeEach(function() {
            field.model.module = 'TestModule';
            field.model.id = 'testId';

            sinon.stub(field, 'setDisabled').callsFake(function() {
                return false;
            });

            sinon.stub(field.model, 'revertAttributes').callsFake(function() {});
            field.view.clearValidationErrors = function() {};
            field.view.toggleRow = function() {};
            field.view.model = new Backbone.Model({
                id: 'viewId1'
            });
            sinon.stub(field.view, 'toggleRow').callsFake(function() {});
            field.view.layout = {
                trigger: $.noop,
                off: sinon.stub()
            };
            sinon.stub(field.view.layout, 'trigger').callsFake(function() {});
            field.view.name = 'fieldViewName';
            field.cancelEdit();
            lastCall = field.view.toggleRow.lastCall;
            lastCallCtxTrigger = field.view.layout.trigger.lastCall;
        });

        afterEach(function() {
            lastCall = null;
        });

        it('should call toggleRow with three params', function() {
            expect(lastCall.args.length).toBe(3);
        });

        it('should call toggleRow with first param module name', function() {
            expect(lastCall.args[0]).toBe('TestModule');
        });

        it('should call toggleRow with second param model id', function() {
            expect(lastCall.args[1]).toBe(field.model.cid);
        });

        it('should call toggleRow with third param false', function() {
            expect(lastCall.args[2]).toBeFalsy();
        });

        it('should trigger editablelist:viewName:cancel on view context', function() {
            expect(lastCallCtxTrigger.args[0]).toBe('editablelist:' + field.view.name + ':cancel');
        });
    });

    describe('_save()', function() {
        beforeEach(function() {
            field.view.layout = {
                trigger: $.noop,
                off: sinon.stub()
            };
            sinon.stub(field.view.layout, 'trigger').callsFake(function() {});
            field.view.context.parent = {
                trigger: $.noop
            };
            field.view.model = app.data.createBean('ProductBundles');
            sinon.stub(field.view.context.parent, 'trigger').callsFake(function() {});
            sinon.stub(field, '_saveRowModel').callsFake(function() {});
        });

        it('should trigger editablelist:viewName:saving on the view.layout', function() {
            var evtName = 'editablelist:' + field.view.name + ':saving';
            field._save();

            expect(field.view.layout.trigger).toHaveBeenCalledWith(evtName, true, field.model.cid);
        });

        it('should trigger default group save if default group is not saved', function() {
            sinon.stub(field.view.model, 'isNew').callsFake(function() {
                return true;
            });
            field._save();

            expect(field.view.context.parent.trigger).toHaveBeenCalled();
            expect(field._saveRowModel).not.toHaveBeenCalled();
        });

        it('should call _saveRowModel if default group is already saved', function() {
            sinon.stub(field.view.model, 'isNew').callsFake(function() {
                return false;
            });
            field._save();

            expect(field.view.context.parent.trigger).not.toHaveBeenCalled();
            expect(field._saveRowModel).toHaveBeenCalled();
        });
    });

    describe('_validationComplete()', function() {
        beforeEach(function() {
            sinon.stub(field, '_save').callsFake(function() {});
            sinon.stub(field, 'setDisabled').callsFake(function() {});
            sinon.stub(field, 'cancelEdit').callsFake(function() {});
        });

        it('should trigger cancelEdit only if both this.changed and this.model.changed are empty', function() {
            field.changed = undefined;
            field.model.changed = undefined;
            field._validationComplete(true);

            expect(field.cancelEdit).toHaveBeenCalled();
        });

        it('should not trigger cancelEdit if this.changed is not empty', function() {
            field.changed = {
                name: 'new'
            };
            field.model.changed = undefined;
            field._validationComplete(true);

            expect(field.cancelEdit).not.toHaveBeenCalled();
        });

        it('should not trigger cancelEdit if this.model.changed is not empty', function() {
            field.changed = undefined;
            field.model.changed = {
                name: 'new'
            };
            field._validationComplete(true);

            expect(field.cancelEdit).not.toHaveBeenCalled();
        });
    });
});

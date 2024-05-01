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
describe('ProductBundles.Base.Fields.QuoteDataEditablelistbutton', function() {
    var app;
    var field;
    var fieldDef;
    var fieldType = 'quote-data-editablelistbutton';
    var fieldModule = 'ProductBundles';

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

        sinon.stub(field, '_super');
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

        describe('inline-save field', function() {
            beforeEach(function() {
                field.name = 'inline-save';
            });

            it('should set changed = true if current model differs from synced', function() {
                field.model.set('name', 'test1');
                sinon.stub(field.model, 'getSynced').callsFake(function() {
                    return '';
                });
                field._render();

                expect(field.changed).toBeTruthy();
            });

            it('should not set changed if current model is same as synced', function() {
                field.model.set('name', 'test1');
                sinon.stub(field.model, 'getSynced').callsFake(function() {
                    return 'test1';
                });
                field._render();

                expect(field.changed).toBeUndefined();
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
        });

        describe('when not create or new header row', function() {
            beforeEach(function() {
                field.cancelEdit();
                lastCall = field.view.toggleRow.lastCall;
                lastCallCtxTrigger = field.view.layout.trigger.lastCall;
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

            it('should trigger editablelist:viewName:cancel on view layout', function() {
                expect(lastCallCtxTrigger.args[0]).toBe('editablelist:' + field.view.name + ':cancel');
            });
        });
    });

    describe('cancelClicked()', function() {
        var pbItems;
        var pbItem;
        beforeEach(function() {
            pbItems = new Backbone.Collection();
            pbItem = new Backbone.Model({
                id: 'pbModel1'
            });
            field.model.set('product_bundle_items', pbItems);
            field.view.layout = {
                trigger: $.noop,
                off: sinon.stub()
            };
            sinon.stub(field, 'cancelEdit').callsFake(function() {});
            sinon.stub(field.view.layout, 'trigger').callsFake(function() {});
            field.view.name = 'fieldViewName';
        });

        it('should trigger editablelist:fieldViewName:create:cancel when in create view', function() {
            field.view.isCreateView = true;
            field.cancelClicked();

            expect(field.view.layout.trigger).toHaveBeenCalledWith('editablelist:fieldViewName:create:cancel');
        });

        it('should trigger editablelist:fieldViewName:create:cancel when group is empty', function() {
            field.view.isCreateView = false;
            field.model.setSyncedAttributes({
                _justSaved: true
            });
            field.cancelClicked();

            expect(field.view.layout.trigger).toHaveBeenCalledWith('editablelist:fieldViewName:create:cancel');
        });

        it('should call cancelEdit when group is not empty and not in create', function() {
            field.view.isCreateView = false;
            field.model.setSyncedAttributes({
                _justSaved: true
            });
            pbItems.add(pbItem);
            field.cancelClicked();

            expect(field.cancelEdit).toHaveBeenCalled();
        });
    });

    describe('_save()', function() {
        beforeEach(function() {
            field.view.layout = {
                trigger: $.noop,
                off: sinon.stub()
            };
            sinon.stub(field.view.layout, 'trigger').callsFake(function() {});

            field.view.model = app.data.createBean('ProductBundles');
            sinon.stub(field, '_saveRowModel').callsFake(function() {});
        });

        it('should trigger editablelist:viewName:saving on the view.layout', function() {
            field._save();
            expect(field.view.layout.trigger).toHaveBeenCalled();
        });

        it('should call _saveRowModel', function() {
            field._save();
            expect(field._saveRowModel).toHaveBeenCalled();
        });
    });
});

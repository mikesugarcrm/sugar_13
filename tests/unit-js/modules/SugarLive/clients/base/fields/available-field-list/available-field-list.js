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
describe('SugarLive.Fields.AvailableFieldList', function() {
    var app;
    var field;
    var model;
    var fieldName;
    var module = 'SugarLive';

    beforeEach(function() {
        app = SUGAR.App;
        model = app.data.createBean(module);
        model.fieldModule = 'Calls';
        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField('base', fieldName, 'available-field-list', 'edit', {}, module, model, null, true);

        sinon.stub(app.metadata, 'getModule').returns([{
            name: 'id', type: 'id', label: 'LBL_ID',
        }, {
            name: 'name', type: 'text', vname: 'LBL_NAME'
        }, {
            name: 'description', type: 'textarea', label: 'LBL_DESC'
        }, {
            name: 'direction', type: 'text', label: 'LBL_DIR'
        }]);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
    });

    describe('reset to defaults', function() {
        it('should', function() {
            var fieldStub = sinon.stub(field, 'setAvailableFields');
            var renderStub = sinon.stub(field, 'render');
            field.resetToDefaults();
            expect(fieldStub).toHaveBeenCalled();
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('render', function() {
        it('should handle drag and drop on render', function() {
            var handleStub = sinon.stub(field, 'handleDragAndDrop');
            field._render();
            expect(handleStub).toHaveBeenCalled();
        });
    });

    describe('field availability', function() {
        var meta;
        beforeEach(function() {
            meta = {
                fields: [{
                    name: 'name',
                    label: 'LBL_SUBJECT',
                    type: 'name'
                }, {
                    name: 'description',
                    label: 'LBL_DESC',
                    type: 'text'
                }]
            };

            var langStub = sinon.stub(app.lang, 'get');
            langStub.withArgs('LBL_NAME').returns('Subject');
            langStub.withArgs('LBL_DESC').returns('Description');
            langStub.withArgs('LBL_DIR').returns('Direction');
        });

        it('should update the available fields list', function() {
            sinon.stub(app.metadata, 'getView').returns(meta);
            var expectedAvailableFields = [{
                name: 'direction', label: 'LBL_DIR', displayName: 'Direction'
            }];
            field.setAvailableFields();
            expect(field.availableFields).toEqual(expectedAvailableFields);
        });

        it('should retrieve all possible fields', function() {
            sinon.stub(app.metadata, 'getView').returns(meta);

            var expectedFields = [{
                name: 'description',
                label: 'LBL_DESC',
                displayName: 'Description'
            }, {
                name: 'direction',
                label: 'LBL_DIR',
                displayName: 'Direction'
            }, {
                name: 'name',
                label: 'LBL_NAME',
                displayName: 'Subject'
            }];

            var result = field.getAllFields('Calls');
            expect(result).toEqual(expectedFields);
        });
    });

    describe('isFieldSupported', function() {
        var targetField;
        beforeEach(function() {
            targetField = {
                name: 'description',
                type: 'textarea',
                studio: {}
            };
        });

        it('should ignore specific field names', function() {
            targetField.name = 'deleted';
            var result = field.isFieldSupported(targetField);
            expect(result).toBe(false);
        });

        it('should not allow specific field types', function() {
            targetField.type = 'id';
            var result = field.isFieldSupported(targetField);
            expect(result).toBe(false);
        });

        it('should ', function() {
            targetField.type = 'widget';
            var result = field.isFieldSupported(targetField);
            expect(result).toBe(true);
        });
    });

    describe('hasNoStudioSupport', function() {
        var targetField;
        beforeEach(function() {
            targetField = {
                name: 'description',
                type: 'textarea',
                studio: {}
            };
        });

        it('should check the studio field values', function() {
            var result = field.hasNoStudioSupport(targetField);
            expect(result).toBe(false);
        });

        it('should not be supported if it has widget type', function() {
            targetField.type = 'widget';
            var result = field.hasNoStudioSupport(targetField);
            expect(result).toBe(false);
        });

        it('should detect the studio field', function() {
            targetField.studio = false;
            var result = field.hasNoStudioSupport(targetField);
            expect(result).toBe(true);
        });
    });

    describe('handleDragAndDrop', function() {
        it('should call this.$.sortable method', function() {
            sinon.stub(jQuery.fn, 'sortable').callsFake(function() { });
            field.handleDragAndDrop();

            expect(jQuery.fn.sortable).toHaveBeenCalled();
        });

        it('should cancel the drag and drop action', function() {
            var ui = {
                sender: {
                    sortable: function(param) { }
                }
            };
            var sortableStub = sinon.stub(ui.sender, 'sortable');
            field.cancelDrop({}, ui);
            expect(sortableStub).toHaveBeenCalledWith('cancel');
        });
    });
});

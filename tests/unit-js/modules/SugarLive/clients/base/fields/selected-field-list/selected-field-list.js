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
describe('SugarLive.Fields.SelectedFieldList', function() {
    var app;
    var field;
    var model;
    var fieldName;
    var module = 'SugarLive';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        model.fieldModule = 'Calls';
        SugarTest.loadComponent('base', 'field', 'base');
        var meta = {
            fields: [
                {
                    name: 'name',
                    label: 'LBL_SUBJECT',
                    type: 'name'
                },
                {
                    name: 'description',
                    label: 'LBL_DESC',
                    type: 'text'
                }
            ]
        };

        sinon.stub(app.metadata, 'getView').returns(meta);
        sinon.stub(app.metadata, 'getModule').returns({
            name: {name: 'name', type: 'name', vname: 'LBL_NAME'},
            description: {name: 'description', type: 'text', vname: 'LBL_DESC'}
        });

        field = SugarTest.createField('base', fieldName, 'selected-field-list', 'edit', {}, module, model, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
    });

    describe('resetToDefaults', function() {
        it('should reset to defaults', function() {
            var setStub = sinon.stub(field, 'setSelectedFields');
            var renderStub = sinon.stub(field, 'render');
            field.resetToDefaults();
            expect(setStub).toHaveBeenCalled();
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('remove action', function() {
        it('should remove the active pill', function() {
            var ev = {
                target: {
                    remove: function() { }
                }
            };
            var removeStub = sinon.stub(ev.target, 'remove');
            var handleStub = sinon.spy(field, 'handleColumnsChanging');
            var triggerStub = sinon.stub(field.collection, 'trigger');
            field.removePill(ev);
            expect(removeStub).toHaveBeenCalled();
            expect(handleStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('preview');
        });
    });

    describe('render', function() {
        it('should trigger a preview', function() {
            var triggerStub = sinon.stub(field.collection, 'trigger');
            field._render();
            expect(triggerStub).toHaveBeenCalledWith('preview');
        });
    });

    describe('initialize sortable', function() {
        it('should initialize the main sortable', function() {
            sinon.stub(jQuery.fn, 'sortable').callsFake(function() { });
            field.initDragAndDrop();
            expect(jQuery.fn.sortable).toHaveBeenCalled();
        });
    });

    describe('setSelectedFields', function() {
        it('should update the selected fields list', function() {
            var langStub = sinon.stub(app.lang, 'get');
            langStub.withArgs('LBL_NAME').returns('Subject');
            langStub.withArgs('LBL_DESC').returns('Description');
            var expectedSelectedFields = [{
                name: 'name',
                label: 'LBL_NAME',
                displayName: 'Subject'
            }, {
                name: 'description',
                label: 'LBL_DESC',
                displayName: 'Description'
            }];

            field.setSelectedFields();

            expect(field.selectedFields).toEqual(expectedSelectedFields);
        });
    });

    describe('dropping a field', function() {
        var ui = {};
        var appendStub;

        beforeEach(function() {
            ui.sender = $('<ul></ul>');
            ui.item = $('<li></li>');
            appendStub = sinon.stub(ui.item, 'append');
        });

        it('should add a remove icon', function() {
            ui.sender.attr('id', 'fields-sortable');
            field.handleDrop({}, ui);
            expect(appendStub).toHaveBeenCalledWith(field.removeFldIcon);
        });

        it('should not add a remove icon', function() {
            ui.sender.attr('id', 'list');
            field.handleDrop({}, ui);
            expect(appendStub).not.toHaveBeenCalled();
        });
    });
});

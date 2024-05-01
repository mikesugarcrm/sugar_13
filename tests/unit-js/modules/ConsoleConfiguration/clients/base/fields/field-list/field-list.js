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
describe('ConsoleConfiguration.Fields.FieldList', function() {
    var app;
    var field;
    var model;
    var fields;
    var fieldName;
    var multiLineMetadata;
    var module = 'ConsoleConfiguration';
    var domSample = '<ul id="columns-sortable" class="field-list" module_name="Accounts">' +
        '<li class="pill outer multi-field-block">' +
        '<ul class="multi-field-sortable multi-field">' +
        '<li class="list-header" fieldname="name" ' +
        'fieldlabel="LBL_NAME/LBL_INDUSTRY" data-original-title="Name/Industry">' +
        'Name/Industry<i class="multi-field-column-remove"></i></li>' +
        '<li class="pill" fieldname="name" fieldlabel="LBL_NAME" data-original-title="Name">' +
        'Name<i class="console-field-remove"></i></li>' +
        '<li class="pill" fieldname="industry" fieldlabel="LBL_INDUSTRY" data-original-title="Industry">' +
        'Industry<i class="console-field-remove"></i></li>' +
        '</ul></li>' +
        '<li class="pill outer" fieldname="description" fieldlabel="LBL_DESCRIPTION" data-original-title="Desc">' +
        'Description<i class="console-field-remove"></i></li>' +
        '<li class="pill outer" fieldname="account_type" fieldlabel="LBL_TYPE" data-original-title="Type">' +
        'Type<i class="console-field-remove"></i></li></ul>';

    beforeEach(function() {
        app = SugarTest.app;
        fields = [
            {
                name: 'next_renewal_date',
                label: 'LBL_NEXT_RENEWAL_DATE',
                subfields: [
                    {
                        name: 'next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        default: 'true',
                        enabled: 'true',
                        type: 'relative-date',
                        widget_name: 'widget_next_renewal_date',
                    },
                    {
                        name: 'next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        default: 'true',
                        enabled: 'true',
                    },
                ],
            },
        ];
        multiLineMetadata = {
            panels: [{
                fields: fields,
            }],
        };
        model = app.data.createBean(module);
        model.set({
            enabled_module: 'Accounts',
        });

        var moduleFields = {
            renewal: {
                name: 'renewal',
                type: 'bool'
            },
            calls: {
                name: 'calls',
                type: 'link'
            },
            widget_amount: {
                name: 'widget_amount',
                type: 'widget',
                multiline: 'true'
            }
        };
        sinon.stub(app.metadata, 'getModule')
            .withArgs(model.get('enabled_module'), 'fields').returns(moduleFields);

        sinon.stub(app.metadata, 'getView')
            .withArgs(model.get('enabled_module'), 'multi-line-list')
            .returns(multiLineMetadata);

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField('base', fieldName, 'field-list', 'edit', {}, module, model, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
    });

    describe('initialize', function() {
        it('should call getTabContentFields once', function() {
            var getTabContentFieldsSpy = sinon.spy(field, 'getMappedFields');
            field.initialize(field.options);
            expect(getTabContentFieldsSpy.calledOnce).toBe(true);
        });

        it('should initialize the main sortable', function() {
            sinon.stub(jQuery.fn, 'sortable').callsFake(function() {});
            field.initSingleFieldDragAndDrop();
            expect(jQuery.fn.sortable).toHaveBeenCalled();
        });

        it('should initialize the multi line field sortables', function() {
            sinon.stub(jQuery.fn, 'sortable').callsFake(function() {});
            field.initMultiFieldDragAndDrop(jQuery.fn);
            expect(jQuery.fn.sortable).toHaveBeenCalled();
        });
    });

    describe('getViewMetaData', function() {
        it('should call proper function based on defaultViewMeta in context', function() {
            var viewMeta = field.getViewMetaData('Accounts');
            expect(viewMeta).toEqual(multiLineMetadata);

            field.context.set('defaultViewMeta', {Accounts: {}});
            viewMeta = field.getViewMetaData('Accounts');
            expect(viewMeta).toEqual({});
        });
    });

    describe('getMappedFields', function() {
        it('should return field to subfield mapping', function() {
            var expected = {
                next_renewal_date: [
                    {
                        name: 'widget_next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_name: 'next_renewal_date',
                        widget_name: 'widget_next_renewal_date'
                    },
                    {
                        name: 'next_renewal_date',
                        label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_label: 'LBL_NEXT_RENEWAL_DATE',
                        parent_name: 'next_renewal_date',
                    },
                ],
            };

            var actual = field.getMappedFields();

            expect(actual).toEqual(expected);
        });
    });

    describe('getSelectedFieldList', function() {
        it('should call the getSelectedFieldList method', function() {
            var methodStub = sinon.stub(field, 'getSelectedFieldList');
            field.triggerPreviewUpdate();
            expect(methodStub).toHaveBeenCalled();
        });

        it('should call the trigger with an empty list', function() {
            var contextTriggerSpy = sinon.stub(field.context, 'trigger');
            field.triggerPreviewUpdate();
            expect(contextTriggerSpy).toHaveBeenCalledWith('consoleconfig:preview:Accounts', []);
        });

        it('should call the trigger with the correct list of fields', function() {
            var contextTriggerSpy = sinon.stub(field.context, 'trigger');
            var expectedList = JSON.parse('[[{"name":"name","label":"LBL_NAME"},{"name":"industry",' +
                '"label":"LBL_INDUSTRY"}],[{"name":"description","label":"LBL_DESCRIPTION"}],' +
                '[{"name":"account_type","label":"LBL_TYPE"}]]');
            field.$el.append(domSample);
            field.triggerPreviewUpdate();
            expect(contextTriggerSpy).toHaveBeenCalledWith('consoleconfig:preview:Accounts', expectedList);
            field.$el.html('');
        });
    });

    describe('utilitary methods', function() {
        it('can check if a field is defined as multi-line', function() {
            expect(field.isDefinedAsMultiLine('calls')).toBe(false);
            expect(field.isDefinedAsMultiLine('renewal')).toBe(false);
            expect(field.isDefinedAsMultiLine('widget_amount')).toBe(true);
        });
    });

    describe('multi line field updates', function() {
        // updateMultiLineField, getNewHeaderDetails, addMultiFieldHint
        var multiLineField;
        var multiLineFieldHeader;

        beforeEach(function() {
            field.$el.append(domSample);
            multiLineFieldHeader = field.$el.find('.list-header[fieldname="name"]');
            multiLineField = multiLineFieldHeader.parent();
        });

        afterEach(function() {
            field.$el.html('');
        });

        it('updates correctly when it has only a single field', function() {
            multiLineField.find('.pill[fieldname="name"]').remove();
            field.addMultiFieldHint(multiLineField);
            field.updateMultiLineField(multiLineField);
            expect(multiLineFieldHeader.attr('fieldname')).toEqual('industry');
            expect(multiLineFieldHeader.attr('fieldlabel')).toEqual('LBL_INDUSTRY');
            expect(multiLineFieldHeader.attr('data-original-title')).toEqual('Industry');
            expect(multiLineField.find('.multi-field-hint').length).toEqual(0);
        });

        it('updates correctly when it has no fields', function() {
            multiLineField.find('.pill[fieldname="name"]').remove();
            multiLineField.find('.pill[fieldname="industry"]').remove();
            field.addMultiFieldHint(multiLineField);
            field.updateMultiLineField(multiLineField);
            expect(multiLineFieldHeader.attr('fieldname')).toBeFalsy();
            expect(multiLineFieldHeader.attr('fieldlabel')).toEqual('');
            expect(multiLineFieldHeader.attr('data-original-title')).toEqual('LBL_CONSOLE_MULTI_ROW');
            expect(multiLineField.find('.multi-field-hint').length).toEqual(1);
        });

        it('updates correctly when fields have been re-added', function() {
            multiLineField.find('.pill[fieldname="name"]').remove();
            multiLineField.find('.pill[fieldname="industry"]').remove();
            multiLineField.append('<li class="pill" fieldname="industry" fieldlabel="LBL_INDUSTRY" ' +
                'data-original-title="Industry">Industry</li>');
            multiLineField.append('<li class="pill" fieldname="status" fieldlabel="LBL_STATUS" ' +
                'data-original-title="Status">Status</li>');
            field.addMultiFieldHint(multiLineField);
            field.updateMultiLineField(multiLineField);
            expect(multiLineFieldHeader.attr('fieldname')).toEqual('industry/status');
            expect(multiLineFieldHeader.attr('fieldlabel')).toEqual('LBL_INDUSTRY/LBL_STATUS');
            expect(multiLineFieldHeader.attr('data-original-title')).toEqual('Industry/Status');
            expect(multiLineField.find('.multi-field-hint').length).toEqual(0);
        });
    });

    describe('drag & drop into a multi line field - shouldRejectFieldDrop', function() {
        var multiLineField;
        var multiLineFieldHeader;

        beforeEach(function() {
            field.$el.append(domSample);
            multiLineFieldHeader = field.$el.find('.list-header[fieldname="name"]');
            multiLineField = multiLineFieldHeader.parent();
        });

        afterEach(function() {
            field.$el.html('');
        });

        it('should reject the drop because there are already 2 fields in it', function() {
            var ui = {
                item: $('<li class="pill outer" fieldname="status">Status</li>')
            };
            multiLineField.append(ui.item); // simulate the drop
            var multiLineFields = multiLineField.find('.pill');
            expect(field.shouldRejectFieldDrop(ui, multiLineFields)).toEqual(true);
        });

        it('should reject the drop because a multi line field is dropped into another', function() {
            var ui = {
                item: $('<li class="pill outer multi-field-block"></li>')
            };
            multiLineField.append(ui.item); // simulate the drop
            var multiLineFields = multiLineField.find('.pill');
            expect(field.shouldRejectFieldDrop(ui, multiLineFields)).toEqual(true);
        });

        it('should reject the drop because there is a multi line widget inside', function() {
            var ui = {
                item: $('<li class="pill outer" fieldname="status">Status</li>')
            };
            multiLineField.find('.pill[fieldname="name"]').remove();
            multiLineField.find('.pill[fieldname="industry"]').remove();
            multiLineField.append('<li class="pill" fieldname="widget_amount">Amount</li>');
            multiLineField.append(ui.item); // simulate the drop
            var multiLineFields = multiLineField.find('.pill');
            expect(field.shouldRejectFieldDrop(ui, multiLineFields)).toEqual(true);
        });

        it('should reject the drop because there is an item already and a multi line widget is dropped', function() {
            var ui = {
                item: $('<li class="pill" fieldname="widget_amount">Amount</li>')
            };
            multiLineField.find('.pill[fieldname="industry"]').remove();
            multiLineField.append('<li class="pill" fieldname="widget_amount">Amount</li>');
            multiLineField.append(ui.item); // simulate the drop
            var multiLineFields = multiLineField.find('.pill');
            expect(field.shouldRejectFieldDrop(ui, multiLineFields)).toEqual(true);
        });

        it('should not reject the drop if there is a single item and we drop another standard item', function() {
            var ui = {
                item: $('<li class="pill" fieldname="status">Amount</li>')
            };
            multiLineField.find('.pill[fieldname="name"]').remove();
            multiLineField.append(ui.item); // simulate the drop
            var multiLineFields = multiLineField.find('.pill');
            expect(field.shouldRejectFieldDrop(ui, multiLineFields)).toEqual(false);
        });
    });
});

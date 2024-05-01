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
describe('HomeDashboardFilterGroupWidget', function() {
    let widget;
    let context;
    let app;

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();
        let model = new Backbone.Model({
            id: 1,
            metadata: {
                filters: {}
            }
        });
        context.set('model', model);
        context.prepare();

        widget = SugarTest.createView('base', 'Home', 'dashboard-filter-group-widget', null, context, true);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        context = null;
        widget.dispose();
        widget = null;
    });

    describe('Initialization', function() {
        it('should initialize properties correctly', function() {
            const options = {
                groupId: 'testGroupId',
                groupMeta: {
                    fields: ['testField'],
                    fieldType: 'testFieldType',
                    filterDef: 'testFilterDef',
                    label: 'testLabel'
                },
                widgetNo: 1
            };
            const expectedLabel = 'testLabel';
            const expectedFields = ['testField'];
            const expectedType = 'testFieldType';
            const expectedFilterDef = 'testFilterDef';
            const expectedIsEmptyGroup = false;

            widget.options = options;
            widget._initProperties();

            expect(widget._groupId).toEqual('testGroupId');
            expect(widget._groupMeta).toEqual(options.groupMeta);
            expect(widget._widgetNo).toEqual(1);
            expect(widget._groupLabel).toEqual(expectedLabel);
            expect(widget._groupFields).toEqual(expectedFields);
            expect(widget._groupType).toEqual(expectedType);
            expect(widget._filterDef).toEqual(expectedFilterDef);
            expect(widget._isEmptyGroup).toEqual(expectedIsEmptyGroup);
        });
    });

    describe('Groups updated', function() {
        it('should not update if group ID does not match active group ID', function() {
            widget._groupId = 'testGroupId';
            widget._isSelected = false;
            const filterGroups = {};
            const activeFilterGroupId = 'otherGroupId';

            widget.groupsUpdated(filterGroups, activeFilterGroupId);

            expect(widget._isSelected).toBeFalsy();
        });

        it('should not update if group ID is not present in filter groups', function() {
            widget._groupId = 'notTheSameGroupId';
            widget._isSelected = false;
            const filterGroups = {};
            const activeFilterGroupId = 'testGroupId';

            widget.groupsUpdated(filterGroups, activeFilterGroupId);

            expect(widget._isSelected).toBeFalsy();
        });

        it('should update group data if group ID and filter groups match', function() {
            widget._groupId = 'testGroupId';
            widget._isSelected = false;
            widget._widgetNo = 1;

            const filterGroups = {
                testGroupId: {
                    fields: ['updatedField'],
                    fieldType: 'updatedFieldType',
                    filterDef: 'updatedFilterDef',
                    label: 'updatedLabel'
                }
            };
            const activeFilterGroupId = 'testGroupId';
            const expectedFields = ['updatedField'];
            const expectedType = 'updatedFieldType';
            const expectedFilterDef = 'updatedFilterDef';
            const expectedIsEmptyGroup = false;

            widget._createFilterOperatorWidget = function() {
                widget._filterOperatorWidget = {
                    getSummaryText: function() {},
                    dispose: function() {},
                };
            };

            widget.groupsUpdated(filterGroups, activeFilterGroupId);

            expect(widget._isSelected).toBeTruthy();
            expect(widget._groupFields).toEqual(expectedFields);
            expect(widget._groupType).toEqual(expectedType);
            expect(widget._filterDef).toEqual(expectedFilterDef);
            expect(widget._isEmptyGroup).toEqual(expectedIsEmptyGroup);
        });
    });
})

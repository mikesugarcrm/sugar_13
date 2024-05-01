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
describe('Reports.Fields.VisibilityWidget', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            client: 'base',
            name: 'visibility-widget',
            type: 'visibility-widget',
            viewName: 'detail',
            module: 'Reports',
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('initialize', function() {
        it('should set properties appropriately', function() {
            field.initialize({});

            expect(field.SCREENS_MAPPING).toEqual({
                chart: 'firstScreen',
                table: 'secondScreen',
            });
            expect(field._widgetsVisibility).toEqual({
                filters: {
                    onScreen: false,
                    interactable: false,
                },
                table: {
                    onScreen: false,
                    interactable: false,
                },
                chart: {
                    onScreen: false,
                    interactable: false,
                },
            });
        });
    });

    describe('_updateVisibilityState', function() {
        it('should change visibility state', function() {
            expect(field._widgetsVisibility.chart.onScreen).toEqual(false);

            field._updateVisibilityState('chart', {
                onScreen: true,
                interactable: true,
            });

            expect(field._widgetsVisibility.chart.onScreen).toEqual(true);
        });
    });

    describe('changeVisibility', function() {
        it('should change visibility on user action', function() {
            const onScreenState = field._widgetsVisibility.table.onScreen;

            field.changeVisibility({
                currentTarget: {
                    id: 'table',
                },
            });

            expect(field._widgetsVisibility.table.onScreen).toEqual(!onScreenState);
        });
    });

    describe('setVisibilityState', function() {
        it('should change visibility on outside interaction', function() {
            field.setVisibilityState({
                hidden: 'firstScreen',
                filtersActive: false,
            });

            expect(field._widgetsVisibility.table.onScreen).toEqual(true);
            expect(field._widgetsVisibility.filters.onScreen).toEqual(false);
            expect(field._widgetsVisibility.chart.onScreen).toEqual(false);
        });
    });
});

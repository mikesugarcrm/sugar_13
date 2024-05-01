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
describe('Forecasts.View.ConfigWorksheetColumns', function() {
    var app,
        meta,
        options,
        context,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        var cfgModel = new Backbone.Model({
            is_setup: 1,
            show_worksheet_best: true,
            show_worksheet_likely: true,
            show_worksheet_worst: false,
            worksheet_columns: []
        });

        context.set({
            model: cfgModel,
            module: 'Forecasts'
        });

        meta = {
            label: 'testLabel',
            panels: [{
                fields: []
            }]
        };

        options = {
            meta: meta,
            context: context
        };

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('config-panel', 'view', 'base', 'title');
        SugarTest.testMetadata.set();

        // load the parent config-panel view
        SugarTest.loadComponent('base', 'view', 'config-panel');
        view = SugarTest.createView('base', 'Forecasts', 'config-worksheet-columns', meta, context, true);
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('handleColumnModelChange', function() {
        it('should Add to selectedOptions', function() {
            view.handleColumnModelChange({added: {test: 'test'}});
            expect(view.selectedOptions.length).toEqual(1);
        });

        it('should Remove from selectedOptions', function() {
            view.selectedOptions.push({test: 'test'});
            view.handleColumnModelChange({removed: view.selectedOptions[0]});
            expect(view.selectedOptions.length).toEqual(0);
        });
    });

    describe("addOption()", function() {
        var testFieldObj0,
            testFieldObj1,
            testFieldObj2,
            initObjects = [];

        beforeEach(function() {
            testFieldObj0= {
                id: 'test_field0',
                text: 'Test Field0',
                index: 0
            };
            testFieldObj1 = {
                id: 'test_field1',
                text: 'Test Field1',
                index: 1
            };
            testFieldObj2 = {
                id: 'test_field2',
                text: 'Test Field2',
                index: 2
            };
        });

        afterEach(function() {
            testFieldObj0 = null;
            testFieldObj1 = null;
            testFieldObj2 = null;
        });

        describe("option already exists", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj1, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.addOption(testFieldObj1);
            });

            it("should not add already existing option", function() {
                expect(view.allOptions).toContain(testFieldObj1);
                expect(view.selectedOptions).toContain(testFieldObj1);
            });
            it("should not be added again to the array", function() {
                expect(view.allOptions.length).toEqual(3);
                expect(view.selectedOptions.length).toEqual(3);
            });
        });

        describe("option does not exist", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.addOption(testFieldObj1);
            });

            it("should add option since it doesn't exist", function() {
                expect(view.allOptions).toContain(testFieldObj1);
                expect(view.selectedOptions).toContain(testFieldObj1);
            });
            it("should add option at correct array index", function() {
                expect(view.allOptions[testFieldObj1.index]).toEqual(testFieldObj1);
                expect(view.selectedOptions[testFieldObj1.index]).toEqual(testFieldObj1);
            });
        });
    });

    describe("removeOption()", function() {
        var testFieldObj0,
            testFieldObj1,
            testFieldObj2,
            initObjects = [];

        beforeEach(function() {
            testFieldObj0= {
                id: 'test_field0',
                text: 'Test Field0',
                index: 0
            };
            testFieldObj1 = {
                id: 'test_field1',
                text: 'Test Field1',
                index: 1
            };
            testFieldObj2 = {
                id: 'test_field2',
                text: 'Test Field2',
                index: 2
            };
        });

        afterEach(function() {
            testFieldObj0 = null;
            testFieldObj1 = null;
            testFieldObj2 = null;
        });

        describe("option already exists, remove it", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj1, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.removeOption(testFieldObj1);
            });

            it("should remove existing option", function() {
                expect(view.allOptions).not.toContain(testFieldObj1);
                expect(view.selectedOptions).not.toContain(testFieldObj1);
            });
        });

        describe("option does not exist, nothing should happen", function() {
            beforeEach(function() {
                initObjects = [testFieldObj0, testFieldObj2];
                view.allOptions = initObjects;
                view.selectedOptions = initObjects;
                view.removeOption(testFieldObj1);
            });

            it("should still not contain removed object", function() {
                expect(view.allOptions).not.toContain(testFieldObj1);
                expect(view.selectedOptions).not.toContain(testFieldObj1);
            });
            it("should not affect arrays", function() {
                expect(view.allOptions.length).toEqual(2);
                expect(view.selectedOptions.length).toEqual(2);
            });
        });
    });
});

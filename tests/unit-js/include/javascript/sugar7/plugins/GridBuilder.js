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
describe("Plugins.GridBuilder", function() {
    var moduleName = "Cases",
        app,
        viewName   = "record",
        view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, "view", "base");
        SugarTest.loadComponent("base", "field", "base");
        SugarTest.loadComponent("base", "view", viewName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        view = SugarTest.createView("base", moduleName, viewName, null, null);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        view = null;
    });

    describe("render", function() {
        describe("labels are on top", function() {
            it("Should create a one-column panel grid", function() {
                var results,
                    fields    = [
                        // case: field.span is undefined
                        // result: field.span should be 12
                        {
                            name: "foo1"
                        },

                        // case: field.span >= 12
                        // result: field.span should be 12
                        {
                            name: "foo2",
                            span: 20
                        },

                        // case: field.span is 0
                        // result: field.span should be 1
                        {
                            name: "foo3",
                            span: 1
                        },

                        // case: 0 < field.span < 12
                        // result: field.span should remain 6
                        {
                            name: "foo4",
                            span: 6
                        }
                    ];
                app.user.setPreference('field_name_placement', 'field_on_top');
                view.meta.panels = [{
                    columns:     1,
                    labels:      true,
                    fields:      fields
                }];
                view.render();
                results = view.meta.panels[0].grid;

                // each field should be on its own row
                expect(results.length).toBe(fields.length);

                // case: field.span is undefined
                expect(results[0][0].span).toBe(12);

                // case: field.span >= 12
                expect(results[1][0].span).toBe(12);

                // case: field.span is 0
                expect(results[2][0].span).toBe(1);

                // case: 0 < field.span < 12
                expect(results[3][0].span).toBe(6);
            });

            it("Should create a two-column panel grid", function() {
                var results,
                    fields    = [
                        // case: the third field should be on its own row with an empty column
                        // result: the first two fields are one the first row and the third field is on its own row
                        {
                            name: "foo1"
                        },
                        {
                            name: "foo2"
                        },
                        {
                            name: "foo3"
                        },

                        // case: field.span is 8 and previous field's span is 6
                        // result: field overflows the row and ends up on the next row
                        {
                            name: "foo4",
                            span: 8
                        },

                        // case: field.span >= 12
                        // result: field.span should be 12; field overflows the row and its span dictates that the
                        // field be on its own row
                        {
                            name: "foo5",
                            span: 20
                        }
                    ];
                app.user.setPreference('field_name_placement', 'field_on_top');
                view.meta.panels = [{
                    columns:     2,
                    labels:      true,
                    fields:      fields
                }];
                view.render();
                results = view.meta.panels[0].grid;

                // the number of rows found in this two-column grid
                expect(results.length).toBe(4);

                // case: the third field should be on its own row with an empty column
                expect(results[0].length).toBe(2);
                expect(results[1].length).toBe(1);
                expect(results[1][0].span).toBe(6);

                // case: field.span is 8 and previous field's span is 6
                expect(results[2].length).toBe(1);
                expect(results[2][0].span).toBe(8);

                // result: field.span should be 12; field overflows the row and its span dictates that the
                // field be on its own row
                expect(results[3].length).toBe(1);
                expect(results[3][0].span).toBe(12);
            });

            it("Should create a three-column panel grid", function() {
                var results,
                    fields    = [
                        // case: field.span is calculated for three fields such that they fit on one row
                        // result: three fields are on the same row
                        {
                            name: "foo1"
                        },
                        {
                            name: "foo2"
                        },
                        {
                            name: "foo3"
                        },

                        // case: field.span is 5 of the first field; field.span is 8 for the second field; field.span
                        // is calculated for the third field
                        // result: the first field is on its own row; the second field overflows the first row and is
                        // on the second row with the third field; and field.labelSpan should match field.span
                        {
                            name: "foo4",
                            span: 5
                        },
                        {
                            name: "foo5",
                            span: 8
                        },
                        {
                            name: "foo6"
                        },

                        // case: field.span >= 12
                        // result: field.span should be 12; field overflows the row and its span dictates that the
                        // field be on its own row
                        {
                            name: "foo7",
                            span: 12
                        },

                        // case: field.span for the second field is too large to fit on the first row and too large for
                        // the third field to fit on the second row
                        // result: three fields are on their own rows
                        {
                            name: "foo8"
                        },
                        {
                            name: "foo9",
                            span: 10
                        },
                        {
                            name: "foo10"
                        }
                    ];
                app.user.setPreference('field_name_placement', 'field_on_top');
                view.meta.panels = [{
                    columns:     3,
                    labels:      true,
                    fields:      fields
                }];
                view.render();
                results = view.meta.panels[0].grid;

                // the number of rows found in this three-column grid
                expect(results.length).toBe(7);

                // result: three fields are on the same row
                expect(results[0].length).toBe(3);

                // case: field.span is 5 of the first field; field.span is 8 for the second field; field.span
                // is calculated for the third field; and field.labelSpan should match field.span
                expect(results[1].length).toBe(1);
                expect(results[1][0].span).toBe(5);
                expect(results[1][0].labelSpan).toBe(results[1][0].span);
                expect(results[2].length).toBe(2);
                expect(results[2][0].span).toBe(8);
                expect(results[2][0].labelSpan).toBe(results[2][0].span);
                expect(results[2][1].span).toBe(4);
                expect(results[2][1].labelSpan).toBe(results[2][1].span);

                // case: field.span >= 12
                expect(results[3].length).toBe(1);
                expect(results[3][0].span).toBe(12);

                // case: field.span for the second field is too large to fit on the first row and too large for
                // the third field to fit on the second row
                expect(results[4].length).toBe(1);
                expect(results[5].length).toBe(1);
                expect(results[6].length).toBe(1);
            });
        });

        describe("labels are inline", function() {
            it("Should create a one-column panel grid", function() {
                var results,
                    fields    = [
                        // case: field.span is undefined
                        // result: field.span should be 8 and field.labelSpan should be 4
                        {
                            name: "foo1"
                        },

                        // case: field.span is 0
                        // result: field.span should be 1 and field.labelSpan should be 4
                        {
                            name: "foo2",
                            span: 0
                        },

                        // case: field.span is 10
                        // result: field.span should be 6 and field.labelSpan should be 4
                        {
                            name: "foo3",
                            span: 10
                        },

                        // case: field.span >= 12
                        // result: field.span should be 8 and field.labelSpan should be 4
                        {
                            name: "foo4",
                            span: 20
                        },

                        // case: field.span is 12 and field.dismiss_label is true
                        // result: field.span should remain 12 and field.labelSpan should be 4
                        {
                            name:          "foo5",
                            span:          12,
                            dismiss_label: true
                        },

                        // case: field.span is 4 and field.dismiss_label is true
                        // result: field.span should remain 4 and field.labelSpan should be 4
                        {
                            name:          "foo6",
                            span:          4,
                            dismiss_label: true
                        },

                        // case: field.span is 10 and field.dismiss_label is true
                        // result: field.span should remain 10 and field.labelSpan should be 4
                        {
                            name:          "foo7",
                            span:          10,
                            dismiss_label: true
                        },

                        // case: field.span > 12 and field.dismiss_label is true
                        // result: field.span should be 12 and field.labelSpan should be 4
                        {
                            name:          "foo8",
                            span:          20,
                            dismiss_label: true
                        }
                    ];
                app.user.setPreference('field_name_placement', 'field_on_side');
                view.meta.panels = [{
                    columns:     1,
                    labels:      true,
                    fields:      fields
                }];
                view.render();
                results = view.meta.panels[0].grid;

                // each field should be on its own row
                expect(results.length).toBe(fields.length);

                // case: field.span is undefined
                expect(results[0][0].span).toBe(8);
                expect(results[0][0].labelSpan).toBe(4);

                // case: field.span is 0
                expect(results[1][0].span).toBe(1);
                expect(results[1][0].labelSpan).toBe(4);

                // case: field.span is 10
                expect(results[2][0].span).toBe(6);
                expect(results[2][0].labelSpan).toBe(4);

                // case: field.span >= 12
                expect(results[3][0].span).toBe(8);
                expect(results[3][0].labelSpan).toBe(4);

                // case: field.span is 12 and field.dismiss_label is true
                expect(results[4][0].span).toBe(12);
                expect(results[4][0].labelSpan).toBe(4);

                // case: field.span is 4 and field.dismiss_label is true
                expect(results[5][0].span).toBe(4);
                expect(results[5][0].labelSpan).toBe(4);

                // field.span is 10 and field.dismiss_label is true
                expect(results[6][0].span).toBe(10);
                expect(results[6][0].labelSpan).toBe(4);

                // case: field.span > 12 and field.dismiss_label is true
                expect(results[7][0].span).toBe(12);
                expect(results[7][0].labelSpan).toBe(4);
            });

            it("Should create a two-column panel grid", function() {
                var results,
                    fields    = [
                        // case: field.span >= 12
                        // result: field.span should be 10 and field.labelSpan should be 2; field's span dictates that
                        // the field should be on its own row
                        {
                            name: "foo1",
                            span: 12
                        },

                        // case: field.span >= 12 and field.dismiss_label is true
                        // result: field.span should be 12 and field.labelSpan should be 2; field overflows the row and
                        // its span dictates that the field be on its own row
                        {
                            name:          "foo2",
                            span:          20,
                            dismiss_label: true
                        },

                        // case: field.span is undefined for both fields
                        // result: field.span should be 4 and field.labelSpan should be 2 for both fields; both fields
                        // should fit on one row
                        {
                            name: "foo4"
                        },
                        {
                            name: "foo5"
                        },

                        // case: the sum of the spans for the first two fields and their labels < 12, and a third
                        // field is too large to fit on the row
                        // result: The first two fields should have field spans of 3 and label spans of 2. The sum of
                        // these spans is 10, so both fields will be on the same row. The third field naturally
                        // overflows the row and is added to the next row.
                        {
                            name: "foo6",
                            span: 5
                        },
                        {
                            name: "foo7",
                            span: 5
                        },
                        {
                            name: "foo8"
                        },

                        // case: field.span is undefined and field.dismiss_label is true
                        // result: field.span should be 6 and field.labelSpan should be 2
                        {
                            name:          "foo9",
                            dismiss_label: true
                        }
                    ];
                app.user.setPreference('field_name_placement', 'field_on_side');
                view.meta.panels = [{
                    columns:     2,
                    labels:      true,
                    fields:      fields
                }];
                view.render();
                results = view.meta.panels[0].grid;

                // the number of rows found in this two-column grid
                expect(results.length).toBe(5);

                // case: field.span >= 12
                expect(results[0].length).toBe(1);
                expect(results[0][0].span).toBe(10);
                expect(results[0][0].labelSpan).toBe(2);

                // case: field.span >= 12 and field.dismiss_label is true
                expect(results[1].length).toBe(1);
                expect(results[1][0].span).toBe(12);
                expect(results[1][0].labelSpan).toBe(2);

                // case: field.span is undefined for both fields
                expect(results[2].length).toBe(2);
                expect(results[2][0].span).toBe(4);
                expect(results[2][0].labelSpan).toBe(2);
                expect(results[2][1].span).toBe(4);
                expect(results[2][1].labelSpan).toBe(2);

                // case: the sum of the spans for the first two fields and their labels < 12, and a third
                // field is too large to fit on the row
                expect(results[3].length).toBe(2);
                expect(results[3][0].span).toBe(3);
                expect(results[3][0].labelSpan).toBe(2);
                expect(results[3][1].span).toBe(3);
                expect(results[3][1].labelSpan).toBe(2);
                expect(results[4].length).toBe(2); // 2 because the next test case adds a field to the fifth row
                expect(results[4][0].span).toBe(4);
                expect(results[4][0].labelSpan).toBe(2);

                // case: field.span is undefined and field.dismiss_label is true
                expect(results[4][1].span).toBe(6);
                expect(results[4][1].labelSpan).toBe(2);
            });

            it('Should create a three-column panel grid with label-field ratio of 2/2', function() {
                var results;
                var fields = [{
                    name: 'foo1'
                }, {
                    name: 'foo2'
                }, {
                    name: 'foo3'
                }, {
                    name: 'foo4'
                }, {
                    name: 'foo5'
                }];
                app.user.setPreference('field_name_placement', 'field_on_side');
                view.meta.panels = [{
                    columns: 3,
                    labels: true,
                    fields: fields
                }];
                view.render();
                results = view.meta.panels[0].grid;

                // the fields should appear in 2 rows
                expect(results.length).toBe(2);

                //first row should contain 3 fields
                expect(results[0].length).toBe(3);
                // second row should contain 2 fields
                expect(results[1].length).toBe(2);

                // each field and label size should be 2 and 2
                _.each(_.union(results[0], results[1]), function(field) {
                    expect(field.span).toBe(2);
                    expect(field.labelSpan).toBe(2);
                }, this);
            });

            it("Should create a five-column panel grid with no field.span's or label.span's less than 1", function() {
                var results,
                    fields = [
                        {
                            name: "foo1"
                        },
                        {
                            name: "foo2"
                        },
                        {
                            name: "foo3"
                        },
                        {
                            name: "foo4"
                        },
                        {
                            name: "foo5"
                        }
                    ];
                app.user.setPreference('field_name_placement', 'field_on_side');
                view.meta.panels = [{
                    columns:     5,
                    labels:      true,
                    fields:      fields
                }];
                view.render();
                results = view.meta.panels[0].grid;

                // the number of rows found in this five-column grid
                expect(results.length).toBe(1);

                // When displaying a five-column grid in which no fields have defined field.span or field.labelSpan,
                // the spans are calculated using Math.Floor, which leads to values that are 0. Unless a span was
                // intentionally defined to be 0, it should be assumed that 0 is an invalid span -- a span of 0 doesn't
                // make sense and isn't supported by Twitter Boostrap. Therefore, the spans should be set to a minimum
                // value of 1 if they are calculated to be less than 1.
                expect(results[0].length).toBe(5);

                _.each(results[0], function(field) {
                    expect(field.span).toBe(1);
                    expect(field.labelSpan).toBe(1);
                }, this);
            });
        });
    });
});

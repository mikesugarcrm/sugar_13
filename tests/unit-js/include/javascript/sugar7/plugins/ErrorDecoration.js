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
describe("Plugins.ErrorDecoration", function() {
    var moduleName = 'Cases',
        app,
        viewName = 'record',
        view,
        clock;

    beforeEach(function() {
        clock = sinon.useFakeTimers();
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate('record-decor', 'field', 'base', 'record-decor');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'field', 'record-decor');
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "panels": [{
                "name": "panel_body",
                "label": "LBL_PANEL_2",
                "columns": 1,
                "labels": true,
                "labelsOnTop": false,
                "placeholders":true,
                "fields": ["description","case_number","type"]
            }]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        view = SugarTest.createView("base", moduleName, viewName, null, null);
        view.getGridBuilder = function() {
            return {
                build: function() {
                    return {
                        grid: [
                            [{name: "description", type: "base", label: "description", span: 8, labelSpan: 4}],
                            [{name: "case_number", type: "float", label: "case_number", span: 8, labelSpan: 4}],
                            [{name: "type", type: "text", label: "type", span: 8, labelSpan: 4}]
                        ],
                        lastTabIndex: 0
                    };
                }
            };
        };
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        view = null;
        clock.restore();
    });
    describe('decorating fields', function() {
        it("should call decorateError on field during 'error:validation:field' event", function(){
            view.render();
            view.model.set({
                case_number: 123,
                description: 'Description'
            });
            var descriptionField = _.find(view.fields, function(field){
                return field.name === 'description';
            });
            descriptionField.action = 'edit';
            var stub = sinon.stub(descriptionField, 'decorateError');

            //Simulate a 'required' error on description field
            view.model.trigger('error:validation:description', {required : true});

            //Use sinon clock to delay expectations since decoration is deferred
            clock.tick(20);
            expect(stub.calledWithExactly({required: true})).toBe(true);
            stub.restore();
        });

        it("should call clearErrorDecoration on each field", function(){
            //Prepare test
            view.render();
            view.model.set({
                case_number: 123,
                description: 'Description'
            });
            var descriptionField = _.find(view.fields, function(field){
                return field.name === 'description';
            });
            var stub = sinon.stub(descriptionField, 'clearErrorDecoration');

            //Unit test
            view.clearValidationErrors(_.toArray(view.fields));

            //Use sinon clock to delay expectations since decoration is deferred
            clock.tick(20);
            expect(stub.calledOnce).toBe(true);
            stub.restore();
        });
    });
});

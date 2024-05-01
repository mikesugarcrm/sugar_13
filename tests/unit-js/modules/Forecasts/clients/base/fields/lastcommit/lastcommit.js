
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

describe("Forecasts.Base.Field.Lastcommit", function() {
    var app, field, model, fieldDef = {}, moduleName = 'Forecasts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sinon.stub(app.utils, 'getColumnVisFromKeyMap').callsFake(function() {
            return true;
        });

        model = new Backbone.Model({
            'best_case': 100,
            'worst_case': 200
        });

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField("base", "best_case", 'lastcommit', 'list', fieldDef, moduleName, model, null, true);
    });

    afterEach(function() {
        sinon.restore();
        field = null;
        fieldDef = null;
        app = null;
    });
    describe('processDataPoints', function() {
        beforeEach(function() {
            field.points = ['best_case', 'worst_case'];
            sinon.stub(app.template, 'getField').callsFake(function() {
                return function() {
                    return 'no_access';
                };
            });
        });

        afterEach(function() {
            field.points = [];
            app.template.getField.restore();
        });

        it('both point should have values', function() {
            var points = field.processDataPoints(model);
            expect(points[0].value).not.toBeUndefined();
            expect(points[0].error).toBeUndefined();
            expect(points[1].value).not.toBeUndefined();
            expect(points[1].error).toBeUndefined();
        });

        it('best_case should have error', function() {
            sinon.stub(app.user, 'getAcls').callsFake(function() {
                return {
                    'ForecastWorksheets': {
                        'fields': {
                            'best_case': {
                                'read': 'no',
                                'write': 'no',
                                'create': 'no'
                            }
                        }
                    }
                };
            });
            var points = field.processDataPoints(model);
            // 0 is best_case
            expect(points[0].value).toBeUndefined();
            expect(points[0].error).not.toBeUndefined();
            expect(points[0].error).toEqual('no_access');
            // 1 is likely_case
            expect(points[1].value).not.toBeUndefined();
            expect(points[1].error).toBeUndefined();
            app.user.getAcls.restore();
        });
    });
});

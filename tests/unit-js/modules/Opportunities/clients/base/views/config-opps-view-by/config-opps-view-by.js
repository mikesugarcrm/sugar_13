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
describe('Opportunities.View.ConfigOppsViewBy', function() {
    var app,
        view,
        options,
        meta,
        context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        var cfgModel = new Backbone.Model({
            opps_view_by: 'Opportunities'
        });

        context.set({
            model: cfgModel,
            module: 'Opportunities'
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

        sinon.stub(app.template, 'getView').callsFake(function(view) {
            return function() {
                return view;
            };
        });

        // load the parent config-panel view
        SugarTest.loadComponent('base', 'view', 'config-panel');
        view = SugarTest.createView('base', 'Opportunities', 'config-opps-view-by', meta, null, true);
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should set currentOppsViewBySetting', function() {
            view.initialize(options);
            expect(view.currentOppsViewBySetting).toEqual('Opportunities');
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.stub(view, 'showRollupOptions');
            sinon.stub(view, '_updateTitleValues').callsFake(function() {
            });
        });
        it('should call showRollupOptions()', function() {
            view._render();
            expect(view.showRollupOptions).toHaveBeenCalled();
        });
    });

    describe('_updateTitleValues()', function() {
        beforeEach(function() {
            sinon.stub(view, 'showRollupOptions');
            sinon.stub(view, '_getFieldOptions').callsFake(function() {
                return {
                    'testValue': 'Test Value'
                };
            });
        });
        it('should set this.titleSelectedValues', function() {
            view.model.set('opps_view_by', 'testValue');
            view._updateTitleValues();
            expect(view.titleSelectedValues).toBe('Test Value');
        });
    });
    describe('_getFieldOptions', function() {
        var field;
        beforeEach(function() {
            field = {
                items: {
                    'testValue': 'Test Value'
                },
                once: function() {
                }
            };
        });
        it('with items defined', function() {
            sinon.stub(field, 'once');
            sinon.stub(view, 'getField').callsFake(function() {
                return field;
            });

            view._getFieldOptions();
            expect(field.once).not.toHaveBeenCalled();
        });

        it('with items is undefined', function() {
            field.items = undefined;

            sinon.stub(field, 'once');
            sinon.stub(view, 'getField').callsFake(function() {
                return field;
            });

            view._getFieldOptions();
            expect(field.once).toHaveBeenCalled();
            expect(view.waitingForFieldItems).toBe(true);
        });
    });
});

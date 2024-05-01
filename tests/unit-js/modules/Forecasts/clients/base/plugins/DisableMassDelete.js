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
describe('Forecasts.Base.Plugins.DisableMassDelete', function() {
    var app,
        view,
        layout,
        moduleName = 'Opportunities',
        context,
        options;

    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        context.set({mass_collection: new Backbone.Collection()});

        SugarTest.loadFile('../modules/Forecasts/clients/base/plugins', 'DisableMassDelete', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        options = {
            meta: {
                panels: [{
                    fields: []
                }]
            }
        };

        SugarTest.seedMetadata(true);
        app.metadata.getModule('Forecasts', 'config').is_setup = 1;
        SugarTest.loadComponent('base', 'view', 'massupdate');

        layout = SugarTest.createLayout('base', moduleName, 'list', null, null);
        view = SugarTest.createView('base', moduleName, 'massupdate', options, context, true, layout);
    });

    afterEach(function() {
        sinon.restore();
        delete app.plugins.plugins['field']['DisableMassDelete'];
        view = null;
        app = null;
        context = null;
        options = null;
        layout = null;
    });

    describe('when mass_collection does not exist', function() {
        it('should return and not call warn delete', function() {
            view.context.set('mass_collection', undefined);
            sinon.spy(app.metadata, 'getModule');
            view._warnDelete();
            expect(app.metadata.getModule).not.toHaveBeenCalled();
        });
    });

    describe('when nothing is closed', function() {
        beforeEach(function() {
            sinon.stub(view, 'getMassUpdateModel').callsFake(function() {
                return {models: []};
            });
        });

        afterEach(function() {
            view.getMassUpdateModel.restore();
        });

        it('_warnDelete message should return null', function() {
            var message = view._warnDelete();
            expect(message).toEqual(null);
        });

        it('checkMassUpdateClosedModels should return false', function() {
            var hasClosedModels = view.checkMassUpdateClosedModels();
            expect(hasClosedModels).toBeFalsy();
        });
    });

    describe('when something is closed', function() {
        beforeEach(function() {
            sinon.stub(view, 'getMassUpdateModel').callsFake(function() {
                return {
                    models: [new Backbone.Model({
                        id: 'aaa',
                        name: 'boo',
                        module: moduleName,
                        sales_status: 'Closed Won',
                        closed_revenue_line_items: 0
                    })],
                    remove: $.noop,
                    setChunkSize: $.noop,
                    off: $.noop
                };
            });
        });

        afterEach(function() {
            view.getMassUpdateModel.restore();
        });

        it('should return WARNING_NO_DELETE_SELECTED_STATUS', function() {
            var message = view._warnDelete();
            expect(message).toEqual('WARNING_NO_DELETE_SELECTED_STATUS');
        });

        it('checkMassUpdateClosedModels should return true', function() {
            var hasClosedModels = view.checkMassUpdateClosedModels();
            expect(hasClosedModels).toBeTruthy();
        });
    });

    describe('when an opp has a closed RLI', function() {
        beforeEach(function() {
            sinon.stub(view, 'getMassUpdateModel').callsFake(function() {
                return {
                    models: [new Backbone.Model({
                        id: 'aaa',
                        name: 'boo',
                        module: moduleName,
                        sales_status: 'In Progress',
                        closed_revenue_line_items: 1
                    })],
                    remove: $.noop,
                    setChunkSize: $.noop,
                    off: $.noop
                };
            });
        });

        afterEach(function() {
            view.getMassUpdateModel.restore();
        });

        it('should return WARNING_NO_DELETE_CLOSED_SELECTED_STATUS', function() {
            var message = view._warnDelete();
            expect(message).toEqual('WARNING_NO_DELETE_CLOSED_SELECTED_STATUS');
        });
    });

    describe('when an opp is closed and it also has a closed RLI', function() {
        beforeEach(function() {
            sinon.stub(view, 'getMassUpdateModel').callsFake(function() {
                return {
                    models: [new Backbone.Model({
                        id: 'aaa',
                        name: 'boo',
                        module: moduleName,
                        sales_status: 'Closed Won',
                        closed_revenue_line_items: 1
                    })],
                    remove: $.noop,
                    setChunkSize: $.noop,
                    off: $.noop
                };
            });
        });

        afterEach(function() {
            view.getMassUpdateModel.restore();
        });

        it('should return WARNING_NO_DELETE_SELECTED_STATUS', function() {
            var message = view._warnDelete();
            expect(message).toEqual('WARNING_NO_DELETE_SELECTED_STATUS');
        });
    });

    describe('when an item has a closed sales_stage', function() {
        beforeEach(function() {
            sinon.stub(view, 'getMassUpdateModel').callsFake(function() {
                return {
                    models: [new Backbone.Model({
                        id: 'aaa',
                        name: 'boo',
                        module: moduleName,
                        sales_stage: 'Closed Won',
                        sales_status: null,
                        closed_revenue_line_items: 0
                    })],
                    remove: $.noop,
                    setChunkSize: $.noop,
                    off: $.noop
                };
            });
        });

        afterEach(function() {
            view.getMassUpdateModel.restore();
        });

        it('should return WARNING_NO_DELETE_SELECTED_STATUS', function() {
            var message = view._warnDelete();
            expect(message).toEqual('WARNING_NO_DELETE_SELECTED_STATUS');
        });
    });
});

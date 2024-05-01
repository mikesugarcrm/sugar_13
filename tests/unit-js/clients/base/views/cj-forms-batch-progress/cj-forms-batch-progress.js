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
describe('Base.View.CJFormsBatchProgressView', function() {
    let app;
    let view;
    let context;
    let moduleName = 'Accounts';
    let viewName = 'cj-forms-batch-progress';
    let layoutName = 'record';
    let drawerBefore;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'massupdate-progress');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadPlugin('ToggleMoreLess');
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                'panels': [
                    {
                        fields: []
                    }
                ]
            },
            moduleName
        );
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            module: moduleName,
            layout: layoutName,
            parentModel: new Backbone.Model(),
        });
        context.prepare();
        layout = SugarTest.createLayout('base', moduleName, 'dashboard');

        sinon.stub(app.CJBaseHelper, 'getBatchChunk').returns(2);
        drawerBefore = app.drawer;
        app.drawer = {
            offBefore: sinon.stub(),
            before: sinon.stub()
        };

        view = SugarTest.createView('base', moduleName, viewName, {}, context, null, layout);
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();

        app.drawer = drawerBefore;
        context = null;
        layout = null;
        view = null;
    });

    describe('reset', function() {
        it('should reset processedCount variable to 0', function() {
            view.reset();

            expect(view.processedCount).toEqual(0);
        });

        it('should reset failsCount variable to 0', function() {
            view.reset();

            expect(view.failsCount).toEqual(0);
        });

        it('should reset totalRecord variable to 0', function() {
            view.reset();

            expect(view.totalRecord).toEqual(0);
        });
    });

    describe('setTotalRecords', function() {
        it('should set totalRecord variable', function() {
            view.setTotalRecords(5);

            expect(view.totalRecord).toEqual(5);
        });
    });

    describe('getTotalRecords', function() {
        it('should provide totalRecord value', function() {
            view.totalRecord = 2;

            expect(view.getTotalRecords()).toEqual(2);
        });
    });

    describe('incrementProgressSize', function() {
        it('should increment processed count', function() {
            view.totalRecord = 4;
            view.incrementProgressSize();

            expect(view.processedCount).toEqual(view.chunkCount);
        });
    });

    describe('showProgress', function() {
        it('should call app.drawer.before', function() {
            view.showProgress();

            expect(app.drawer.before).toHaveBeenCalled();
        });

        it('should _super with showProgress as a parameter', function() {
            view.showProgress();

            expect(view._super).toHaveBeenCalledWith('showProgress');
        });
    });

    describe('hideProgress', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'dismiss');
            sinon.stub(view, 'hide');
            sinon.stub(view, '_dispose');
        });

        it('should call app.drawer.offBefore', function() {
            view.hideProgress();

            expect(app.drawer.offBefore).toHaveBeenCalled();
        });

        it('should app.alert.dismiss with stop_confirmation as a parameter', function() {
            view.hideProgress();

            expect(app.alert.dismiss).toHaveBeenCalledWith('stop_confirmation');
        });

        it('should call hide method', function() {
            view.hideProgress();

            expect(view.hide).toHaveBeenCalled();
        });
    });

    describe('onItemProcessed', function() {
        beforeEach(function() {
            sinon.stub(view, 'updateProgress');
            sinon.stub(view, 'incrementProgressSize');
        });

        it('should call updateProgress method', function() {
            view.onItemProcessed();

            expect(view.updateProgress).toHaveBeenCalled();
        });

        it('should call incrementProgressSize method', function() {
            view.onItemProcessed();

            expect(view.incrementProgressSize).toHaveBeenCalled();
        });
    });

    describe('updateProgress', function() {
        beforeEach(function() {
            sinon.stub(view, 'getProgressSize').returns(0);
            view.$holders.progressbar = {
                css: sinon.stub()
            };
        });

        it('should call getProgressSize method', function() {
            view.updateProgress();

            expect(view.getProgressSize).toHaveBeenCalled();
        });

        it('should call css method of progressbar', function() {
            view.currentProgress = 0;
            view.totalRecord = 1;
            view.updateProgress();

            expect(view.$holders.progressbar.css).toHaveBeenCalledWith({'width': '0%'});
        });
    });

    describe('updateModal', function() {
        beforeEach(function() {
            view.currentProgress = 0;
            view.$holders.progressbar = {
                css: sinon.stub()
            };
            view.$holders.message = {
                text: sinon.stub()
            };
        });

        it('should call text method of message', function() {
            const message = 'Test Update';
            view.updateModal(message);

            expect(view.$holders.message.text).toHaveBeenCalledWith(message);
        });

        it('should call css method of progressbar', function() {
            view.updateModal();

            expect(view.$holders.progressbar.css).toHaveBeenCalledWith({'width': '10%'});
        });
    });
});

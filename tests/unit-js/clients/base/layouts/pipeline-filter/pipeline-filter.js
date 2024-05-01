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

describe('Base.Layouts.PipelineFilter', function() {
    var app;
    var context;
    var layout;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.prepare();

        options = {
            context: context,
        };

        sinon.stub(app.metadata, 'getModule').withArgs('VisualPipeline', 'config').returns({
            enabled_modules: [
                'Cases',
                'Leads',
                'Opportunities',
                'Tasks',
            ]
        });

        SugarTest.loadComponent('base', 'layout', 'filter');
        var parentLayout = SugarTest.createLayout('base', 'Opportunities', 'list', {}, context);
        layout = SugarTest.createLayout('base', 'Opportunities', 'pipeline-filter', {}, context, false, {
            layout: parentLayout
        });
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
    });

    describe('getFilterEditStateKey', function() {
        var currentModule;
        var layoutType;

        beforeEach(function() {
            layoutType = layout.layoutType;
            currentModule = layout.layout.currentModule;
        });

        afterEach(function() {
            sinon.restore();
            layout.layout.currentModule = currentModule;
            currentModule = null;
            layout.layoutType = layoutType;
            layoutType = null;
        });

        it('should return key for records if layoutType is pipeline-records', function() {
            var keyStub = sinon.stub(app.user.lastState, 'key');
            layout.layoutType = 'pipeline-records';
            layout.layout.currentModule = 'Opportunities';
            layout.getFilterEditStateKey();
            expect(keyStub.lastCall.args[0]).toEqual('edit-Opportunities-records');
        });

        it('should call _super if layoutType is not pipeline-records', function() {
            var superStub = sinon.stub(layout, '_super');
            layout.layoutType = 'someType';
            layout.getFilterEditStateKey();
            expect(superStub).toHaveBeenCalledWith('getFilterEditStateKey');
        });
    });

    describe('applyFilter', function() {
        describe('when layout.layoutType is pipeline-records', function() {
            it('should call filterPipeline() method', function() {
                layout.layoutType = 'pipeline-records';
                sinon.stub(layout, 'filterPipeline').callsFake(function() {});
                layout.applyFilter();

                expect(layout.filterPipeline).toHaveBeenCalled();
            });
        });
        describe('when layout.layoutType is not pipeline-records', function() {
            it('should call _super.applyFilter() method', function() {
                layout.layoutType = 'demoType';
                sinon.stub(layout, '_super').callsFake(function() {});
                layout.applyFilter();

                expect(layout._super).toHaveBeenCalledWith('applyFilter');
            });
        });
    });

    describe('filterPipeline', function() {
        var query;
        var def;
        var origFilterDef;

        beforeEach(function() {
            query = 'testQuery';
            def = [{$owner: 'testOwner'}];

            var elValSpy = sinon.spy(jQuery.fn, 'val');
            sinon.stub(layout, 'getComponent').callsFake(function() {
                return {
                    $el: {
                        val: elValSpy
                    }
                };
            });
            sinon.stub(layout.context, 'get').callsFake(function() {
                return {
                    origFilterDef: [{$owner: 'testOwner'}],
                    filterDef: ['testDef']
                };
            });
            origFilterDef = layout.context.get().origFilterDef;
            sinon.stub(layout, 'buildFilterDef').callsFake(function() {
                return ['testDef'];
            });
            sinon.stub(layout.context, 'trigger').callsFake(function() {});
        });

        afterEach(function() {
            query = null;
            def = null;
            origFilterDef = null;
        });
        describe('when the query is empty', function() {
            it('should call layout.getComponent method and assign value to query', function() {
                query = null;
                layout.filterPipeline(query, def);

                expect(layout.getComponent).toHaveBeenCalled();
                expect(jQuery.fn.val).toHaveBeenCalled();
            });
        });

        it('should call context.get with collection', function() {
            layout.filterPipeline(query, def);

            expect(layout.context.get).toHaveBeenCalledWith('collection');
        });

        it('should call layout.buildFilterDef', function() {
            layout.filterPipeline(query, def);

            expect(layout.buildFilterDef).toHaveBeenCalledWith(def, query, layout.context);
        });

        it('should set filterDef and origFilterDef for ctxCollection', function() {
            layout.filterPipeline(query, def);
            var ctxCollection = layout.context.get('collection');
            var filterDef = layout.buildFilterDef(def, query, layout.context);

            expect(ctxCollection.filterDef).toEqual(filterDef);
            expect(ctxCollection.origFilterDef).toEqual(def);
        });

        it('should call layout.context.trigger with pipeline:recordlist:filter:changed and filterDef', function() {
            layout.filterPipeline(query, def);
            var filterDef = layout.buildFilterDef(def, query, layout.context);

            expect(layout.context.trigger).toHaveBeenCalledWith('pipeline:recordlist:filter:changed', filterDef);
        });
    });

    describe('getRelevantContextList', function() {
        var contextList;

        describe('when layout.layoutType is pipeline-records', function() {
            var context;

            describe('when context does not have modelId and has collection', function() {
                it('should push context to contextList', function() {
                    layout.layoutType = 'pipeline-records';
                    context = layout.context;
                    sinon.stub(layout.context, 'get').withArgs('modelId').returns(null);
                    sinon.stub(layout.context, 'has').withArgs('collection').returns(true);
                    contextList = layout.getRelevantContextList();

                    expect(contextList).toEqual([context]);
                });
            });
            describe('when context has modelId or not have collection', function() {
                describe('when context has modelId and collection', function() {
                    it('should push context to contextList', function() {
                        layout.layoutType = 'pipeline-records';
                        context = layout.context;
                        sinon.stub(layout.context, 'get').withArgs('modelId')
                            .returns({testData: 'testData'});
                        sinon.stub(layout.context, 'has').withArgs('collection').returns(true);
                        contextList = layout.getRelevantContextList();

                        expect(contextList).not.toEqual([context]);
                    });
                });
                describe('when context does not have modelId and does not have collection', function() {
                    it('should push context to contextList', function() {
                        layout.layoutType = 'pipeline-records';
                        context = layout.context;
                        sinon.stub(layout.context, 'get').withArgs('modelId').returns(null);
                        sinon.stub(layout.context, 'has').withArgs('collection').returns(false);
                        contextList = layout.getRelevantContextList();

                        expect(contextList).not.toEqual([context]);
                    });
                });
            });
        });
        describe('when layout.layoutType is not pipeline-records', function() {
            it('should call _super.getRelevantContextList() method', function() {
                layout.layoutType = 'demoType';
                sinon.stub(layout, '_super').callsFake(function() {});
                layout.getRelevantContextList();

                expect(layout._super).toHaveBeenCalledWith('getRelevantContextList');
            });
        });
    });
});

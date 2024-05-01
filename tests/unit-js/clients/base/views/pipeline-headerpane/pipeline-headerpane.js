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

describe('Base.Views.PipelineHeaderpane', function() {
    var layout;
    var view;
    var app;
    var layout;
    var context;
    var viewMeta;

    beforeEach(function() {
        app = SUGAR.App;
        var context = new app.Context({
            module: 'Opportunities',
            model: app.data.createBean('Opportunities'),
            layout: 'pipeline-records',
        });
        viewMeta = {
            fields: {
                label: 'LBL_PIPELINE_TYPE',
                name: 'pipeline_type',
                type: 'pipeline-type',
            }
        };
        sinon.stub(app.metadata, 'getModule').withArgs('VisualPipeline', 'config').returns(
            {
                table_header: {
                    Leads: 'status',
                    Opportunities: 'sales_status',
                }
            }
        );
        layout = SugarTest.createLayout('base', 'Opportunities', 'pipeline-records');
        view = SugarTest.createView('base', 'Opportunities', 'pipeline-headerpane', viewMeta, context, false, layout);
        sinon.stub(view.context, 'on').callsFake(function() {});
        sinon.stub(view, '_super').callsFake(function() {});
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('initialize()', function() {

        beforeEach(function() {
            sinon.stub(view, 'createNewRecord').callsFake(function() {});
            view.initialize({context: context});
        });

        it('should call view.context.on with button:pipeline_create_button:click', function() {

            expect(view.context.on).toHaveBeenCalledWith('button:pipeline_create_button:click', view.createNewRecord,
                view);
        });

        it('should call app.metadata.getModule method and assign value to view.table_header', function() {

            expect(app.metadata.getModule).toHaveBeenCalledWith('VisualPipeline', 'config');
            expect(view.table_header).toEqual('sales_status');
        });

        it('should populate the view.pipelineTypes array', function() {

            expect(view.pipelineTypes).toEqual([
                'LBL_PIPELINE_TYPE',
                'pipeline_type',
                'pipeline-type',
            ]);
        });
    });

    describe('changePipeline', function() {
        var evt;

        beforeEach(function() {
            evt = {
                preventDefault: $.noop,
                currentTarget: '.tab-badgeable > a[name=testBtn]',
            };

            removeClassStub = sinon.stub();
            addClassStub = sinon.stub();
            hasClassStub = sinon.stub();

            sinon.stub(view, '$').callsFake(function() {
                return {
                    data: function() {
                        return 'sales_status';
                    },
                    addClass: addClassStub,
                    removeClass: removeClassStub,
                    hasClass: hasClassStub,
                };
            });

            sinon.stub(view.layout, 'getComponent').returns({
                trigger: function() {}
            });
            sinon.stub(view.layout.getComponent('filterpanel'), 'trigger');
        });

        afterEach(function() {
            evt = null;
            view = null;
            layout = null;

            sinon.restore();
            addClassStub = null;
            removeClassStub = null;
            hasClassStub = null;
        });

        it('should not trigger filter:apply when pipeline view is already selected', function() {
            hasClassStub.returns(true);
            view.changePipeline(evt);

            expect(view.layout.getComponent('filterpanel').trigger).not.toHaveBeenCalledWith('filter:apply');
        });

        describe('when pipeline view is not already selected', function() {
            var pipelineType;

            beforeEach(function() {
                pipelineType = 'testType';

                hasClassStub.returns(false);
                sinon.stub(view.context, 'get').callsFake(function() {
                    return {
                        set: function() {
                            return 'testType';
                        }
                    };
                });

                view.collection = {
                    off: $.noop,
                    origFilterDef: {
                        $favorite: ''
                    }
                };
                view.changePipeline(evt);
            });

            it('should remove selected class from previous button', function() {
                expect(removeClassStub).toHaveBeenCalled();
            });

            it('should add selected class to current button', function() {
                expect(addClassStub).toHaveBeenCalled();
            });

            it('should get the pipelineType data from current button', function() {
                pipelineType = view.$().data();

                expect(pipelineType).toEqual('sales_status');
            });

            it('should set the pipelineType in the context model', function() {
                pipelineType = view.context.get().set();

                expect(pipelineType).toEqual('testType');
            });

            it('should trigger filter:apply', function() {
                expect(view.layout.getComponent('filterpanel').trigger).toHaveBeenCalled();
            });
        });
    });

    describe('createNewRecord', function() {
        it('should call app.drawer.open', function() {
            app.drawer = {
                close: $.noop,
                count: $.noop,
                reset: $.noop,
                open: $.noop
            };
            sinon.stub(app.drawer, 'open').callsFake($.noop);

            view.createNewRecord();
            expect(app.drawer.open).toHaveBeenCalled();
        });
    });
});

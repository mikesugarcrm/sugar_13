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
describe('Leads.Base.View.PipelineRecordlistContent', function() {
    var app;
    var view;
    var model;
    var pipelineData;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'pipeline-recordlist-content');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Leads', 'pipeline-recordlist-content', null, null, true);
        model = app.data.createBean('Leads');
        pipelineData = {
            ui: {},
            oldCollection: {},
            newCollection: {}
        };
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        sinon.restore();
    });

    describe('saveModel', function() {
        beforeEach(function() {
            app.drawer = {
                open: function() {},
                close: function() {}
            };
            sinon.stub(app.lang, 'getAppListStrings').returns({
                '': '',
                'New': 'New',
                'Assigned': 'Assigned',
                'In Process': 'In Process',
                'Converted': 'Converted',
                'Recycled': 'Recycled',
                'Dead': 'Dead',
            });
            sinon.spy(model, 'get');
            sinon.stub(view, '_postChange');
            sinon.stub(app.drawer, 'open');
            sinon.stub(app.alert, 'show');
        });

        afterEach(function() {
            delete app.drawer;
        });

        describe('when the header field is "status"', function() {
            beforeEach(function() {
                view.headerField = 'status';
            });

            describe('and the record is already converted', function() {
                beforeEach(function() {
                    model.set('converted', true);
                });

                it('should cancel the tile move', function() {
                    view.saveModel(model, pipelineData);
                    expect(view._postChange).toHaveBeenCalledWith(model, true, pipelineData);
                });

                it('should display an error message to the user', function() {
                    view.saveModel(model, pipelineData);
                    expect(app.alert.show).toHaveBeenCalled();
                });
            });

            describe('and the record is not yet converted', function() {
                beforeEach(function() {
                    model.set('converted', false);
                });

                describe('and the tile is moved to converted status', function() {
                    beforeEach(function() {
                        pipelineData.newCollection.headerKey = 'Converted';
                    });

                    it('should open the lead convert view in a drawer', function() {
                        view.saveModel(model, pipelineData);
                        expect(app.drawer.open).toHaveBeenCalledWith({
                            layout: 'convert',
                            context: {
                                forceNew: true,
                                skipFetch: true,
                                module: 'Leads',
                                leadsModel: model
                            }
                        });
                    });
                });

                describe('and the tile is moved to a non-converted status', function() {
                    it('should run the parent saveModel instead', function() {
                        view.saveModel(model, pipelineData);
                        expect(view._postChange).not.toHaveBeenCalled();
                        expect(app.alert.show).not.toHaveBeenCalled();
                        expect(app.drawer.open).not.toHaveBeenCalled();
                        expect(view._super).toHaveBeenCalled();
                    });
                });
            });
        });

        describe('when the header field is not "status"', function() {
            it('should run the parent saveModel instead', function() {
                view.saveModel(model, pipelineData);
                expect(view._postChange).not.toHaveBeenCalled();
                expect(app.alert.show).not.toHaveBeenCalled();
                expect(app.drawer.open).not.toHaveBeenCalled();
                expect(view._super).toHaveBeenCalled();
            });
        });
    });
});

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
describe('ConsoleConfiguration.View.ConfigTabSettingsView', function() {
    var app;
    var view;
    var layout;
    var context;
    var ctxModel;
    var parentLayout;

    beforeEach(function() {
        app = SUGAR.App;

        context = app.context.getContext();
        ctxModel = app.data.createBean('ConsoleConfiguration');
        context.set('model', ctxModel);
        context.set('collection', app.data.createBeanCollection('ConsoleConfiguration'));

        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        parentLayout = SugarTest.createLayout('base', null, 'base');
        layout = SugarTest.createLayout('base', 'ConsoleConfiguration', 'config-drawer', {},  context);
        layout.name = 'side-pane';
        layout.layout = parentLayout;

        view = SugarTest.createView('base', 'ConsoleConfiguration', 'config-tab-settings', {}, context, true, layout);
        sinon.stub(view, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.stub(view, 'render').callsFake(function() {});
            sinon.stub(view.collection, 'on').callsFake(function() {});
            view.bindDataChange();
        });

        it('should call view.context.on with change', function() {

            expect(view.collection.on).toHaveBeenCalledWith('add remove reset');
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'get').callsFake(function() {});
            sinon.stub(view, 'toggleFreezeColumn');
            sinon.stub(view, '$').callsFake(function() {
                return {
                    tabs: sinon.stub()
                };
            });
            sinon.stub(view.context, 'trigger').callsFake(function() {});
            view.render();
        });

        it('should call view._super with render', function() {

            expect(view._super).toHaveBeenCalledWith('render');
        });

        it('should call toggleFreezeColumn method', function() {

            expect(view.toggleFreezeColumn).toHaveBeenCalled();
        });

        it('should call view.$ method with #tabs', function() {

            expect(view.$).toHaveBeenCalledWith('#tabs');
        });
    });

    describe('toggleFreezeColumn', function() {
        let closestStub;
        let parentStub;

        it('should not do anything if allowFreezeFirstColumn is true', function() {
            app.config.allowFreezeFirstColumn = true;
            sinon.stub(view, '$');

            view.toggleFreezeColumn();
            expect(view.$).not.toHaveBeenCalled();
        });

        describe('when allowFreezeFirstColumn is false', function() {
            beforeEach(function() {
                app.config.allowFreezeFirstColumn = false;
            });

            it('should get the freeze_first_column and row-fluid element', function() {
                closestStub = sinon.stub().returns({
                    length: 0,
                    hide: sinon.stub()
                });
                sinon.stub(view, '$').callsFake(function() {
                    return {
                        length: 1,
                        closest: closestStub
                    };
                });
                view.toggleFreezeColumn();

                expect(view.$).toHaveBeenCalledWith('.freeze-config');
                expect(view.$().closest).toHaveBeenCalledWith('.row-fluid');
                expect(view.$().closest().hide).not.toHaveBeenCalled();
            });

            it('should hide the title and checkbox element', function() {
                parentStub = sinon.stub().returns({
                    length: 1,
                    children: sinon.stub().returns({
                        eq: sinon.stub().returns({
                            hide: sinon.stub()
                        })
                    })
                });
                closestStub = sinon.stub().returns({
                    length: 1,
                    index: sinon.stub().returns(5),
                    parent: parentStub,
                    hide: sinon.stub()
                });
                sinon.stub(view, '$').callsFake(function() {
                    return {
                        length: 1,
                        closest: closestStub
                    };
                });
                view.toggleFreezeColumn();

                expect(view.$().closest().index).toHaveBeenCalled();
                expect(view.$().closest().parent).toHaveBeenCalled();
                expect(view.$().closest().parent().children).toHaveBeenCalled();
                expect(view.$().closest().parent().children().eq).toHaveBeenCalledWith(4);
                expect(view.$().closest().parent().children().eq().hide).toHaveBeenCalled();
                expect(view.$().closest().hide).toHaveBeenCalled();
            });
        });
    });
});

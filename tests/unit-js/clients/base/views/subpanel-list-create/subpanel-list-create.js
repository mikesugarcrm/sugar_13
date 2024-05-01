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
describe('Base.View.SubpanelListCreate', function() {
    var app;
    var view;
    var layout;
    var parentLayout;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        var context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            collection: new Backbone.Collection()
        });
        context.parent = new Backbone.Model();

        layout = SugarTest.createLayout("base", null, "subpanels", null, null);
        parentLayout = SugarTest.createLayout("base", null, "list", null, null);
        layout.layout = parentLayout;

        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'subpanel-list');

        if (!_.isFunction(app.utils.generateUUID)) {
            app.utils.generateUUID = function() {}
        }
        sandbox.stub(app.utils, 'generateUUID').callsFake(function() {
            return 'testUUID'
        });
        app.routing.start();
        sandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                opps_view_by: 'RevenueLineItems'
            };
        });

        view = SugarTest.createView('base', null, 'subpanel-list-create', {}, context, false, layout, true);
    });

    afterEach(function() {
        sandbox.restore();
        view.dispose();
        view = null;
        app.router.stop();
    });

    describe('initialize', function() {
        it('should set the dataView on the context', function() {
            expect(view.context.get('dataView')).toBe('subpanel-list-create');
        });

        it('should set isCreateSubpanel to be true on the context', function() {
            expect(view.context.get('isCreateSubpanel')).toBeTruthy();
        });
    });

    describe('bindDataChange', function() {
        var appCtrlCtxSpy;

        beforeEach(function() {
            view.collection = new Backbone.Collection();
            view.context.set('link', 'rlis345');
            sandbox.stub(view.context.parent, 'on').callsFake($.noop);
            sandbox.stub(view, 'resetSubpanel').callsFake($.noop);
            appCtrlCtxSpy = sandbox.stub(app.controller.context, 'on');
        });

        afterEach(function() {
            appCtrlCtxSpy.restore();
            appCtrlCtxSpy = null;
        });

        describe('when Opps Config opps_view_by is Opportunities', function() {
            it('should not set listener on app.controller.context', function() {
                app.metadata.getModule.restore();
                sandbox.stub(app.metadata, 'getModule').callsFake(function() {
                    return {
                        opps_view_by: 'Opportunities'
                    };
                });
                sandbox.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Opportunities: {},
                        RevenueLineItems: {}
                    };
                });
                view.bindDataChange();

                var viewDetails = view.closestComponent('record') ?
                    view.closestComponent('record') :
                    view.closestComponent('create');
                if (!_.isUndefined(viewDetails)) {
                    expect(appCtrlCtxSpy).not.toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }
            });
        });

        describe('when Opps Config opps_view_by is RevenueLineItems', function() {
            beforeEach(function() {
                app.metadata.getModule.restore();
                sandbox.stub(app.metadata, 'getModule').callsFake(function() {
                    return {
                        opps_view_by: 'RevenueLineItems'
                    };
                });
            });

            it('should not set listener on app.controller.context when user has no access to Opps edit', function() {
                sandbox.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Opportunities: {
                            edit: 'no'
                        },
                        RevenueLineItems: {}
                    };
                });
                view.bindDataChange();

                var viewDetails = view.closestComponent('record') ?
                    view.closestComponent('record') :
                    view.closestComponent('create');
                if (!_.isUndefined(viewDetails)) {
                    expect(appCtrlCtxSpy).not.toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }
            });

            it('should not set listener on app.controller.context when user has no access to RLIs', function() {
                sandbox.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Opportunities: {},
                        RevenueLineItems: {
                            access: 'no'
                        }
                    };
                });
                view.bindDataChange();

                var viewDetails = view.closestComponent('record') ?
                    view.closestComponent('record') :
                    view.closestComponent('create');
                if (!_.isUndefined(viewDetails)) {
                    expect(appCtrlCtxSpy).not.toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }
            });

            it('should not set listener on app.controller.context when user has no edit access to RLIs', function() {
                sandbox.stub(app.user, 'getAcls').callsFake(function() {
                    return {
                        Opportunities: {},
                        RevenueLineItems: {
                            edit: 'no'
                        }
                    };
                });
                view.bindDataChange();

                var viewDetails = view.closestComponent('record') ||
                    view.closestComponent('create') ||
                    view.closestComponent('convert');

                if (!_.isUndefined(viewDetails)) {
                    expect(appCtrlCtxSpy).not.toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }
            });
        });

        describe('', function() {
            beforeEach(function() {
                view.bindDataChange();
            });

            it('should set event listeners on subpanel:validateCollection:rlis345', function() {
                expect(view.context.parent.on).toHaveBeenCalledWith('subpanel:validateCollection:rlis345');
            });

            it('should set event listeners on subpanel:resetCollection:rlis345', function() {
                expect(view.context.parent.on).toHaveBeenCalledWith('subpanel:resetCollection:rlis345');
            });

            it('should call resetSubpanel', function() {
                expect(view.resetSubpanel).toHaveBeenCalled();
            });
        });
    });

    describe('onAddFromProductCatalog', function() {
        var pcData;

        beforeEach(function() {
            sandbox.stub(view, '_addBeanToList').callsFake(function(p1, data) {
                view.collection.add(data);
            });
            pcData = {
                discount_price: '50.00',
                name: 'test1'
            };
            app.user.set({
                id: 'test1id',
                full_name: 'test1name'
            });
        });

        afterEach(function() {
            pcData = null;
        });

        it('should set discount_price from data to new bean likely/best/worst case', function() {
            view.onAddFromProductCatalog(pcData);

            expect(view._addBeanToList).toHaveBeenCalledWith(true, {
                discount_price: '50.00',
                name: 'test1',
                likely_case: '50.00',
                best_case: '50.00',
                worst_case: '50.00',
                assigned_user_id: 'test1id',
                assigned_user_name: 'test1name'
            });
        });

        it('should remove an existing empty model and add the new one', function() {
            var emptyModel = app.data.createBean('Products');
            view.collection.reset([emptyModel]);
            view.onAddFromProductCatalog(pcData);

            expect(view.collection.length).toBe(1);
        });
    });

    describe('resetSubpanel()', function() {
        beforeEach(function() {
            sandbox.stub(view.collection, 'reset');
            sandbox.stub(view, '_addBeanToList');
        });

        it('should call reset on the collection', function() {
            view.resetSubpanel();
            expect(view.collection.reset).toHaveBeenCalled();
        });

        it('should not call _addBeanToList with true if the subpanel is allowed to be empty', function() {
            view.context.set('allowEmpty', true);
            view.resetSubpanel();
            expect(view._addBeanToList).not.toHaveBeenCalled();
        });

        it('should call _addBeanToList with true if the subpanel is not allowed to be empty', function() {
            view.resetSubpanel();
            expect(view._addBeanToList).toHaveBeenCalledWith(true);
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sandbox.stub(view, '_super').callsFake(function() {});
            sandbox.stub(view, '_toggleEdit').callsFake(function() {});
        });

        it('should call toggleEdit', function() {
            view.render();
            expect(view._toggleEdit).toHaveBeenCalledWith(true);
        });
    });

    describe('_addBeanToList', function() {
        var existingData;
        var pcData;

        beforeEach(function() {
            existingData = {
                discount_price: '50.00',
                name: 'test1',
                likely_case: '50.00',
                best_case: '50.00',
                worst_case: '50.00',
                assigned_user_id: 'test1id',
                assigned_user_name: 'test1name'
            };
            pcData = {
                discount_price: '150.00',
                name: 'test2',
                likely_case: '150.00',
                best_case: '150.00',
                worst_case: '150.00',
                assigned_user_id: 'test2id',
                assigned_user_name: 'test2name'
            };
            view.collection.reset([existingData]);
            sandbox.stub(view, 'checkButtons').callsFake($.noop);
        });

        afterEach(function() {
            pcData = null;
        });

        describe('when hasValidModels is false', function() {
            it('should only call checkButtons', function() {
                view._addBeanToList(false, undefined);

                expect(view.checkButtons).toHaveBeenCalled();
            });
        });

        describe('when hasValidModels is true', function() {
            beforeEach(function() {
                sandbox.stub(view, '_addCustomFieldsToBean').callsFake(function(bean) {
                    return bean;
                });
            });

            describe('when prepopulateData is not sent', function() {
                beforeEach(function() {
                    view._addBeanToList(true, undefined);
                });

                it('should add the bean to the end of the collection', function() {
                    expect(view.collection.at(0).get('name')).toBe('test1');
                    expect(view.collection.at(1).get('id')).toBe('testUUID');
                    expect(view.collection.at(1).get('name')).toBeUndefined();
                });
            });

            describe('when prepopulateData is sent', function() {
                beforeEach(function() {
                    view._addBeanToList(true, pcData);
                });

                it('should add the bean to the beginning of the collection', function() {
                    expect(view.collection.at(0).get('name')).toBe('test2');
                    expect(view.collection.at(1).get('name')).toBe('test1');
                });
            });
        });
    });
});

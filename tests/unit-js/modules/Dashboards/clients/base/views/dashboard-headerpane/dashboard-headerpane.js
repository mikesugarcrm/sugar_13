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

describe('Dashboards.Base.View.DashboardHeaderpane', function() {
    var app;
    var view;
    var sandbox = sinon.createSandbox();

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'dashboard-headerpane-main');
        SugarTest.loadComponent('base', 'view', 'dashboard-headerpane', 'Dashboards');

        app.routing.start();
    });

    afterEach(function() {
        sandbox.restore();
        app.router.stop();

        app.cache.cutAll();
        app.view.reset();

        view.layout = null;
        view.context = null;
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        it('should create a RHS dashboard and enter edit mode', function() {
            var context = new app.Context({
                model: app.data.createBean('Dashboards'),
                create: true
            });
            context.parent = new app.Context({
                module: 'Accounts'
            });
            sandbox.stub(app.metadata, 'getView')
                .withArgs('Accounts', 'dashboard-headerpane', 'Dashboards')
                .returns('dashboard metadata');
            sandbox.stub(app.template, 'getView').withArgs('dashboard-headerpane')
                .returns($.noop);

            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane', null, context, true);

            expect(view.changed).toBeTruthy();
            expect(view.action).toEqual('edit');
            expect(view.inlineEditMode).toBeTruthy();
        });
    });

    describe('_getMouseTargetFields', function() {
        it('should return a list of fields', function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            var event = {target: 'target'};
            var target = {
                parents: function() {
                    return {
                        find: function() {
                            return 'fields';
                        }
                    };
                }
            };
            sandbox.stub(view, '$').returns(target);
            expect(view._getMouseTargetFields(event)).toEqual('fields');
        });
    });

    describe('handleMouseMove', function() {
        var tooltipStub;

        beforeEach(function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            sandbox.stub(view, '_getMouseTargetFields').returns([{
                getBoundingClientRect: function() {
                    return {
                        left: 10,
                        top: 20,
                        width: 10,
                        height: 10
                    };
                }
            }]);
            tooltipStub = sandbox.stub();
            sandbox.stub(window, '$').returns({
                tooltip: tooltipStub
            });
        });

        it('should show tooltip', function() {
            sandbox.stub(view, '_isTooltipOn').returns(false);
            sandbox.stub(view, '_isEllipsisOn').returns(true);
            var event = {clientX: 15, clientY: 25};
            view.handleMouseMove(event);
            expect(tooltipStub).toHaveBeenCalledWith('show');
        });

        it('should not do anything if tooltip is already on', function() {
            sandbox.stub(view, '_isTooltipOn').returns(true);
            var event = {clientX: 15, clientY: 25};
            view.handleMouseMove(event);
            expect(tooltipStub).not.toHaveBeenCalled();
        });

        it('should not do anything if ellipsis is not on', function() {
            sandbox.stub(view, '_isTooltipOn').returns(false);
            sandbox.stub(view, '_isEllipsisOn').returns(false);
            var event = {clientX: 15, clientY: 25};
            view.handleMouseMove(event);
            expect(tooltipStub).not.toHaveBeenCalled();
        });

        it('should hide tooltip', function() {
            sandbox.stub(view, '_isTooltipOn').returns(true);
            var event = {clientX: 25, clientY: 35};
            view.handleMouseMove(event);
            expect(tooltipStub).toHaveBeenCalledWith('hide');
        });

        it('should not do anything', function() {
            sandbox.stub(view, '_isTooltipOn').returns(false);
            var event = {clientX: 25, clientY: 35};
            view.handleMouseMove(event);
            expect(tooltipStub).not.toHaveBeenCalled();
        });
    });

    describe('handleMouseLeave', function() {
        var tooltipStub;

        beforeEach(function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            sandbox.stub(view, '_getMouseTargetFields').returns([{}]);
            tooltipStub = sandbox.stub();
            sandbox.stub(window, '$').returns({
                tooltip: tooltipStub
            });
        });

        it('should hide tooltip', function() {
            sandbox.stub(view, '_isTooltipOn').returns(true);
            view.handleMouseLeave({});
            expect(tooltipStub).toHaveBeenCalledWith('hide');
        });

        it('should not do anything', function() {
            sandbox.stub(view, '_isTooltipOn').returns(false);
            view.handleMouseLeave({});
            expect(tooltipStub).not.toHaveBeenCalled();
        });
    });

    describe('toggleNameField', function() {
        it('should call toggleField', function() {
            var field = 'test';
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            sandbox.stub(view, 'getField').returns(field);
            view.toggleField = sandbox.stub();

            view.toggleNameField(true);
            expect(view.toggleField).toHaveBeenCalledWith(field, true);
        });
    });

    describe('saveHandle', function() {
        it('should call handleSave', function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');

            sandbox.stub(view.model, 'changedAttributes').returns({'name': true});
            view.layout = {
                handleSave: sinon.stub(),
            };
            view.setButtonStates = sandbox.stub();
            view.toggleEdit = sandbox.stub();

            view.saveHandle();
            expect(view.layout.handleSave).toHaveBeenCalled();
        });
    });

    describe('editOverviewTabClicked', function() {
        it('should switch tab and call editClicked', function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            view.context = {
                trigger: sandbox.stub(),
                get: function() {
                    return 1;
                }
            };
            var editClickedStub = sandbox.stub(view, 'editClicked');
            view.editOverviewTabClicked();
            expect(view.context.trigger).toHaveBeenCalledWith('tabbed-dashboard:switch-tab', 0);
            expect(editClickedStub).toHaveBeenCalled();
        });
    });

    describe('hasUnsavedChanges', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
        });

        it('should return false if the model has no change', function() {
            sandbox.stub(view.model, 'save').callsFake(function(attrs) {
                _.extend(view.model.changed, attrs);
            });

            // new model that has no change
            expect(view.hasUnsavedChanges()).toBeFalsy();

            view.model.set('id', 'model_id');

            // no change to existing model
            expect(view.hasUnsavedChanges()).toBeFalsy();

            // the only change to an existing model is my_favorite
            view.model.favorite(true);
            view.model.setSyncedAttributes({my_favorite: true});

            expect(view.hasUnsavedChanges()).toBeFalsy();
        });

        it('should return false if the legacyComponent is removed from the metadata', function() {
            sandbox.stub(view.model, 'save').callsFake(function(attrs) {
                _.extend(view.model.changed, attrs);
            });

            view.model.unset('updated');

            sandbox.stub(view.model, 'isNew').returns(false);
            sandbox.stub(view.model, 'getSynced').returns({
                tabs: {},
                css_class: 'test'
            });

            // new model that has change
            view.model.set('metadata', {
                tabs: {},
                cass_class: 'test',
                legacyComponents: {}
            });

            sandbox.stub(view.model, 'changedAttributes')
                .returns(
                    {
                        metadata: {
                            tabs: {},
                            cass_class: 'test'
                        }
                    });

            expect(view.hasUnsavedChanges()).toBeFalsy();
        });

        it('should return true if the model has been changed', function() {
            // model that is updated
            view.model.set('updated', true);

            expect(view.hasUnsavedChanges()).toBeTruthy();
            view.model.unset('updated');

            // new model that has change
            view.model.set('name', 'new model');

            expect(view.hasUnsavedChanges()).toBeTruthy();
            view.model.unset('name');

            // existing model that is modified
            view.model.set({
                updated: false,
                id: 'model_id',
                name: 'old model'
            });
            sandbox.stub(view.model, 'changedAttributes').returns({name: 'new model'});

            expect(view.hasUnsavedChanges()).toBeTruthy();
        });
    });

    describe('_isDashboard', function() {
        var context;

        beforeEach(function() {
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{view: 'multi-line-list'}]};
            context = app.context.getContext();
            context.set('tabs', [tab0, tab1]);
            context.prepare();
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane', null, context, true);
        });

        it('should return true for dashboard tab', function() {
            context.set('activeTab', 0);
            expect(view._isDashboard()).toBeTruthy();
        });

        it('should return false for non-dashboard tab', function() {
            context.set('activeTab', 1);
            expect(view._isDashboard()).toBeFalsy();
        });
    });

    describe('_enableEditButton', function() {
        var button;
        var setDisabledStub;

        beforeEach(function() {
            SugarTest.loadComponent('base', 'field', 'button');
            button = SugarTest.createField('base', 'button', 'button', 'edit');
            button.name = 'edit_button';
            setDisabledStub = sandbox.stub(button, 'setDisabled');
            view = SugarTest.createView('base', 'Dashboards', 'dashboard-headerpane');
            view.buttons = [{
                type: 'actiondropdown',
                fields: [button],
                _orderButtons: $.noop,
                render: $.noop
            }];
        });

        afterEach(function() {
            button.dispose();
            button = null;
        });

        it('should disable edit button', function() {
            view._enableEditButton(false);
            expect(setDisabledStub).toHaveBeenCalledWith(true);
        });

        it('should enable edit button', function() {
            view._enableEditButton(true);
            expect(setDisabledStub).toHaveBeenCalledWith(false);
        });
    });
});

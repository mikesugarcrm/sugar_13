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
describe('Base.View.DashletToolbar', function() {
    var app;
    var dashboard;
    var layout;
    var moduleName = 'Accounts';
    var view;
    var viewName = 'dashlet-toolbar';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'view', viewName);

        var context = new app.Context({
            module: moduleName,
            layout: 'dashlet',
        });
        context.parent = app.context.getContext({
            module: 'Accounts',
            layout: 'record',
        });
        context.prepare();
        context.parent.prepare();

        dashboard = app.view.createLayout({
            type: 'dashboard',
            name: 'dashboard',
        });

        layout = app.view.createLayout({
            type: 'dashlet',
            context: context,
            meta: {index: 0},
            layout: dashboard,
        });

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            {
                buttons: [
                    {
                        type: 'dashletaction',
                        action: 'toggleminify'
                    },
                    {
                        dropdownbuttons: [
                            {
                                type: 'dashletaction',
                                action: 'editClicked',
                                label: 'LBL_DASHLET_CONFIG_EDIT_LABEL',
                                name: 'edit_button',
                            }
                        ]
                    }
                ]
            },
            context,
            false,
            layout
        );
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        dashboard.dispose();
    });

    describe('bindDataChange', function() {
        it('should set the header fields, buttons, and model on dashlet:toolbar:change', function() {
            var renderStub = sinon.stub(view, 'render');
            var account = app.data.createBean(moduleName);

            view.bindDataChange();
            view.context.trigger(
                'dashlet:toolbar:change',
                [{name: 'myfield'}],
                [
                    {
                        name: 'a new button',
                        type: 'button'
                    }
                ],
                account
            );

            expect(renderStub).toHaveBeenCalled();
            expect(view.headerFields).toEqual([{name: 'myfield'}]);
            expect(view.buttons).toEqual([
                {
                    name: 'a new button',
                    type: 'button'
                },
                {
                    type: 'dashletaction',
                    action: 'toggleminify'
                },
                {
                    dropdownbuttons: [
                        {
                            type: 'dashletaction',
                            action: 'editClicked',
                            label: 'LBL_DASHLET_CONFIG_EDIT_LABEL',
                            name: 'edit_button',
                        }
                    ]
                }
            ]);
            expect(view.dashletModel).toBe(account);
        });
    });

    describe('editClicked', function() {
        it('should defer to the layout', function() {
            var layoutEditDashletStub = sinon.stub(layout, 'editDashlet');
            view.editClicked();
            expect(layoutEditDashletStub).toHaveBeenCalled();
        });
    });

    describe('adjustHeaderPaneTitle', function() {
        it('should resize dashlet title when buttons are toggled', function() {
            var adjustHeaderPaneTitle = sinon.stub(view, 'adjustHeaderPaneTitle');
            view.toggleButtons();
            expect(adjustHeaderPaneTitle).toHaveBeenCalled();
        });
    });
});

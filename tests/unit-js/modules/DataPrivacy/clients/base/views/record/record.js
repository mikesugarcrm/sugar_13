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
describe('DataPrivacy.Views.Record', function() {
    var app;
    var context;
    var view;
    var sandbox;

    beforeEach(function() {
        var viewName = 'record';
        var moduleName = 'DataPrivacy';

        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        context = app.context.getContext({module: moduleName});
        model = app.data.createBean('DataPrivacy');
        context.set('model', model);

        var meta = {
            panels: [
                {
                    fields: [
                        {
                            name: 'type'
                        }
                    ]
                }
            ]
        };

        view = SugarTest.createView('base', moduleName, viewName, meta, context, true);
        sandbox = sinon.createSandbox();

        view.buttons = [{
            type: 'button',
            name: 'cancel_button',
            label: 'LBL_CANCEL_BUTTON_LABEL',
            def: {
                type: 'button',
                name: 'cancel_button',
                label: 'LBL_CANCEL_BUTTON_LABEL',
                showOn: 'edit'
            },
            show: sandbox.spy(),
            hide: sandbox.spy(),
            setDisabled: sandbox.spy()
        }, {
            type: 'rowaction',
            name: 'save_button',
            label: 'LBL_SAVE_BUTTON_LABEL',
            def: {
                type: 'rowaction',
                name: 'save_button',
                label: 'LBL_SAVE_BUTTON_LABEL',
                showOn: 'edit',
                acl_action: 'edit'
            },
            show: sandbox.spy(),
            hide: sandbox.spy(),
            setDisabled: sandbox.spy()
        }, {
            type: 'rowaction',
            name: 'complete_button',
            label: 'LBL_COMPLETE_BUTTON_LABEL',
            def: {
                type: 'rowaction',
                name: 'complete_button',
                label: 'LBL_COMPLETE_BUTTON_LABEL',
                showOn: 'view',
                acl_action: 'admin'
            },
            show: sandbox.spy(),
            hide: sandbox.spy(),
            setDisabled: sandbox.spy()
        }, {
            type: 'rowaction',
            name: 'erase_complete_button',
            label: 'LBL_ERASE_COMPLETE_BUTTON_LABEL',
            def: {
                type: 'rowaction',
                name: 'erase_complete_button',
                label: 'LBL_ERASE_COMPLETE_BUTTON_LABEL',
                showOn: 'view',
                acl_action: 'admin'
            },
            show: sandbox.spy(),
            hide: sandbox.spy(),
            setDisabled: sandbox.spy()
        }, {
            type: 'rowaction',
            name: 'reject_button',
            label: 'LBL_REJECT_BUTTON_LABEL',
            def: {
                type: 'rowaction',
                name: 'reject_button',
                label: 'LBL_REJECT_BUTTON_LABEL',
                showOn: 'view',
                acl_action: 'admin'
            },
            show: sandbox.spy(),
            hide: sandbox.spy(),
            setDisabled: sandbox.spy()
        }, {
            type: 'actiondropdown',
            name: 'main_dropdown',
            def: {
                primary: true,
                showOn: 'view',
            },
            fields: [
                {
                    type: 'rowaction',
                    name: 'edit_button',
                    label: 'LBL_EDIT_BUTTON_LABEL',
                    def: {
                        showOn: 'edit',
                        acl_action: 'edit'
                    },
                    show: sandbox.spy(),
                    hide: sandbox.spy(),
                    setDisabled: sandbox.spy()
                }, {
                    type: 'shareaction',
                    name: 'share',
                    label: 'LBL_RECORD_SHARE_BUTTON',
                    def: {
                        showOn: 'view',
                        acl_action: 'view'
                    },
                    show: sandbox.spy(),
                    hide: sandbox.spy(),
                    setDisabled: sandbox.spy()
                }, {
                    type: 'rowaction',
                    name: 'duplicate_button',
                    label: 'LBL_DUPLICATE_BUTTON_LABEL',
                    def: {
                        showOn: 'create',
                        acl_action: 'create'
                    },
                    show: sandbox.spy(),
                    hide: sandbox.spy(),
                    setDisabled: sandbox.spy()
                }
            ],
            show: sandbox.spy(),
            hide: sandbox.spy(),
            setDisabled: sandbox.spy()
        }];
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('call setButtons() with erase type', function() {
        it('should show Erase & Complete and Reject button but hide Complete button', function() {
            view.model.set('status', 'Open');
            view.model.set('type', 'Request to Erase Information');
            view.setButtons(view.STATE.VIEW);

            // erase_complete_button should be shown
            expect(view.buttons[3].show).toHaveBeenCalled();
            // complete_button should be hidden
            expect(view.buttons[2].hide).toHaveBeenCalled();
            // reject_button should be shown
            expect(view.buttons[4].show).toHaveBeenCalled();
        });
    });
    describe('call setButtons() with non-erase type', function() {
        it('should show Complete and Reject buttons but hide Erase & Complete button', function() {
            view.model.set('status', 'Open');
            view.model.set('type', 'Request for Data Privacy Policy');
            view.setButtons(view.STATE.VIEW);

            // complete_button should be shown
            expect(view.buttons[2].show).toHaveBeenCalled();
            // erase_complete_button should be hidden
            expect(view.buttons[3].hide).toHaveBeenCalled();
            // reject_button should be shown
            expect(view.buttons[4].show).toHaveBeenCalled();
        });
    });
    describe('call setButtons() with non-Open status', function() {
        it('should hide Complete, Erase & Complete and reject buttons', function() {
            view.model.set('status', 'Rejected');
            view.setButtons(view.STATE.VIEW);

            // complete_button should be hidden
            expect(view.buttons[2].hide).toHaveBeenCalled();
            // erase_complete_button should be hidden
            expect(view.buttons[3].hide).toHaveBeenCalled();
            // reject_button should be hidden
            expect(view.buttons[4].hide).toHaveBeenCalled();
        });
    });
});

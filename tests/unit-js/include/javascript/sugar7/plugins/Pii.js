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
describe('Plugins.Pii', function() {
    var app;
    var view;
    var stub;
    var plugin;
    var component;
    var setModelFields;
    var getMetadata = function() {
        return {
            buttons: [
                {
                    type: 'button',
                    name: 'cancel_button',
                    label: 'LBL_CANCEL_BUTTON_LABEL',
                    css_class: 'btn-invisible btn-link',
                    showOn: 'edit'
                },
                {
                    type: 'actiondropdown',
                    name: 'main_dropdown',
                    buttons: [
                        {
                            type: 'rowaction',
                            event: 'button:edit_button:click',
                            name: 'edit_button',
                            label: 'LBL_EDIT_BUTTON_LABEL',
                            primary: true,
                            showOn: 'view',
                            acl_action: 'edit'
                        },
                        {
                            type: 'rowaction',
                            event: 'button:save_button:click',
                            name: 'save_button',
                            label: 'LBL_SAVE_BUTTON_LABEL',
                            primary: true,
                            showOn: 'edit',
                            acl_action: 'edit'
                        },
                        {
                            type: 'rowaction',
                            name: 'delete_button',
                            label: 'LBL_DELETE_BUTTON_LABEL',
                            showOn: 'view',
                            acl_action: 'delete'
                        },
                        {
                            type: 'rowaction',
                            name: 'duplicate_button',
                            label: 'LBL_DUPLICATE_BUTTON_LABEL',
                            showOn: 'view',
                            acl_module: 'Accounts'
                        }
                    ]
                }
            ]
        };
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadPlugin('Pii');
        plugin = app.plugins.plugins.view.Pii;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('record', getMetadata(), 'Accounts');
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'view', 'record');
        view = SugarTest.createView('base', 'Accounts', 'record');

        setModelFields = function(addPii) {
            view.model.fields = {
                name: {name: 'name'},
                rating: {name: 'rating'},
                tasks: {name: 'tasks', type: 'link'}
            };

            if (addPii) {
                view.model.fields.name.pii = true;
            }
        };

        app.drawer = {
            open: sinon.stub()
        };
    });

    afterEach(function() {
        sinon.restore();
        if (component) {
            component.dispose();
            component = null;
        }
        sinon.restore();
        app.cache.cutAll();
        delete app.drawer;
        app = null;
    });

    describe('plugin', function() {
        it('should attach init event handler', function() {
            stub = sinon.stub(view, 'on');
            plugin.onAttach.apply(view);
            expect(stub).toHaveBeenCalledWith('init');
        });

        it('should call the insert pii button', function() {
            stub = sinon.stub(view, '_insertViewPiiButton');
            view.trigger('init');
            expect(stub).toHaveBeenCalled();
        });

        it('should not add view pii button meta if there are no PII fields', function() {
            setModelFields();
            view.trigger('init');
            expect(view.meta.buttons[1].buttons.length).toEqual(4);
        });

        it('should add view pii button at the last position if audit button is not present', function() {
            setModelFields(true);
            expect(view.meta.buttons[1].buttons.length).toEqual(4);
            view.trigger('init');
            expect(view.meta.buttons[1].buttons.length).toEqual(5);
            expect(view.meta.buttons[1].buttons[4].name).toEqual('view_pii_button');
        });

        it('should add view pii button after audit button', function() {
            setModelFields(true);
            view.meta.buttons[1].buttons[0].name = 'audit_button';

            expect(view.meta.buttons[1].buttons.length).toEqual(4);
            view.trigger('init');
            expect(view.meta.buttons[1].buttons.length).toEqual(5);
            expect(view.meta.buttons[1].buttons[1].name).toEqual('view_pii_button');
        });

        describe('viewPiiClicked', function() {
            it('should open the View PII drawer', function() {
                setModelFields(true);
                view.trigger('init');
                var childContext = new app.Context();
                sinon.stub(view.context, 'getChildContext').returns(childContext);
                view.viewPiiClicked();
                expect(app.drawer.open).toHaveBeenCalledWith({
                    layout: 'pii',
                    context: childContext
                });
            });
        });
    });
});

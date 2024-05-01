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
describe('Emails.Views.Record', function() {
    var app;
    var context;
    var view;
    var sandbox;

    beforeEach(function() {
        var viewName = 'record';
        var moduleName = 'Emails';
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();
        app.drawer = {on: $.noop, off: $.noop, getHeight: $.noop, close: $.noop, reset: $.noop, count: $.noop};

        context = app.context.getContext({module: moduleName});
        context.prepare(true);

        var meta = {
            panels: [
                {
                    fields: [
                        {
                            name: 'recipients',
                            fields: [
                                {name: 'to_collection'},
                                {name: 'cc_collection'},
                                {name: 'bcc_collection'}
                            ]
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
            type: 'actiondropdown',
            name: 'main_dropdown',
            def: {
                primary: true,
                showOn: 'view'
            },
            fields: [
                {
                    name: 'reply_button',
                    type: 'reply-action',
                    label: 'LBL_BUTTON_REPLY',
                    def: {
                        acl_module: 'Emails',
                        acl_action: 'create'
                    },
                    show: sandbox.spy(),
                    hide: sandbox.spy(),
                    setDisabled: sandbox.spy()
                }, {
                    name: 'reply_all_button',
                    type: 'reply-action',
                    label: 'LBL_BUTTON_REPLY_ALL',
                    def: {
                        acl_module: 'Emails',
                        acl_action: 'create',
                        reply_all: true
                    },
                    show: sandbox.spy(),
                    hide: sandbox.spy(),
                    setDisabled: sandbox.spy()
                }, {
                    name: 'forward_button',
                    type: 'forward-action',
                    label: 'LBL_BUTTON_FORWARD',
                    def: {
                        acl_module: 'Emails',
                        acl_action: 'create'
                    },
                    show: sandbox.spy(),
                    hide: sandbox.spy(),
                    setDisabled: sandbox.spy()
                }, {
                    type: 'rowaction',
                    name: 'edit_button',
                    label: 'LBL_EDIT_BUTTON_LABEL',
                    def: {
                        acl_action: 'edit'
                    },
                    show: sandbox.spy(),
                    hide: sandbox.spy(),
                    setDisabled: sandbox.spy()
                }, {
                    name: 'delete_button',
                    type: 'rowaction',
                    label: 'LBL_DELETE_BUTTON',
                    def: {
                        acl_action: 'delete'
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

    describe('Delete Confirmation', function() {
        it('should display (no subject) as the record name on delete confirmation', function() {
            var name = view._getNameForMessage(view.model);
            expect(name).toBe('LBL_NO_SUBJECT');
        });

        it('should display the record name when not empty on delete confirmation', function() {
            var recordName = 'Test Record';

            view.model.set('name', recordName);
            var name = view._getNameForMessage(view.model);
            expect(name).toBe(recordName);
        });
    });

    describe('alert the user that the email is a draft', function() {
        beforeEach(function() {
            sandbox.stub(app.alert, 'show');
        });

        it('should alert the user when state changes to draft on the model', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);
            view.model.set('state', view.STATE_DRAFT);
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should alert the user when the model is synced and state becomes draft', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);
            sandbox.stub(view.model, 'sync').callsFake(function(method, model, options) {
                options.success({state: view.STATE_DRAFT});
            });
            view.model.save();
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should alert the user when the model starts with state equal to draft', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);
            context.get('model').set('state', view.STATE_DRAFT);
            view = SugarTest.createView('base', 'Emails', 'record', null, context, true);

            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should not alert the user if the user cannot edit the draft', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(false);
            view.model.set('state', view.STATE_DRAFT);
            expect(app.alert.show).not.toHaveBeenCalled();
        });
    });

    describe('loading all recipients', function() {
        it('should toggle action buttons while loading all recipients', function() {
            sandbox.spy(view, 'toggleButtons');

            view.trigger('loading_collection_field', 'to_collection');
            view.trigger('loading_collection_field', 'cc_collection');
            view.trigger('loading_collection_field', 'bcc_collection');

            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.alwaysCalledWithExactly(false)).toBe(true);

            view.trigger('loaded_collection_field', 'to_collection');
            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.neverCalledWith(true)).toBe(true);

            view.trigger('loaded_collection_field', 'cc_collection');
            expect(view.toggleButtons).toHaveBeenCalledThrice();
            expect(view.toggleButtons.neverCalledWith(true)).toBe(true);

            view.trigger('loaded_collection_field', 'bcc_collection');
            expect(view.toggleButtons.callCount).toBe(4);
            expect(view.toggleButtons.lastCall.args[0]).toBe(true);
        });

        describe('Render each recipient field when it has changed', function() {
            using(
                'recipient fields',
                [
                    'from_collection',
                    'to_collection',
                    'cc_collection',
                    'bcc_collection'
                ],
                function(fieldName) {
                    it('should render the field', function() {
                        var field = {
                            render: sandbox.spy()
                        };
                        sandbox.stub(view, 'getField').withArgs(fieldName).returns(field);
                        view.model.trigger('change:' + fieldName);
                        expect(field.render).toHaveBeenCalled();
                    });
                }
            );
        });
    });

    describe('saving an email', function() {
        it('should set the view parameter to the name of the view', function() {
            var options = view.getCustomSaveOptions({});
            expect(options.params.view).toBe(view.name);
        });
    });

    describe('the email is archived', function() {
        beforeEach(function() {
            view.model.set({
                id: _.uniqueId(),
                state: 'Archived'
            }, {
                silent: true
            });
        });

        describe('clicking the edit button', function() {
            beforeEach(function() {
                sandbox.stub(app.router, 'navigate');
            });

            using('access', [true, false], function(access) {
                it('should not navigate to the compose route', function() {
                    sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(access);

                    view.editClicked();

                    expect(app.router.navigate).not.toHaveBeenCalledWith(
                        '#Emails/' + view.model.get('id') + '/compose',
                        {trigger: true}
                    );
                    expect(app.router.navigate).toHaveBeenCalledWith('Emails/' + view.model.get('id') + '/edit');
                });
            });
        });

        it('should show the Forward, Reply, and Reply All buttons', function() {
            view.setButtonStates(view.STATE.VIEW);

            // cancel_button should be hidden
            expect(view.buttons[0].show).not.toHaveBeenCalled();
            expect(view.buttons[0].hide).toHaveBeenCalled();

            // save_button should be hidden
            expect(view.buttons[1].show).not.toHaveBeenCalled();
            expect(view.buttons[1].hide).toHaveBeenCalled();

            // main_dropdown should be shown
            expect(view.buttons[2].show).toHaveBeenCalled();
            expect(view.buttons[2].hide).not.toHaveBeenCalled();

            // reply_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[0].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[0].hide).not.toHaveBeenCalled();

            // reply_all_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[1].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[1].hide).not.toHaveBeenCalled();

            // forward_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[2].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[2].hide).not.toHaveBeenCalled();

            // edit_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[3].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[3].hide).not.toHaveBeenCalled();

            // delete_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[4].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[4].hide).not.toHaveBeenCalled();
        });
    });

    describe('the email is a draft', function() {
        beforeEach(function() {
            view.model.set({
                id: _.uniqueId(),
                state: view.STATE_DRAFT
            }, {
                silent: true
            });
        });

        describe('clicking the edit button', function() {
            beforeEach(function() {
                sandbox.stub(app.router, 'navigate');
            });

            it('should navigate to the compose route', function() {
                sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);

                view.editClicked();

                expect(app.router.navigate).toHaveBeenCalledWith(
                    '#Emails/' + view.model.get('id') + '/compose',
                    {trigger: true}
                );
                expect(app.router.navigate).not.toHaveBeenCalledWith('Emails/' + view.model.get('id') + '/edit');
            });

            it('should not navigate to the compose route', function() {
                sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(false);

                view.editClicked();

                expect(app.router.navigate).not.toHaveBeenCalledWith(
                    '#Emails/' + view.model.get('id') + '/compose',
                    {trigger: true}
                );
                expect(app.router.navigate).toHaveBeenCalledWith('Emails/' + view.model.get('id') + '/edit');
            });
        });

        it('should hide the Forward, Reply, and Reply All buttons', function() {
            view.setButtonStates(view.STATE.VIEW);

            // cancel_button should be hidden
            expect(view.buttons[0].show).not.toHaveBeenCalled();
            expect(view.buttons[0].hide).toHaveBeenCalled();

            // save_button should be hidden
            expect(view.buttons[1].show).not.toHaveBeenCalled();
            expect(view.buttons[1].hide).toHaveBeenCalled();

            // main_dropdown should be shown
            expect(view.buttons[2].show).toHaveBeenCalled();
            expect(view.buttons[2].hide).not.toHaveBeenCalled();

            // reply_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[0].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[0].hide).toHaveBeenCalled();

            // reply_all_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[1].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[1].hide).toHaveBeenCalled();

            // forward_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[2].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[2].hide).toHaveBeenCalled();

            // edit_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[3].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[3].hide).not.toHaveBeenCalled();

            // delete_button should be shown
            // show isn't called because it's visibility is controlled by
            // hiding or showing main_dropdown
            expect(view.buttons[2].fields[4].show).not.toHaveBeenCalled();
            expect(view.buttons[2].fields[4].hide).not.toHaveBeenCalled();
        });
    });
});

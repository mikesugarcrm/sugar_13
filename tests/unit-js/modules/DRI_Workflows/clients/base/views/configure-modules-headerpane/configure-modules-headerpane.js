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
describe('DRI_Workflows.Base.View.ConfigureModulesHeaderpaneView', function() {
    let app;
    let view;
    let context;
    let initOptions;
    let layout;
    let con;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.app.data.declareModels();

        context = new app.Context();

        context.set('model', new Backbone.Model());

        context.prepare();
        context.parent = app.context.getContext();

        layout = SugarTest.createLayout(
            'base',
            'DRI_Workflows',
            'base',
            null,
            context
        );

        view = SugarTest.createView(
            'base',
            'DRI_Workflows',
            'configure-modules-headerpane',
            {},
            context,
            true,
            layout,
            true
        );

        initOptions = {
            context: context,
        };

        con = SugarTest.createView(
            'base',
            'DRI_Workflows',
            'configure-modules-content',
            {},
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        con.dispose();
        app = null;
        layout = null;
        context = null;
        view = null;
        con = null;
    });

    describe('initialize', () => {
        it('should set the title', () => {
            view.initialize(initOptions);
            expect(view.title).toBe('LBL_CONFIGURE_MODULES_CONTENT_TITLE');
        });
    });

    describe('saveConfig', () => {
        let congifureModule;
        beforeEach(function() {
            congifureModule = SugarTest.createView(
                'base',
                'DRI_Workflows',
                'configure-modules-content',
                {},
                context,
                true,
                layout,
                true
            );
        });
        afterEach(function() {
            congifureModule.dispose();
            congifureModule = null;
        });
        it('should save config', () => {
            sinon.stub(layout, 'getComponent').returns(congifureModule);
            sinon.stub(app.api, 'buildURL').returns(true);
            sinon.stub(app.api, 'call').returns(true);
            sinon.stub(view, 'disableEnableSaveButton').returns(true);

            view.saveConfig();
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(app.api.call).toHaveBeenCalled();
            expect(view.disableEnableSaveButton).toHaveBeenCalled();
        });
    });

    describe('updateAPISuccess', () => {
        it('should call render', () => {
            sinon.stub(app.alert, 'dismiss').returns(true);
            sinon.stub(app.alert, 'show').returns(true);
            sinon.stub(app.metadata, 'sync').returns(true);
            sinon.stub(con, 'render').returns(true);

            view.updateAPISuccess(con, true);
            expect(con.render).toHaveBeenCalled();
        });
    });

    describe('updateAPIError', () => {
        it('should call disableEnableSaveButton', () => {
            sinon.stub(view, 'disableEnableSaveButton').returns(true);
            sinon.stub(app.alert, 'show').returns(true);

            view.updateAPIError(con, true);
            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('updateAPIComplete', () => {
        it('should dismiss alert', () => {
            sinon.stub(app.alert, 'dismiss').returns(true);

            view.updateAPIComplete(con, true);
            expect(app.alert.dismiss).toHaveBeenCalled();
        });
    });

    describe('disableEnableSaveButton', () => {
        let button;
        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'save_button',
                type: 'button',
                viewName: 'detail',
                fieldDef: {
                    label: 'LBL_SAVE_BUTTON_LABEL'
                }
            });
        });
        afterEach(function() {
            button.dispose();
            button = null;
        });
        it('should toggle the save button', () => {
            SugarTest.loadComponent('base', 'field', 'button');

            sinon.stub(view, 'getField').returns(button);
            sinon.stub(button, 'setDisabled').returns(true);

            view.disableEnableSaveButton(true);
            expect(button.setDisabled).toHaveBeenCalled();
        });
    });
});

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
describe('DRI_Workflow View', function() {
    let app;
    let sinonSandbox;
    let view;
    let layout;
    let context;
    let viewName = 'dri-workflow';
    let moduleName = 'DRI_Workflows';
    let layoutName = 'record';

    beforeEach(function() {
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'layout', 'base');
        SugarTest.loadComponent('base', 'view', viewName);

        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            module: moduleName,
            layout: layoutName,
            parentModel: new Backbone.Model(),
        });
        context.prepare();

        layout = SugarTest.createLayout('base', moduleName, 'list', {}, context);
        view = SugarTest.createView('base', moduleName, viewName, {module: moduleName}, context, null, layout);

        sinonSandbox.stub(view, '_super');
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.app.view.reset();
        app.data.reset();
        layout.dispose();
        view.dispose();
        view = null;
        layout = null;
    });

    describe('linkExistingActivity', function() {
        it('should open drawer with correct data', function() {
            sinonSandbox.stub(view, 'getParentModel').returns({
                get: sinonSandbox.stub().returns('Test Name'),
                id: '001',
                module: 'Contacts',
            });

            let stage = {
                get: sinonSandbox.stub().returns(''),
                id: 0,
            };
            view.stages = [];

            app.drawer = {
                open: sinon.stub()
            };

            let module = 'Tasks';
            const filterOptions = new app.utils.FilterOptions()
                .config({
                    initial_filter: 'available_items',
                    initial_filter_label: 'Available ' + module,
                    filter_populate: {
                        'is_customer_journey_activity': {
                            $equals: 0,
                        },
                        'status': {
                            $not_in: module === 'Tasks' ? ['Not Applicable'] : ['Not Held'],
                        }
                    },
                }).format();

            view.linkExistingActivity(stage, module);
            expect(app.drawer.open.lastCall.args[0]).toEqual({
                layout: 'dri-link-existing-activity',
                context: {
                    module: module,
                    isMultiSelect: true,
                    filterOptions: filterOptions,
                    stageParent: stage,
                },
            });
        });
    });

    describe('setHorizontalScrollBarPosition', function() {
        it('Should check if Presentation mode is Horizontal', function() {
            sinon.stub(view, 'getPresentationMode').returns('H');
            view.context.parent = {
                get: sinon.stub().returns('Accounts')
            };
            sinon.stub(app.CJBaseHelper, 'getValueFromCache').returns('active');
            view.layout.getComponent = sinon.stub().returns(view);
            view.setHorizontalScrollBarPosition();
            expect(view.getPresentationMode).toHaveBeenCalled();
        });
    });
});

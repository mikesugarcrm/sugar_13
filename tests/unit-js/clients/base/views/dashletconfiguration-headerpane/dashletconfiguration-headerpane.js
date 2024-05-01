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
describe('BaseDashletconfigurationHeaderpaneView', function() {
    var app;
    var moduleName = 'Accounts';
    var oldDrawer;
    var layout;
    var layoutName = 'dashletconfiguration';
    var view;
    var viewName = 'dashletconfiguration-headerpane';

    beforeEach(function() {
        app = SugarTest.app;
        oldDrawer = app.drawer;

        SugarTest.loadComponent('base', 'layout', layoutName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addLayoutDefinition(
            layoutName,
            {
                components: [
                    {
                        layout: {
                            type: 'default',
                            name: 'sidebar',
                            components: [
                                {
                                    layout: {
                                        type: 'base',
                                        name: 'main-pane',
                                        css_class: 'main-pane span8',
                                        components: [
                                            {
                                                view: 'dashletconfiguration-headerpane'
                                            }
                                        ]
                                    }
                                }
                            ]
                        }
                    }
                ]
            }
        );
        SugarTest.testMetadata.set();

        layout = app.view.createLayout({
            name: layoutName,
            type: layoutName,
            module: moduleName
        });

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            {module: moduleName},
            null,
            false,
            layout
        );
    });

    afterEach(function() {
        sinon.restore();
        if (oldDrawer) {
            app.drawer = oldDrawer;
        }
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('plugins', function() {
        it('should have the Editable and ErrorDecoration plugins', function() {
            expect(view.plugins).toEqual(['Editable', 'ErrorDecoration']);
        });
    });

    describe('toggling the save button', function() {
        it('should toggle the save button on dashletconfig:save:toggle', function() {
            var setDisabledStub = sinon.stub();
            sinon.stub(view, 'getField')
                .withArgs('save_button')
                .returns({setDisabled: setDisabledStub});

            layout.trigger('dashletconfig:save:toggle', true);

            expect(setDisabledStub).toHaveBeenCalledWith(false);
        });
    });

    describe('close', function() {
        it('should close the drawer', function() {
            app.drawer = {close: sinon.stub()};

            view.close();

            expect(app.drawer.close).toHaveBeenCalled();
        });
    });
});

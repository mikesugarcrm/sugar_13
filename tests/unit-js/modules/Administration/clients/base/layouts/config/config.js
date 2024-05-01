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
describe('Administration.Layouts.Config', function() {
    var app;
    var context;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('category', 'test');
        context.set('module', 'Administration');
        SugarTest.loadComponent('base', 'layout', 'config', 'Administration');
        layout = SugarTest.createLayout('base', 'Administration', 'config', {}, context, true);
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
        context = null;
        app = null;
    });

    describe('_addComponentsFromDef', function() {
        it('should add header and view to layout', function() {
            var declareStub = sinon.stub(app.view, 'declareComponent');
            var superStub = sinon.stub(layout, '_super');

            var components = [
                {
                    layout: {
                        components: [
                            {
                                layout: {
                                    components: []
                                }
                            }
                        ]
                    }
                }
            ];

            layout._addComponentsFromDef(components);

            expect(declareStub).toHaveBeenCalled();
            expect(declareStub.args[0][1]).toEqual('test-config');
            expect(declareStub.args[1][1]).toEqual('test-config-header');
            expect(superStub).toHaveBeenCalled();
            expect(superStub.lastCall.args[1][0][0].layout.components[0].layout.components).toEqual([
                {
                    view: 'test-config'
                },
                {
                    view: 'test-config-header'
                }
            ]);
        });
    });
});

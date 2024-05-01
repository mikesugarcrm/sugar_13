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
describe('DataPrivacy Subpanel Layout', function() {
    var app;
    var context;
    var layout;
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        context = new app.Context();
        model = app.data.createBean();

        SugarTest.loadComponent('base', 'layout', 'subpanels');
        SugarTest.loadComponent('base', 'layout', 'subpanels', 'DataPrivacy');

        layout = app.view.createLayout({
            type: 'subpanels',
            context: context,
            module: 'DataPrivacy',
            meta: {},
            platform: 'base'
        });
        //Manually register the component for cleanup as using SugarTest.createComponent would call initComponents
        SugarTest.components.push(layout);
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
        delete app.drawer;
        app = null;
    });

    describe('initComponents', function() {
        it('should add the mark for erasure button to the subpanel-list view', function() {
            var viewMeta = {meta: {rowactions: {actions: []}}};
            var components = [
                {
                    meta: {components: [{'view': 'subpanel-list'}]},
                    viewName: 'subpanel-list',
                    viewMeta: app.utils.deepCopy(viewMeta),
                    isValidTarget: true
                },
                {
                    meta: {components: [{'view': 'subpanel-not-list'}]},
                    viewName: 'subpanel-list',
                    viewMeta: app.utils.deepCopy(viewMeta),
                    isValidTarget: false
                },
                {
                    meta: {components: [{'view': 'subpanel-for-foo'}]},
                    viewName: 'subpanel-for-foo',
                    viewMeta: app.utils.deepCopy(viewMeta),
                    isValidTarget: true
                },
                {
                    meta: {components: [{'view': {type: 'subpanel-for-bar'}}]},
                    viewName: 'subpanel-for-bar',
                    viewMeta: app.utils.deepCopy(viewMeta),
                    isValidTarget: true
                },
                {
                    meta: {'view': 'not-a-layout'},
                    viewMeta: app.utils.deepCopy(viewMeta),
                    isValidTarget: false
                }
            ];
            _.each(components, function(testData) {
                var m = sinon.mock();
                if (testData.isValidTarget) {
                    m.returns(testData.viewMeta);
                }
                if (testData.viewName) {
                    testData.getComponent = m.withArgs(testData.viewName);
                }
            });

            layout._super = function() {
                this._components = components;
            };

            layout.initComponents();

            for (var i in components) {
                if (components[i].getComponent) {
                    components[i].getComponent.verify();
                }
                if (components[i].isValidTarget) {
                    expect(components[i].viewMeta.meta.rowactions.actions[0].type).toEqual('dataprivacyerase');
                } else {
                    expect(components[i].viewMeta.meta.rowactions.actions).toEqual([]);
                }
            }

        });
    });
});

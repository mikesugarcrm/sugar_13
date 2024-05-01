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
describe('MoreLess Plugin', function () {
    var moduleName = 'Cases',
        app,
        viewName = 'record',
        view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            'panels': [
                {
                    'name': 'panel_header',
                    'header': true,
                    'fields': [{name: 'name', span: 8, labelSpan: 4}],
                    'labels': true
                },
                {
                    'name': 'panel_body',
                    'label': 'LBL_PANEL_2',
                    'columns': 1,
                    'labels': true,
                    'labelsOnTop': false,
                    'placeholders': true,
                    'fields': [
                        {name: 'description', type: 'base', label: 'description', span: 8, labelSpan: 4},
                        {name: 'case_number', type: 'float', label: 'case_number', span: 8, labelSpan: 4},
                        {name: 'type', type: 'text', label: 'type', span: 8, labelSpan: 4}
                    ]
                },
                {
                    'name': 'panel_hidden',
                    'hide': true,
                    'columns': 1,
                    'labelsOnTop': false,
                    'placeholders': true,
                    'fields': [
                        {name: 'created_by', type: 'date', label: 'created_by', span: 8, labelSpan: 4},
                        {name: 'date_entered', type: 'date', label: 'date_entered', span: 8, labelSpan: 4},
                        {name: 'date_modified', type: 'date', label: 'date_modified', span: 8, labelSpan: 4},
                        {name: 'modified_user_id', type: 'date', label: 'modified_user_id', span: 8, labelSpan: 4}
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        view = SugarTest.createView('base', moduleName, 'record', null, null);

        sinon.stub(view, '_buildGridsFromPanelsMetadata').callsFake(function(panels) {
            view.hiddenPanelExists = true;

            // The panel grid contains references to the actual fields found in panel.fields, so the fields must
            // be modified to include the field attributes that would be calculated during a normal render
            // operation and then added to the grid in the correct row and column.
            panels[0].grid = [
                [panels[0].fields[0]]
            ];
            panels[1].grid = [
                [panels[1].fields[0]],
                [panels[1].fields[1]],
                [panels[1].fields[2]]
            ];
            panels[2].grid = [
                [panels[2].fields[0]],
                [panels[2].fields[1]],
                [panels[2].fields[2]],
                [panels[2].fields[3]]
            ];
        });
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinon.restore();
        view = null;
    });

    describe('more_less toggle', function() {
        it('should set state when more/less is toggled', function() {
            var setStateStub = sinon.stub(SugarTest.app.user.lastState, 'set').callsFake($.noop());
            view.toggleMoreLess();
            expect(setStateStub.calledOnce).toBe(true);
            view.toggleMoreLess();
            expect(setStateStub.calledTwice).toBe(true);
            setStateStub.restore();
        });

        it('should set visibility during rendering time when last state is assigned', function() {
            var getState = 'less';
            var getStateStub = sinon.stub(SugarTest.app.user.lastState, 'get').callsFake(function() {
                return getState;
            });
            view.render();
            expect(view.hidePanel).toBe(true);
            expect(view.hideMoreButton).toBe(false);
            expect(view.hideLessButton).toBe(true);
            getStateStub.resetHistory();

            getState = 'more';
            view.render();
            expect(view.hidePanel).toBe(false);
            expect(view.hideMoreButton).toBe(true);
            expect(view.hideLessButton).toBe(false);

            getStateStub.restore();
        });
    });

});

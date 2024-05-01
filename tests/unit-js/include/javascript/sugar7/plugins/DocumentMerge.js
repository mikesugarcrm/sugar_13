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
describe('Plugins.DocumentMerge', function() {
    var moduleName = 'Accounts';
    var sandbox;
    var app = SUGAR.App;
    var plugin;
    var recordView;
    var recordListView;
    var recordListLayout;
    var subpanelListLayout;
    var parentLayout;
    var subpanelListView;

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();

        SugarTest.loadPlugin('DocumentMerge');
        plugin = app.plugins.plugins.view.DocumentMerge;

        loadRecordViewData();
        loadRecordListViewData();

        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app.routing.start();

        recordView = SugarTest.createView('base', moduleName, 'record', null, null);
        recordListView = SugarTest.createView('base', moduleName, 'recordlist', null, null);
        recordListLayout = SugarTest.createLayout('base', moduleName, 'list', null, null);
        recordListView.layout = recordListLayout;

        subpanelListLayout = SugarTest.createLayout('base', moduleName, 'subpanels', null, null);
        parentLayout = SugarTest.createLayout('base', moduleName, 'list', null, null);
        subpanelListLayout.layout = parentLayout;
        SugarTest.loadComponent('base', 'view', 'subpanel-list');
        subpanelListView = SugarTest.createView(
            'base',
            moduleName,
            'subpanel-list',
            null,
            null,
            null,
            subpanelListLayout
        );
        app.drawer = {open: sinon.stub()};
    });

    function loadRecordViewData() {
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('record-decor', 'field', 'base', 'record-decor');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'businesscard');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'field', 'record-decor');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            'buttons': [
                {
                    'type': 'button',
                    'name': 'cancel_button',
                    'label': 'LBL_CANCEL_BUTTON_LABEL',
                    'css_class': 'btn-invisible btn-link',
                    'showOn': 'edit'
                },
                {
                    'type': 'actiondropdown',
                    'name': 'main_dropdown',
                    'buttons': [
                        {
                            'type': 'rowaction',
                            'event': 'button:edit_button:click',
                            'name': 'edit_button',
                            'label': 'LBL_EDIT_BUTTON_LABEL',
                            'primary': true,
                            'showOn': 'view',
                            'acl_action': 'edit'
                        },
                        {
                            'type': 'rowaction',
                            'event': 'button:save_button:click',
                            'name': 'save_button',
                            'label': 'LBL_SAVE_BUTTON_LABEL',
                            'primary': true,
                            'showOn': 'edit',
                            'acl_action': 'edit'
                        },
                        {
                            'type': 'rowaction',
                            'name': 'delete_button',
                            'label': 'LBL_DELETE_BUTTON_LABEL',
                            'showOn': 'view',
                            'acl_action': 'delete'
                        },
                        {
                            'type': 'rowaction',
                            'name': 'duplicate_button',
                            'label': 'LBL_DUPLICATE_BUTTON_LABEL',
                            'showOn': 'view',
                            'acl_module': moduleName
                        }
                    ]
                }
            ],
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
                        {name: 'type', type: 'text', label: 'type', span: 8, labelSpan: 4},
                        {
                            name: 'commentlog',
                            type: 'commentlog',
                            label: 'Comment Log',
                            span: 8,
                            labelSpan: 4,
                            fields: [
                                'entry',
                                'date_entered',
                                'created_by_name',
                            ],
                        },
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
    };

    function loadRecordListViewData() {
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('recordlist', 'view', 'base', 'row');
        SugarTest.testMetadata.addViewDefinition('list', {
            'favorite': true,
            'selection': {
                'type': 'multi',
                'actions': []
            },
            'rowactions': {
                'actions': []
            },
            'panels': [
                {
                    'name': 'panel_header',
                    'header': true,
                    'fields': [
                        'name',
                        'case_number',
                        'type',
                        'description',
                        'date_entered',
                        'date_modified',
                        'modified_user_id'
                    ]
                }
            ]
        }, moduleName);
    }

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
        delete app.plugins.plugins.view.DocumentMerge;
    });

    describe('record merge buttons', function() {
        using('combinations of licenses', [
            {
                licenses: ['ENT'],
                authenticated: true,
                expected: 1,
            },
        ], function(values) {
            it('should check if the merge buttons exist on the record view', function() {
                sandbox.stub(app.user, 'get')
                    .returns(values.licenses)
                    .withArgs('cookie_consent').returns(true)
                    .withArgs('show_wizard').returns(false);
                sandbox.stub(app.acl, 'hasAccess')
                    .withArgs('edit', 'DocumentMerges').returns(true)
                    .withArgs('view', 'DocumentTemplates').returns(true);

                sandbox.stub(app.api, 'isAuthenticated').returns(values.authenticated);

                recordView._addDocumentMergeButtons();

                var mainDropdown = _.filter(recordView.meta.buttons, function(button) {
                    return button.name === 'main_dropdown';
                });

                var mergeButton = _.filter(mainDropdown[0].buttons, function(button) {
                    return button.name === 'merge_template';
                });

                var mergePdfButton = _.filter(mainDropdown[0].buttons, function(button) {
                    return button.name === 'merge_template_pdf';
                });
                expect(mergeButton.length).toBe(values.expected);
                expect(mergePdfButton.length).toBe(values.expected);
            });

            it('should open a drawer on click', function() {
                plugin.onAttach.apply(recordView);
                recordView.mergeTemplate('merge');
                expect(app.drawer.open).toHaveBeenCalled();
            });
        });
    });

    describe('recordlist merge buttons', function() {
        using('combinations of licenses', [
            {
                licenses: ['ENT'],
                authenticated: true,
                expected: 1,
            },
        ], function(values) {
            it('should check if the merge buttons exist on the record view', function() {
                sandbox.stub(app.user, 'get')
                    .returns(values.licenses)
                    .withArgs('cookie_consent').returns(true)
                    .withArgs('show_wizard').returns(false);
                sandbox.stub(app.acl, 'hasAccess')
                    .withArgs('edit', 'DocumentMerges').returns(true)
                    .withArgs('view', 'DocumentTemplates').returns(true);

                sandbox.stub(app.api, 'isAuthenticated').returns(values.authenticated);

                recordListView._addDocumentMergeButtons();

                var mainDropdown = recordListView.meta.selection.actions;

                var mergeButton = _.filter(mainDropdown, function(button) {
                    return button.name === 'merge_template';
                });

                var mergePdfButton = _.filter(mainDropdown, function(button) {
                    return button.name === 'merge_template_pdf';
                });
                expect(mergeButton.length).toBe(values.expected);
                expect(mergePdfButton.length).toBe(values.expected);
            });
        });
    });

    describe('subpanel-list merge buttons', function() {
        using('combinations of licenses', [
            {
                licenses: ['ENT'],
                authenticated: true,
                expected: 1,
            },
        ], function(values) {
            it('should check if the merge buttons exist on the record view', function() {
                sandbox.stub(app.user, 'get')
                    .returns(values.licenses)
                    .withArgs('cookie_consent').returns(true)
                    .withArgs('show_wizard').returns(false);
                sandbox.stub(app.acl, 'hasAccess')
                    .withArgs('edit', 'DocumentMerges').returns(true)
                    .withArgs('view', 'DocumentTemplates').returns(true);

                sandbox.stub(app.api, 'isAuthenticated').returns(values.authenticated);

                subpanelListView._addDocumentMergeButtons();

                var mainDropdown = subpanelListView.meta.rowactions.actions;

                var mergeButton = _.filter(mainDropdown, function(button) {
                    return button.name === 'merge_template';
                });

                var mergePdfButton = _.filter(mainDropdown, function(button) {
                    return button.name === 'merge_template_pdf';
                });
                expect(mergeButton.length).toBe(values.expected);
                expect(mergePdfButton.length).toBe(values.expected);
            });
        });
    });
});

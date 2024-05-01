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
describe('Users.View.CopyContentItems', function() {
    var app;
    var view;
    var layout;
    var module = 'Users';
    var model;
    var _renderDropdownsStub;
    var mockEvent;
    var updateSelectionFiltersStub;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'copy-content', 'Users');

        SugarTest.loadComponent('base', 'view', 'copy-content-buttons', 'Users');
        SugarTest.loadComponent('base', 'view', 'copy-content-items', 'Users');
        SugarTest.loadComponent('base', 'view', 'copy-content-users', 'Users');
        SugarTest.loadComponent('base', 'field', 'hybrid-select', 'Users');

        SugarTest.loadHandlebarsTemplate('copy-content-items', 'view', 'base', null, 'Users');
        SugarTest.loadHandlebarsTemplate('copy-content-items', 'view', 'base', 'dashboards', 'Users');
        SugarTest.loadHandlebarsTemplate('copy-content-items', 'view', 'base', 'filters', 'Users');
        SugarTest.loadHandlebarsTemplate('copy-content-items', 'view', 'base', 'from-modules', 'Users');
        SugarTest.loadHandlebarsTemplate('copy-content-items', 'view', 'base', 'user-preferences', 'Users');
        SugarTest.loadHandlebarsTemplate('copy-content-items', 'view', 'base', 'copy-content-items', 'Users');

        SugarTest.testMetadata.addLayoutDefinition('copy-content', {
            'css-class': 'w-max',
            'components': [
                {
                    view: 'copy-content-buttons'
                },
                {
                    view: 'copy-content-items',
                },
                {
                    view: 'copy-content-users',
                },
            ],
        }, 'Users');

        SugarTest.testMetadata.set();
        app = SugarTest.app;
        model = app.data.createBean(module);

        layout = SugarTest.createLayout('base', 'Users', 'copy-content', null, null, true);

        var viewMeta = {
            'fields': {
                'dashboards_module_select': {
                    'name': 'dashboards_module_select',
                    'type': 'module-enum',
                    'view_template': 'edit',
                },
                'dashboards_select': {
                    'name': 'dashboards_select',
                    'type': 'hybrid-select',
                    'select_module': 'Dashboards',
                    'view_template': 'edit',
                    'placeholder': 'LBL_SELECT_DASHBOARDS',
                },
                'filters_select': {
                    'name': 'filters_select',
                    'type': 'hybrid-select',
                    'select_module': 'Filters',
                    'view_template': 'edit',
                    'placeholder': 'LBL_SELECT_FILTERS',
                },
            }
        };

        view = SugarTest.createView('base', 'Users', 'copy-content-items', viewMeta, null, true, layout);
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
    });

    describe('initialize', function() {
        it('should init the view', function() {
            expect(view.currentUserId).toBe(app.user.id);
            expect(view.sourceUser).toBe(app.user.id);
        });
    });

    describe('_render', function() {
        beforeEach(function() {
            _renderDropdownsStub = sinon.stub(view, '_renderDropdowns');
            view._render();
        });

        it('should render the view', function() {
            expect(_renderDropdownsStub).toHaveBeenCalled();
        });
    });

    describe('changeSection', function() {
        beforeEach(function() {
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('input');
            mockEvent.target.value = 'user_prefs';
            view.render();
            view.changeSection(mockEvent);
        });

        it('should change the current section', function() {
            expect(view.$('.user-prefs-section')).not.toHaveClass('hide');
        });
    });

    describe('changeFromUser', function() {
        beforeEach(function() {
            mockEvent = $.Event('click');
            mockEvent.target = {};
            mockEvent.target.selectedIndex = 1;
            var option1 = {};
            option1.dataset = {id: '1'};
            var option2 = {};
            option2.dataset = {id: '2'};
            mockEvent.target.options = [option1, option2];
            updateSelectionFiltersStub = sinon.stub(view, 'updateSelectionFilters');
            view.changeFromUser(mockEvent);
        });

        it('should change the source user', function() {
            expect(view.sourceUser).toBe('2');
            expect(updateSelectionFiltersStub).toHaveBeenCalled();
        });
    });

    describe('changeSelection', function() {
        beforeEach(function() {
            mockEvent = $.Event('click');
            mockEvent.target = document.createElement('input');
            mockEvent.target.value = 'existing_dashboards';
            view.render();
            view.changeSelection(mockEvent);
        });

        it('should change the current section', function() {
            expect(view.$('.existing-dashboards')).not.toHaveClass('hide');
        });
    });
});

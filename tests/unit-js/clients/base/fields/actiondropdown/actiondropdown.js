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
describe('Base.Field.Actiondropdown', function() {

    var app, field, view, moduleName = 'Contacts', fieldDef;
    let $defaultPlaceholder = null;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('actiondropdown', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('actiondropdown', 'field', 'base', 'dropdown');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        fieldDef = {
            'name': 'main_dropdown',
            'type': 'actiondropdown',
            'buttons': [
                {
                    'type' : 'rowaction',
                    'name' : 'test1'
                },
                {
                    'type' : 'rowaction',
                    'name' : 'test2'
                }
            ]
        };
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    describe('render dropdown', function() {
        it('should render button html nested on the buttons', function() {
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', fieldDef, moduleName);
            field.render();
            field.renderDropdown();
            expect(field.fields.length).toBe(2);
            _.each(field.fields, function(button) {
                var actualPlaceholderCount = field.$el.find("span[sfuuid='" + button.sfId + "']").length;
                expect(actualPlaceholderCount).toBe(1);
            });

            field.dispose();
        });

        it('should have btn-group class when more than one button is visible', function() {
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', fieldDef, moduleName);
            field.render();
            field.renderDropdown();
            expect(field.$el.hasClass('btn-group')).toBe(true);

            field.dispose();
        });

        it('should not have btn-group class when only one button is visible', function() {
            fieldDef.buttons.pop();
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', fieldDef, moduleName);
            field.render();
            expect(field.fields.length).toBe(1);
            field.renderDropdown();
            expect(field.$el.hasClass('btn-group')).toBe(false);

            field.dispose();
        });
    });

    describe('setDisabled', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'main_dropdown',
                'actiondropdown',
                'detail',
                {
                    name: 'main_dropdown',
                    type: 'actiondropdown',
                    buttons: [
                        {
                            type: 'rowaction',
                            name: 'test1'
                        },
                        {
                            type: 'rowaction',
                            name: 'test2'
                        }
                    ]
                },
                moduleName
            );
            field.render();
        });

        it('should toggle attributes', function() {
            field.setDisabled(true);

            expect(field.tabIndex).toEqual(-1);
            var $actionDropDownTag = field.$(field.actionDropDownTag);
            expect($actionDropDownTag.hasClass('disabled')).toEqual(true);
            expect($actionDropDownTag.attr('aria-haspopup')).toEqual('false');
            expect($actionDropDownTag.attr('tabindex')).toEqual('-1');

            field.setDisabled(false);
            $actionDropDownTag = field.$(field.actionDropDownTag);
            expect(field.tabIndex).toEqual(0);
            expect($actionDropDownTag.hasClass('disabled')).toEqual(false);
            expect($actionDropDownTag.attr('aria-haspopup')).toEqual('true');
            expect($actionDropDownTag.attr('tabindex')).toEqual('0');
        });
    });

    describe('switch_on_click', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', {
                'name': 'main_dropdown',
                'type': 'actiondropdown',
                'switch_on_click': true,
                'buttons': [
                    {
                        'type' : 'rowaction',
                        'name' : 'test1'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test2'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test3'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test4'
                    }
                ]
            }, moduleName);
            field.render();
        });
        afterEach(function() {
            field.dispose();
        });
        it('should switch the selected action against the default action', function() {
            var selectedAction = 1,
                actualDefaultButton = field.defaultActionBtn,
                actualSelectedButton = field.dropdownFields[selectedAction];
            expect(actualDefaultButton.def.name).toBe('test1');

            //click dropdown toggle to display the dropdown actions
            field.$('[data-bs-toggle=dropdown]').click();
            expect(actualSelectedButton.def.name).toBe('test3');

            //after the dropdown action is clicked, both buttons are switched
            actualSelectedButton.$el.click();
            field.renderDropdown();
            expect(field.defaultActionBtn.def.name).toBe('test3');
            expect(field.dropdownFields[selectedAction].def.name).toBe('test1');

            //the default button place underneath the dropdown
            var $actualDropdown = field.$('.dropdown-menu'),
                searchSelectedButtonOnDropdown = $actualDropdown
                    .find('span[sfuuid="' + actualSelectedButton.sfId + '"]'),
                searchDefaultButtonOnDropdown = $actualDropdown
                    .find('span[sfuuid="' + actualDefaultButton.sfId + '"]');
            expect(searchSelectedButtonOnDropdown.length).toBe(0);
            expect(searchDefaultButtonOnDropdown.length).toBe(1);

            //the selected button place on the default action
            $defaultPlaceholder = field.$('[data-bs-toggle=dropdown]').prev(),
                searchSelectedButtonOnDefault = $defaultPlaceholder
                    .is('span[sfuuid="' + actualSelectedButton.sfId + '"]'),
                searchDefaultButtonOnDefault = $defaultPlaceholder
                    .is('span[sfuuid="' + actualDefaultButton.sfId + '"]');
            expect(searchSelectedButtonOnDefault).toBe(true);
            expect(searchDefaultButtonOnDefault).toBe(false);
        });
    });
    describe('no_default_action', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', {
                'name': 'main_dropdown',
                'type': 'actiondropdown',
                //'switch_on_click' option must be ignored when no_default_action is enabled
                'switch_on_click': true,
                'no_default_action': true,
                'buttons': [
                    {
                        'type' : 'rowaction',
                        'name' : 'test1'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test2'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test3'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test4'
                    }
                ]
            }, moduleName);
            var $element = $(field.getPlaceholder().toString());
            field.setElement($element);
            field.render();
        });
        afterEach(function() {
            field.dispose();
        });
        it('should place all buttons underneath the dropdown actions', function() {
            //click dropdown toggle to display the dropdown actions
            field.$(field.actionDropDownTag).click();
            $defaultPlaceholder = field.$(field.actionDropDownTag).prev();

            //the default placeholder has to be empty
            expect($defaultPlaceholder.length).toBe(0);
            var $actualDropdown = field.$('.dropdown-menu');

            //all button fields have to place underneath the dropdown actions
            _.each(field.fields, function(button) {
                var checkButtonOnDropdown = $actualDropdown
                    .find('span[sfuuid="' + button.sfId + '"]');
                expect(checkButtonOnDropdown.length).toBe(1);
            }, this);
        });

        it('should not switch the selected buttons if no_default_action is true', function() {
            var defaultAction = 0,
                selectedAction = 2,
                actualDefaultButton = field.fields[defaultAction],
                actualSelectedButton = field.fields[selectedAction];
            expect(actualDefaultButton.def.name).toBe('test1');

            //click dropdown toggle to display the dropdown actions
            field.$('[data-bs-toggle=dropdown]').click();
            expect(actualSelectedButton.def.name).toBe('test3');

            //after the dropdown action is clicked, both buttons are switched
            actualSelectedButton.$el.click();

            //after an action is selected, the button should remain as original condition
            expect(field.fields[defaultAction].def.name).toBe('test1');
            expect(field.fields[selectedAction].def.name).toBe('test3');
        });
    });

    describe('divider', function() {
        var fieldDef;
        beforeEach(function() {
            fieldDef = {
                'name': 'main_dropdown',
                'type': 'actiondropdown',
                'no_default_action': true,
                //'switch_on_click' option must be ignored when no_default_action is enabled
                'switch_on_click': true,
                'buttons': [
                    {
                        'type' : 'rowaction',
                        'name' : 'test1'
                    },
                    {
                        'type' : 'divider'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test2'
                    },
                    {
                        'type' : 'divider'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test3'
                    },
                    {
                        'type' : 'divider'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test3'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test4'
                    }
                ]
            };
        });

        it('should contain the divider in the correct location', function() {
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', fieldDef, moduleName);
            field.render();

            //click dropdown toggle to display the dropdown actions
            field.$('[data-bs-toggle=dropdown]').click();
            var $actualDropdown = field.$('.dropdown-menu'),
                $firtDropdown = $actualDropdown.find('li:eq(0)'),
                $secondDropdown = $actualDropdown.find('li:eq(1)'),
                $thirdDropdown = $actualDropdown.find('li:eq(2)'),
                $forthDropdown = $actualDropdown.find('li:eq(3)');
            expect($firtDropdown.hasClass('divider')).toBe(false);
            expect($secondDropdown.hasClass('divider')).toBe(true);
            expect($thirdDropdown.hasClass('divider')).toBe(false);
            expect($forthDropdown.hasClass('divider')).toBe(true);

            field.dispose();
        });

        it('should not contain the divider above the first actionmenu', function() {
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', fieldDef, moduleName);
            field.render();

            //click dropdown toggle to display the dropdown actions
            field.$('[data-bs-toggle=dropdown]').click();

            var $actualDropdown = field.$('.dropdown-menu'),
                $divider = $actualDropdown.find('li:eq(0)');
            expect($divider.hasClass('divider')).toBe(false);

            field.dispose();
        });

        it('should re-organize the divider once button is hidden due to access fails', function() {
            //click dropdown toggle to display the dropdown actions
            var accessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function(action, module) {
                return action === 'edit' ? false : true;
            });
            fieldDef['buttons'][2]['acl_action'] = 'edit';
            fieldDef['buttons'][2]['acl_module'] = moduleName;

            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', fieldDef, moduleName);
            field.render();
            field.renderDropdown();

            var $actualDropdown = field.$('.dropdown-menu'),
                $firtDropdown = $actualDropdown.find('li:eq(0)'),
                $secondDropdown = $actualDropdown.find('li:eq(1)'),
                $thirdDropdown = $actualDropdown.find('li:eq(2)'),
                $forthDropdown = $actualDropdown.find('li:eq(3)');
            expect($firtDropdown.hasClass('divider')).toBe(false);
            expect($secondDropdown.hasClass('divider')).toBe(true);
            expect($thirdDropdown.hasClass('divider')).toBe(false);
            expect($forthDropdown.hasClass('divider')).toBe(true);
            accessStub.restore();

            field.dispose();
        });
    });

    describe('setMode', function() {
        var f;

        beforeEach(function() {
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', fieldDef, moduleName);
            field.render();
            f = _.first(field.fields);
            f.def.icon = 'sicon sicon-edit';
        });

        afterEach(function() {
            f.def.icon = undefined;
            field.dispose();
        });

        it('should set action to list for first field when not in a subpanel', function() {
            field.setMode('list');
            expect(f.action).toEqual('list');
        });
        it('should set the action to small for first field when in a subpanel', function() {
            sinon.stub(f, 'closestComponent').callsFake(function() {
                return true;
            });
            field.setMode('list');
            expect(f.action).toEqual('small');
        });
    });
});

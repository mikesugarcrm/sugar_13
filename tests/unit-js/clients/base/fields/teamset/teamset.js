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
describe('Base.Field.Teamset', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        Handlebars.templates = {};
    });

    describe('edit', function() {
        var field, oRouter, buildRouteStub;

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
            var fieldDef = {
                'name': 'team_name',
                'rname': 'name',
                'vname': 'LBL_TEAM_NAME',
                'type': 'relate',
                'custom_type': 'teamset',
                'link': 'accounts',
                'table': 'accounts',
                'join_name': 'accounts',
                'isnull': 'true',
                'module': 'Accounts',
                'dbType': 'varchar',
                'len': 100,
                'source': 'non-db',
                'unified_search': true,
                'comment': 'The name of the account represented by the account_id field',
                'required': true, 'importable': 'required'
            };
            SugarTest.declareData('base', 'Filters');
            SugarTest.loadComponent('base', 'field', 'relate');
            var model = app.data.createBean('Accounts', {
                id: 'blahblahid',
                team_name: [
                    {id: 'test-id', name: 'blahblah', primary: false}
                ]
            });

            sinon.stub(Backbone.Collection.prototype, 'fetch');
            sinon.stub(app.BeanCollection.prototype, 'fetch');

            field = SugarTest.createField('base', 'team_name', 'teamset', 'edit', fieldDef, null, model);

            if (!$.fn.select2) {
                $.fn.select2 = function(options) {
                    var obj = {
                        on: function() {
                            return obj;
                        }
                    };
                    return obj;
                };
            }

            // Workaround because router not defined yet
            oRouter = SugarTest.app.router;
            SugarTest.app.router = {buildRoute: $.noop};
            buildRouteStub = sinon.stub(SugarTest.app.router, 'buildRoute').callsFake(
                function(module, id, action, params) {
                    return module + '/' + id;
                }
            );
        });

        afterEach(function() {
            sinon.restore();
            SugarTest.app.router = oRouter;
            field.dispose();
        });

        it('should set value correctly', function() {
            var index = 0;
            field.render();
            field.$el.append($('<select data-index=' + index + '></select><div class="chzn-container-active"></div>'));
            var expected_id = '0987',
                expected_name = 'blahblah';
            field.setValue({id: expected_id, value: expected_name});
            var actual_model = field.model.get('team_name'),
                actual_id = actual_model[index].id,
                actual_name = actual_model[index].name;

            expect(actual_id).toEqual(expected_id);
            expect(actual_name).toEqual(expected_name);
        });

        it('should load the default team setting that is specified in the user profile settings', function() {
            field.model = app.data.createBean('Accounts');
            var expected = [{ id: '1', name: 'global' }];
            sinon.stub(app.user, 'getPreference').callsFake(function() {
                return expected;
            });
            field.render();
            var actual = field.value;
            expect(actual.length).toEqual(1);
            expect(expected[0].id).toEqual(actual[0].id);
            expect(expected[0].name).toEqual(actual[0].name);
            var actual_var = field.model.get('team_name');
            expect(actual_var.length).toEqual(1);
            expect(actual_var[0].id).toEqual(expected[0].id);
            expect(actual_var[0].name).toEqual(expected[0].name);
            expect(actual_var).not.toBe(expected);
        });

        it('should add or remove team from the list', function() {
            field.render();
            var expected = (field.model.get(field.def.name)).length + 1;
            field.addTeam();
            // A team object shouldn't appear in model unless a team is specified.
            field.setValue({id: 'test', value: 'test'});
            var actual = (field.model.get(field.def.name)).length;
            expect(expected).toEqual(actual);

            expected = actual - 1;
            field.removeTeam(0);
            actual = (field.model.get(field.def.name)).length;
            expect(expected).toEqual(actual);
        });

        it('should set a team as primary', function() {
            field.model.set('team_name', [
                {id: '111-222', name: 'blahblah', primary: false},
                {id: 'abc-eee', name: 'poo boo', primary: true}
            ]);
            field.render();
            expect(field.value[0].primary).toBe(false);
            expect(field.value[1].primary).toBe(true);

            field.setPrimary(0);
            expect(field.value[0].primary).toBe(true);
            expect(field.value[1].primary).toBe(false);

        });

        it('should toggle out the primary option only when teamset appends team with its existing primary', function() {
            field.model.set('team_name', [
                {id: '111-222', name: 'blahblah', primary: false},
                {id: 'abc-eee', name: 'poo boo', primary: true}
            ]);
            //Setup for appeding team
            field.model.set('team_name_type', '1');
            field.render();
            expect(field.value[0].primary).toBe(false);
            expect(field.value[1].primary).toBe(true);

            field.setPrimary(1);
            expect(field.value[0].primary).toBe(false);
            expect(field.value[1].primary).toBe(false);

            field.setPrimary(1);
            expect(field.value[0].primary).toBe(false);
            expect(field.value[1].primary).toBe(true);

        });

        it('should set the first item as primary when user choose to not append team', function() {
            field.model.set('team_name', [
                {id: '111-222', name: 'blahblah', primary: false},
                {id: 'abc-eee', name: 'poo boo', primary: true}
            ]);
            //Setup for appeding team
            field.model.set('team_name_type', '1');
            field.render();
            field.setPrimary(1);
            expect(field.value[0].primary).toBe(false);
            expect(field.value[1].primary).toBe(false);

            field.model.set('team_name_type', '0');
            expect(field.value[0].primary).toBe(true);
            expect(field.value[1].primary).toBe(false);
        });

        it('should not let you remove last team when there is only one team left', function() {
            field.model.set('team_name', [
                {id: '111-222', name: 'blahblah', primary: true}
            ]);
            field.render();
            field.removeTeam(0);
            expect(field.value[0].id).toEqual('111-222');
        });

        it('should have an add button but not a remove button when there is only one team left', function() {
            field.model.set('team_name', [
                {id: '111-222', name: 'blahblah', primary: true}
            ]);
            field.render();
            expect(field.value).toEqual([
                {id: '111-222', name: 'blahblah', primary: true, add_button: true}
            ]);
        });

        it('with multiple teams should have remove buttons and an add button on last team', function() {
            field.model.set('team_name', [
                {id: '111-222', name: 'blahblah', primary: true},
                {id: '222-333', name: 'blahblah2', primary: false}
            ]);
            field.render();
            expect(field.value[0]).toEqual(
                {id: '111-222', name: 'blahblah', primary: true, remove_button: true});
            expect(field.value[1]).toEqual(
                {id: '222-333', name: 'blahblah2', primary: false, remove_button: true, add_button: true});
        });

        it('cannot make a blank team the primary team', function() {
            field.model.set('team_name', [
                {id: 'abc-eee', name: 'poo boo', primary: true},
                {primary: false}
            ]);
            field.render();
            expect(field.value[0].primary).toBe(true);
            expect(field.value[1].primary).toBe(false);
            field.setPrimary(1);
            expect(field.value[1].primary).toBe(false);
        });

        it('cannot make an unselected team (that has no ID) the primary team', function() {
            sinon.stub(jQuery, 'data').callsFake(function() {
                // Mocks current target's data attribute index to return index to second item
                return 1;
            });
            var setPrimaryStub = sinon.stub(field, 'setPrimary');
            field.model.set('team_name', [
                {id: 1, 'name': 'Global', 'primary': true},
                {'add_button': true, 'remove_button': true, primary: false}
            ]);
            field.render();
            field.setPrimaryItem({});
            expect(field.value[1].primary).toBe(false);
            expect(setPrimaryStub).not.toHaveBeenCalled();
        });

        it('should let you add an item if team IS selected for very last item', function() {
            var addTeamStub = sinon.stub(field, 'addTeam');
            field.model.set('team_name', [
                {id: 1, 'name': 'Global', 'primary': true},
                {'add_button': true, primary: false, id: 2}
            ]);
            field.render();
            sinon.stub(jQuery.fn, 'data').callsFake(function() {
                return 1;
            });
            field.addItem({});
            runs(function() {
                expect(addTeamStub).toHaveBeenCalled();
            });
        });

        it('should NOT let you add an item if team has not been selected for very last item', function() {
            var addTeamStub = sinon.stub(field, 'addTeam');
            field.model.set('team_name', [
                {id: 1, 'name': 'Global', 'primary': true},
                {'add_button': true, primary: false}
            ]);
            field.render();
            sinon.stub(jQuery.fn, 'data').callsFake(function() {
                return 1;
            });
            field.addItem({});
            runs(function() {
                expect(addTeamStub).not.toHaveBeenCalled();
            });
        });

        it('should leave model in `hasChanged` state upon calling _updateAndTriggerChange', function() {
            var teamsetValue = [
                {'id': 'West', 'primary': true}
            ];
            field._updateAndTriggerChange(teamsetValue);
            expect(field.model.hasChanged()).toBeTruthy();
        });

        it('should be able to compare for the equality', function() {
            var teamsetValue = [
                    {'id': 'East', 'primary': true},
                    {'id': 'West', 'stuff': 'ignore'}
                ],
                equalTeamsetValue = [
                    {'id': 'East', 'primary': true, 'additional': 'blah'},
                    {'id': 'West'}
                ],
                otherFielddef = {
                    'name': 'other_team_name',
                    'type': 'teamset'
                },
                otherField = app.view.createField({
                    def: otherFielddef,
                    view: field.view,
                    context: field.context,
                    model: field.model,
                    module: field.model.module,
                    platform: 'base'
                });

            sinon.stub(app.view.Field.prototype, 'getFormattedValue').callsFake(function() {
                return this.format(this.model.get(this.name));
            });

            field.model.set(field.name, teamsetValue, {silent: true});
            expect(field.equals(otherField)).toBe(false);
            otherField.model.set(otherField.name, equalTeamsetValue, {silent: true});
            expect(field.equals(otherField)).toBe(true);
        });

        describe('_loadTemplate', function() {
            it('should load list template when doing inline editing (See SP-1197)', function() {
                field.view.action = 'list';
                field.action = 'edit';
                field._loadTemplate();
                expect(field.tplName).toEqual('list');

                field.view.action = 'list';
                field.action = 'list';
                field._loadTemplate();
                expect(field.tplName).toEqual('list');
            });

            it('should load the correct template when on the audit log', function() {
                field.view.name = 'audit';
                field.view.baseModule = field.def.module;
                field.action = 'list';

                field._loadTemplate();
                expect(field.tplName).toEqual('audit-list');
            });
        });

        describe('formatFieldForDuplicate', function() {
            var _team = function(id, primary) {
                return {
                    id: id,
                    name: id,
                    primary: primary
                };
            };
            var model1, model2;

            beforeEach(function() {
                field.view.generatedValues = {};
                field.view.generatedValues.teamsets = {};
                field.view.generatedValues.teamsets = {
                    team_name: [
                        _team('East', false),
                        _team('West', true),
                        _team('Global', false)
                    ]
                };

                model1 = new Backbone.Model({
                    team_name: [
                        _team('East', false),
                        _team('West', true)
                    ]
                });

                model2 = new Backbone.Model({
                    team_name: [
                        _team('Global', false),
                        _team('East', true)
                    ]
                });
                field.view.collection = new Backbone.Collection();
                field.view.collection.add(model1);
                field.view.collection.add(model2);
                field.view.primaryRecord = model1;
            });

            it('should fill team names with the right check property for each model', function() {
                var teams;
                field.formatFieldForDuplicate();
                expect(field.view.collection.models).not.toBeEmpty();

                teams = field.view.collection.models[0].get('team_name');
                expect(teams.length).toBe(3);
                expect(teams[0].id).toEqual('East');
                expect(teams[0].checked).toBeTruthy();
                expect(teams[1].id).toEqual('West');
                expect(teams[1].checked).toBeTruthy();
                expect(teams[2].id).toEqual('Global');
                expect(teams[2].checked).toBeFalsy();

                teams = field.view.collection.models[1].get('team_name');
                expect(teams.length).toBe(3);
                expect(teams[0].id).toEqual('East');
                expect(teams[0].checked).toBeFalsy();
                expect(teams[1].id).toBeUndefined();
                expect(teams[1].checked).toBeFalsy();
                expect(teams[2].id).toEqual('Global');
                expect(teams[2].checked).toBeFalsy();
            });
        });

        describe('unformatFieldForDuplicate', function() {
            var _team = function(id, primary, checked) {
                return {
                    id: id,
                    name: id,
                    primary: primary,
                    checked: checked
                };
            };

            var primaryRecord;

            beforeEach(function() {
                field.view.generatedValues = {};
                field.view.generatedValues.teamsets = {};
                field.view.generatedValues.teamsets = {
                    team_name: [
                        _team('East', false),
                        _team('West', true),
                        _team('Global', false)
                    ]
                };

                primaryRecord = new Backbone.Model({
                    team_name: [
                        _team('East', false, false),
                        _team('West', true, true),
                        _team('Global', false, true)
                    ]
                });

                field.view.collection = new Backbone.Collection();
                field.view.collection.add(primaryRecord);
                field.view.primaryRecord = primaryRecord;
            });

            it('should remove the non checked teams from the primary record', function() {
                var teams;
                field.unformatFieldForDuplicate();

                teams = field.view.collection.models[0].get('team_name');
                expect(teams.length).toBe(2);
                expect(teams[0].id).toEqual('West');
                expect(teams[0].checked).toBeTruthy();
                expect(teams[1].id).toEqual('Global');
                expect(teams[1].checked).toBeTruthy();
            });
        });
    });

    describe('massupdate', function() {
        var field;

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('teamset', 'field', 'base', 'massupdate');
            SugarTest.testMetadata.set();
            field = SugarTest.createField('base', 'team_name', 'teamset', 'massupdate');
        });

        afterEach(function() {
            field.dispose();
            SugarTest.testMetadata.dispose();
        });

        describe('render', function() {
            it('should render the field with an append_team checkbox', function() {
                field.render();

                expect(field.$(field.appendTeamTag)).toExist();
            });
        });

        describe('bindDomChange', function() {
            it('should update the model on append_team checkbox change', function() {
                field.render();

                expect(field.appendTeamValue).toBeUndefined();
                expect(field.model.get('team_name_type')).toBeUndefined();

                field.$(field.appendTeamTag).prop('checked', true).trigger('change');

                expect(field.appendTeamValue).toBeTruthy();
                expect(field.model.get('team_name_type')).toBe('1');

                field.$(field.appendTeamTag).prop('checked', false).trigger('change');

                expect(field.appendTeamValue).toBeFalsy();
                expect(field.model.get('team_name_type')).toBe('0');

                field.$(field.appendTeamTag).prop('checked', true).trigger('change');

                expect(field.appendTeamValue).toBeTruthy();
                expect(field.model.get('team_name_type')).toBe('1');
            });
        });
    });
});

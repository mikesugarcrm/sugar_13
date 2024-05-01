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
describe('ConsoleConfiguration.View.ConfigHeaderButtons', function() {
    var app;
    var view;
    var context;
    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        view = SugarTest.createView('base', 'ConsoleConfiguration', 'config-header-buttons', null, null, true);
        app.routing.start();
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var result;
        var options;
        var showInvalidModelStub;
        beforeEach(function() {
            options = {
                context: context,
            };

            sinon.stub(view, '_super').callsFake(function() {});
            showInvalidModelStub = sinon.stub();
            view.initialize(options);
        });

        afterEach(function() {
            result = null;
        });

        it('should call view._super with initialize and options', function() {

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should initialize view._viewAlerts as an empty array', function() {

            expect(view._viewAlerts).toEqual([]);
        });
    });

    describe('showInvalidModel', function() {
        describe('when view is defined', function() {
            beforeEach(function() {
                view._viewAlerts = [];
                sinon.stub(app.alert, 'show').callsFake(function() {});
                view.showInvalidModel();
            });

            it('should not call app.logger.error method', function() {

                expect(app.logger.error).not.toHaveBeenCalled();
            });

            it('should push invalid-data into view._viewAlerts', function() {

                expect(view._viewAlerts).toEqual(['invalid-data']);
            });

            it('should call app.alert.show mwthod', function() {

                expect(app.alert.show).toHaveBeenCalledWith('invalid-data', {
                    level: 'error',
                    messages: 'ERR_RESOLVE_ERRORS'
                });
            });
        });
    });

    describe('cancelConfig', function() {
        beforeEach(function() {
            sinon.stub(view.context, 'get').callsFake(function() {});
            sinon.spy(app.router, 'navigate');
        });

        describe('when triggerBefore is true', function() {
            beforeEach(function() {
                sinon.stub(view, 'triggerBefore').callsFake(function() {return true;});
            });

            it('should not call app.router.navigate', function() {
                app.drawer = {
                    close: $.noop,
                    count: function() {
                        return 1;
                    }
                };
                sinon.spy(app.drawer, 'close');
                view.cancelConfig();

                expect(app.router.navigate).not.toHaveBeenCalled();
                delete app.drawer;
            });

            describe('when app.drawer.count is defined', function() {
                it('should call app.drawer.close method', function() {
                    app.drawer = {
                        close: $.noop,
                        count: function() {
                            return 1;
                        }
                    };
                    sinon.spy(app.drawer, 'close');
                    view.cancelConfig();

                    expect(app.drawer.close).toHaveBeenCalledWith(view.context, view.context.get());
                    delete app.drawer;
                });
            });

            describe('when app.drawer.count is not defined', function() {
                it('should not call app.drawer.close method', function() {
                    app.drawer = {
                        close: $.noop,
                        count: function() {
                            return undefined;
                        }
                    };
                    sinon.spy(app.drawer, 'close');
                    view.cancelConfig();

                    expect(app.drawer.close).not.toHaveBeenCalled();
                    delete app.drawer;
                });
            });
        });

        describe('when triggerBefore is false', function() {
            it('should not call app.router.navigate', function() {
                sinon.stub(view, 'triggerBefore').callsFake(function() {return false;});
                app.drawer = {
                    close: $.noop,
                    count: function() {
                        return 1;
                    }
                };
                sinon.spy(app.drawer, 'close');
                view.cancelConfig();

                expect(app.router.navigate).not.toHaveBeenCalled();
                delete app.drawer;
            });
        });
    });

    describe('_setOrderByFields', function() {
        var model;
        var contextModel;
        var getStub;
        var setStub;

        beforeEach(function() {
            model = new Backbone.Model('ConsoleConfiguration');
            view.collection = {
                models: [model],
                off: function() {}
            };

            // Stub the calls to "this.context.get". Pretend that no settings
            // currently exist for this fake console ID in the config table
            getStub = sinon.stub();
            getStub.withArgs('enabled_modules').returns({'1234-5678': ['Accounts']})
                .withArgs('order_by_primary').returns({})
                .withArgs('order_by_secondary').returns({})
                .withArgs('filter_def').returns({});
            setStub = sinon.stub();
            sinon.stub(view.context, 'get').withArgs('consoleId').returns('1234-5678')
                .withArgs('model').returns({
                model: model,
                get: getStub,
                set: setStub
            });

            contextModel = view.context.get('model');

            // Stub the calls that read the current field values of the view
            // for each module tab
            sinon.stub(model, 'get')
                .withArgs('enabled_module').returns('Accounts')
                .withArgs('order_by_primary').returns('next_renewal_date')
                .withArgs('order_by_primary_direction').returns('asc')
                .withArgs('order_by_secondary').returns('')
                .withArgs('order_by_seconary_direction').returns('desc')
                .withArgs('filter_def').returns({'$owner': ''});
        });

        it('should call view.context.get with model', function() {
            view._setOrderByFields();

            expect(view.context.get).toHaveBeenCalledWith('model');
        });

        it('should call model.get with method', function() {
            view._setOrderByFields();

            expect(model.get).toHaveBeenCalledWith('order_by_primary');
            expect(model.get).toHaveBeenCalledWith('order_by_secondary');
        });

        it('should call the view.context.get.set method with the new values', function() {
            view._setOrderByFields();

            expect(contextModel.set).toHaveBeenCalledWith({
                order_by_primary: {'1234-5678': {'Accounts': 'next_renewal_date:asc'}},
                order_by_secondary: {'1234-5678': {'Accounts': ''}},
            }, {silent: true});
        });
    });

    describe('_beforeSaveConfig', function() {
        var model;
        var contextModel;
        var getStub;
        var setStub;

        beforeEach(function() {
            model = new Backbone.Model('ConsoleConfiguration');
            view.collection = {
                models: [model],
                off: function() {}
            };

            // Stub the calls to "this.context.get". Pretend that no settings
            // currently exist for this fake console ID in the config table
            getStub = sinon.stub();
            getStub.withArgs('enabled_modules').returns({'1234-5678': ['Accounts']})
                .withArgs('filter_def').returns({})
                .withArgs('freeze_first_column').returns({'1234-5678': {'Accounts': true}});
            setStub = sinon.stub();
            sinon.stub(view.context, 'get').withArgs('consoleId').returns('1234-5678')
                .withArgs('model').returns({
                    model: model,
                    get: getStub,
                    set: setStub
                });

            contextModel = view.context.get('model');

            // Stub the calls that read the current field values of the view
            // for each module tab
            sinon.stub(model, 'get')
                .withArgs('enabled_module').returns('Accounts')
                .withArgs('filter_def').returns({'$owner': ''})
                .withArgs('freeze_first_column').returns(true);
        });

        it('should call view.context.get with model', function() {
            view._beforeSaveConfig();

            expect(view.context.get).toHaveBeenCalledWith('model');
        });

        it('should call view.context.get.get with enabled_modules', function() {
            view._beforeSaveConfig();

            expect(contextModel.get).toHaveBeenCalledWith('enabled_modules');
        });

        it('should call model.get with method', function() {
            view._beforeSaveConfig();

            expect(model.get).toHaveBeenCalledWith('enabled_module');
            expect(model.get).toHaveBeenCalledWith('filter_def');
            expect(model.get).toHaveBeenCalledWith('freeze_first_column');
        });

        it('should call the view.context.get.set method with the new values', function() {
            view._beforeSaveConfig();

            expect(contextModel.set).toHaveBeenCalledWith({
                is_setup: true,
                enabled_modules: {'1234-5678': ['Accounts']},
                labels: {},
                viewdefs: {},
                filter_def: {'1234-5678': {'Accounts': {'$owner': ''}}},
                freeze_first_column: {'1234-5678': {'Accounts': true}}
            }, {silent: true});
        });
    });

    describe('_saveConfig', function() {
        var setDisabledStub;
        beforeEach(function() {
            setDisabledStub = sinon.stub();
            sinon.stub(view, 'validateCollection').callsFake(function() {});
            sinon.stub(view, 'getField').callsFake(function() {
                return {
                    setDisabled: setDisabledStub
                };
            });
            view._saveConfig();
        });

        it('should set view.validatedModels to []', function() {

            expect(view.validatedModels).toEqual([]);
        });

        it('should call view.getField with save_button', function() {

            expect(view.getField).toHaveBeenCalledWith('save_button');
        });

        it('should call view.getField.setDisabled with false', function() {

            expect(view.getField('save_button').setDisabled).toHaveBeenCalledWith(true);
        });
    });

    describe('addLabelToList', function() {
        beforeEach(function() {
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_AAA').returns('LBL_AAA')
                .withArgs('LBL_BBB').returns('ANYTHING_BUT_LBL_BBB');
        });

        using('various settings', [{
            element: '<li fieldlabel="LBL_AAA" data-original-title="aaa"></li>',
            expected: [{label: 'LBL_AAA', labelValue: 'aaa'}]
        },{
            element: '<li fieldlabel="LBL_BBB" data-original-title="aaa"></li>',
            expected: []
        }], function(value) {
            it('should set addLabelToList to proper value', function() {
                labelList = [];
                view.addLabelToList($(value.element), 'Cases', labelList);
                expect(labelList).toEqual(value.expected);
            });
        });
    });

    describe('isSpecialField', function() {
        beforeEach(function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs('ModuleA', 'fields')
                .returns({
                    field1: {type: 'widget'},
                    field2: {type: 'anything_but_widget'}
                });
        });

        using('various module settings', [{
            modulename: 'ModuleA',
            fieldName: 'field1',
            expected: true
        },{
            modulename: 'ModuleA',
            fieldName: 'field2',
            expected: false
        }], function(value) {
            it('should set determine whether it is a special field', function() {
                var result = view.isSpecialField(value.fieldName, value.modulename);
                expect(result).toEqual(value.expected);
            });
        });
    });

    describe('getRelateFieldType', function() {
        beforeEach(function() {
            getViewStub = sinon.stub(app.metadata, 'getModule')
                .withArgs('ModuleA', 'fields')
                .returns({
                    anyfield: {module: 'ModuleB', rname: 'relatedFieldName'}
                })
                .withArgs('ModuleB', 'fields')
                .returns({
                    relatedFieldName: {type: 'relatedType'}
                });
        });

        using('various module settings', [{
            modulename: 'ModuleA',
            expected: 'relatedType'
        },{
            modulename: 'ModuleB',
            expected: ''
        }], function(value) {
            it('should return the actual type of a related field', function() {
                var relatedType = view.getRelateFieldType('anyfield', value.modulename);
                expect(relatedType).toEqual(value.expected);
            });
        });
    });

    describe('buildSpecialField', function() {
        beforeEach(function() {
            getViewStub = sinon.stub(app.metadata, 'getModule')
                .withArgs('ModuleA', 'fields')
                .returns({
                    field1: {console: {prop1: true}},
                    field2: {console: {prop1: true, prop2: false}}
                });
        });

        using('various module settings', [{
            modulename: 'ModuleA',
            fieldName: 'field1',
            expected: {prop1: true, widget_name: 'field1'}
        },{
            modulename: 'ModuleA',
            fieldName: 'field2',
            expected: {prop1: true, prop2: false, widget_name: 'field2'}
        }], function(value) {
            it('should build a proper special field definition', function() {
                var fieldObj = {};
                view.buildSpecialField(value.fieldName, fieldObj, value.modulename);
                expect(fieldObj).toEqual(value.expected);
            });
        });
    });

    describe('buildRegularField', function() {
        beforeEach(function() {
            getViewStub = sinon.stub(app.metadata, 'getModule')
                .withArgs('ModuleA', 'fields')
                .returns({
                    nameField: {type: 'name'},
                    datetimeField: {type: 'datetime'},
                    relateField1: {
                        type: 'relate',
                        module: 'ModuleB',
                        rname: 'rField1',
                        enum_module: 'AAA'
                    },
                    relateField2: {
                        type: 'relate',
                        module: 'ModuleB',
                        rname: 'rField2',
                        related_fields: ['fieldA']
                    },
                    relateField3: {
                        type: 'relate',
                        module: 'ModuleB',
                        rname: 'rField2',
                        id_name: 'fieldA'
                    }
                })
                .withArgs('ModuleB', 'fields')
                .returns({
                    rField1: {type: 'enum'},
                    rField2: {type: 'bool'}
                });
        });

        using('various module settings', [{
            modulename: 'ModuleA',
            element: '<li fieldname="nameField" fieldlabel="LBL_AAA"></li>',
            expected: {name: 'nameField', label: 'LBL_AAA', link: false, type: 'name'}
        },{
            modulename: 'ModuleA',
            element: '<li fieldname="datetimeField" fieldlabel="LBL_AAA"></li>',
            expected: {name: 'datetimeField', label: 'LBL_AAA', type: 'datetime'}
        },{
            modulename: 'ModuleA',
            element: '<li fieldname="relateField1" fieldlabel="LBL_AAA"></li>',
            expected: {
                name: 'relateField1',
                label: 'LBL_AAA',
                type: 'enum',
                enum_module: 'ModuleB',
                link: false
            }
        },{
            modulename: 'ModuleA',
            element: '<li fieldname="relateField2" fieldlabel="LBL_AAA"></li>',
            expected: {
                name: 'relateField2',
                label: 'LBL_AAA',
                module: 'ModuleB',
                related_fields: ['fieldA'],
                link: false,
                type: 'relate'
            }
        },{
            modulename: 'ModuleA',
            element: '<li fieldname="relateField3" fieldlabel="LBL_AAA"></li>',
            expected: {
                name: 'relateField3',
                label: 'LBL_AAA',
                module: 'ModuleB',
                related_fields: ['fieldA'],
                link: false,
                type: 'relate'
            }
        }], function(value) {
            it('should build a proper regular field definition', function() {
                var fieldObj = {};
                view.buildRegularField(value.element, fieldObj, value.modulename);
                expect(fieldObj).toEqual(value.expected);
            });
        });
    });

    describe('validateCollection', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model('ConsoleConfiguration');
            sinon.stub(view, 'getFields').callsFake(function() {
                return {
                    name: {
                        type: name
                    }
                };
            });

            view.collection = {
                models: [model],
                off: function() {}
            };
            sinon.stub(model, 'doValidate').callsFake(function() {});
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function() {});

            view.validateCollection();
        });

        it('should call view.getFields method', function() {

            expect(view.getFields).toHaveBeenCalledWith(view.module, view.model);
        });

        it('should call app.acl.hasAccessToModel method', function() {

            expect(app.acl.hasAccessToModel).toHaveBeenCalledWith('edit', view.model, 'name');
        });

        it('should call model.doValidate method', function() {

            expect(model.doValidate).toHaveBeenCalled('name');
        });
    });
});

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
describe('Metrics.View.RecordHeaderButtons', function() {
    var app;
    var view;
    var context;
    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        view = SugarTest.createView('base', 'Metrics', 'record-header-buttons', null, null, true);
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
            sinon.stub(view.context, 'get').withArgs('create').returns(true);
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
                    view.model = new Backbone.Model({});
                    view.cancelConfig();

                    expect(app.drawer.close).toHaveBeenCalledWith(view.context, view.model);
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

    describe('_beforeSaveConfig', function() {
        var model;
        var setStub;

        beforeEach(function() {
            model = new Backbone.Model('Metrics');
            setStub = sinon.stub(model, 'set');
            view.model = model;
        });

        it('should call model.set', function() {
            view._beforeSaveConfig();
            expect(setStub).toHaveBeenCalled();
        });
    });

    describe('_saveConfig', function() {
        beforeEach(function() {
            sinon.stub(view, 'validateModel').callsFake(function() {});
            view._saveConfig();
        });

        it('should set view.validateModel', function() {

            expect(view.validateModel).toHaveBeenCalled();
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
});

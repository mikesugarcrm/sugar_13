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
describe('Plugins.JSTree', function() {
    var module = 'KBContents',
        fieldDef = {
            category_root: '0',
            module_root: module
        },
        app, field, renderTreeStub, sinonSandbox, treeData, $fixture;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'nestedset', module);
        SugarTest.loadFile(
            '../modules/Categories/clients/base/plugins',
            'JSTree',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadFile(
            '../modules/Categories/clients/base/plugins',
            'NestedSetCollection',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });

        SugarTest.loadHandlebarsTemplate('nestedset', 'field', 'base', 'edit', module);

        SugarTest.testMetadata.set();
        app.data.declareModels();

        treeData = SugarTest.loadFixture('tree', '../tests/modules/Categories/fixtures');

        field = SugarTest.createField('base', 'nestedset', 'nestedset', 'edit', fieldDef, module, null, null, true);
        renderTreeStub = sinonSandbox.stub(field, '_renderTree');

        $fixture = $('<div id="Tree.Fixture">').appendTo('body');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        delete app.plugins.plugins['field']['JSTree'];
        delete app.plugins.plugins['field']['NestedSetCollection'];
        sinonSandbox.restore();
        $fixture.remove();
    });

    it('Tree add node.', function() {
        field.render();
        field.jsTree = {
            jstree: function() {}
        };

        var jstreeStub = sinonSandbox.stub(field.jsTree, 'jstree');

        field.addNode('test', 'last');
        expect(jstreeStub).toHaveBeenCalledWith('create');
    });

    it('Tree Search.', function() {
        field.render();
        field.jsTree = {
            jstree: function() {}
        };
        var jstreeStub = sinonSandbox.stub(field.jsTree, 'jstree');

        field.searchNode('valid string');
        expect(jstreeStub).toHaveBeenCalledWith('search');
    });

    it('Move Node should call different collection methods.', function() {
        field.render();
        var collectionMoveBeforeStub = sinonSandbox.stub(field.collection, 'moveBefore'),
            collectionMoveLastStub = sinonSandbox.stub(field.collection, 'moveLast');

        field.moveNode(1, 2, 'before');
        expect(collectionMoveBeforeStub).toHaveBeenCalled();

        field.moveNode(1, 2, 'last');
        expect(collectionMoveLastStub).toHaveBeenCalled();

        collectionMoveLastStub.resetHistory();
        field.moveNode(1, 2);
        expect(collectionMoveLastStub).toHaveBeenCalled();
    });

    it('On sync complete should rerender field.', function() {
        var nestedCollection = new app.NestedSetCollection(treeData);
        var renderStub = sinonSandbox.stub(field, 'render');

        nestedCollection.root = 1;
        field.collection.root = 1;
        field.onNestedSetSyncComplete(nestedCollection);
        expect(renderStub).toHaveBeenCalled();

        renderStub.restore();
        field.collection.root = 1;
        nestedCollection.root = 2;
        renderStub = sinonSandbox.stub(field, 'render');

        field.onNestedSetSyncComplete(nestedCollection);
        expect(renderStub).not.toHaveBeenCalled();
    });

    it('Rename should affect nested collection.', function() {
        var name = 'FakeName',
            id = '1',
            jsTreeData = {
                rslt: {
                    name: name,
                    obj: {
                        data: function(arg) {
                            if (arg == 'id') {
                                return id;
                            }
                        }
                    }
                }
            };
        field.collection = new app.NestedSetCollection(treeData);

        field._renameNodeHandler(null, jsTreeData);
        expect(field.collection.getChild(id).get('name')).toEqual(name);
    });

    it('Delete should show alert.', function() {
        field.collection = new app.NestedSetCollection(treeData);
        var menuObj = field._loadContextMenu({showMenu: true, acl: {}});
        var alertStub = sinonSandbox.stub(app.alert, 'show');

        menuObj.delete.action({
            data: function(arg) {
                return '1';
            }
        });
        expect(alertStub).toHaveBeenCalled();
    });

    it('Delete model.', function() {
        var fakeModel = new Backbone.Model();
        var destroyStub = sinonSandbox.stub(fakeModel, 'destroy');
        fakeModel.module = module;
        fakeModel.children = {};

        field.deleteModel({model: fakeModel});
        expect(destroyStub).toHaveBeenCalledOnce();
    });

    it('Submenu should be built from nested collection.', function() {
        field.collection = new app.NestedSetCollection(treeData);

        var menu = field._buildRootsSubmenu();
        expect(menu.movetosubmenu0.id).toEqual('1');
        expect(menu.movetosubmenu0.label).toEqual('First');
        expect(menu.movetosubmenu1.id).toEqual('2');
        expect(menu.movetosubmenu1.label).toEqual('Second');
    });

    it('Create handler.', function() {
        field.collection = new app.NestedSetCollection(treeData);
        field.root = '1';
        var appendStub = sinonSandbox.stub(field.collection, 'append');
        var jsTreeData = {
            args: [{}],
            rslt: {
                parent: -1,
                position: 1,
                name: 'fakeName',
                obj: {}
            }
        };
        field._createHandler(null, jsTreeData);
        expect(appendStub).toHaveBeenCalled();
    });

    it('Input should have tooltip when focused', function() {
        sinonSandbox.stub(Modernizr, 'touch').callsFake(false);
        app.tooltip.init();

        field.$treeContainer = $fixture;
        field.$noData = $('<div />');
        field.jsTreeSettings = {
            plugins: []
        };

        $fixture.jstree = function () {
        };
        var jsTreeStub = sinonSandbox.stub($fixture, 'jstree');

        var jsTree = $('<div />');
        jsTreeStub.returns(jsTree);

        field.createTree(treeData, $fixture);

        var data = {
            obj: $('<div />'),
            h1: $('<h1 />'),
            h2: $('<h2 />')
        };
        $('<input />').appendTo(data.obj);
        data.obj.appendTo($fixture);

        jsTree.trigger('show_input.jstree', data);

        var $inputs = $(_.filter($(data.obj).find('input'), function (input) {
            return $(input).css('display') !== 'none';
        }));

        $inputs.trigger('focus');

        expect($('.tooltip').length).toBe($inputs.length, 'Tooltip has not been shown');

        // Removing component must remove tooltips and it's data
        field.onDetach();
        expect($inputs.data('bs.tooltip')).not.toBeDefined('Tooltip data has not been removed');
        expect($('.tooltip').length).toBe(0, 'Tooltip has not been hidden when field removed');
    });

    it('Proper tree node should be removed even if after Confirm another node clicked.', function() {
        field.collection = new app.NestedSetCollection(treeData);

        sinonSandbox.stub(field, '_toggleVisibility');

        var alertStub = sinonSandbox.stub(app.alert, 'show');

        var id = 1;
        var model = field.collection.getChild(id);
        var destroyStub = sinonSandbox.stub(model, 'destroy');

        var obj = {
            data: function(arg) {
                return id;
            }
        };

        // emulates that other element has been chosen after Confirm clicked
        field.is_selected = function() {
            return false;
        };

        sinonSandbox.mock(field).expects('remove').once().withArgs(obj);

        // loading context menu and click on Delete
        var menuObj = field._loadContextMenu({showMenu: true, acl: {}});
        menuObj.delete.action.call(field, obj);

        // emulating clicking confirm button
        var callOptions = alertStub.args[0][1];
        callOptions.onConfirm();

        // emulating successful model delete
        var destroyOptions = destroyStub.args[0][0];
        destroyOptions.success();

        expect(alertStub).toHaveBeenCalled();
        expect(destroyStub).toHaveBeenCalled();
    });

    describe('Select Node Handler test.', function() {
        var jsTreeData;
        beforeEach(function() {
            jsTreeData = {
                args: [{}],
                rslt: {
                    name: 'fakeName',
                    obj: {
                        data: function() {
                            return 'fakeData';
                        },
                        find: function() {
                            return {
                                text: function() {
                                    return {
                                        trim: function() {
                                            return 'fakeName';
                                        }
                                    };
                                }
                            };
                        },
                        hasClass: function() {
                            return false;
                        }
                    }
                }
            };
            field.jsTreeCallbacks = {};
        });
        afterEach(function() {
            field.jsTreeCallbacks = null;
            jsTreeData = null;
            sinonSandbox.restore();
        });

        using('Actions provider.', [
            {
                action: 'jstree-toggle',
                callback: 'onToggle',
                default: '_jstreeToggle'
            },
            {
                action: 'jstree-leaf-click',
                callback: 'onLeaf',
                default: null
            },
            {
                action: 'jstree-contextmenu',
                callback: 'onShowContextmenu',
                default: '_jstreeShowContextmenu'
            },
            {
                action: 'jstree-addnode',
                callback: 'onAdd',
                default: '_onAdd'
            },
            {
                action: 'jstree-select',
                callback: 'onSelect',
                default: '_jstreeSelectNode'
            }
        ], function(value) {
            it('Select node handler should call appropriate callbacks.', function() {
                $(jsTreeData.args[0]).data('action', value.action);

                if (value.default) {
                    var defaultCallbackStub = sinonSandbox.stub(field, value.default);
                    field._selectNodeHandler(null, jsTreeData);
                    expect(defaultCallbackStub).toHaveBeenCalled();
                }

                field.jsTreeCallbacks[value.callback] = function() {
                };
                var callbackStub = sinonSandbox.stub(field.jsTreeCallbacks, value.callback);
                field._selectNodeHandler(null, jsTreeData);
                expect(callbackStub).toHaveBeenCalled();
            });
        });

    });

});

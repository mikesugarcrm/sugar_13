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
describe('SugarLive.View.ConfigPreviewView', function() {
    var app;
    var view;
    var layout;
    var context;
    var ctxModel;
    var parentLayout;

    function createListDom() {
        var ul = document.createElement('ul');
        ul.setAttribute('module_name', 'Calls');
        var li1 = document.createElement('li');
        li1.setAttribute('fieldname', 'name');
        li1.setAttribute('fieldlabel', 'LBL_SUBJECT');
        var li2 = document.createElement('li');
        li2.setAttribute('fieldname', 'description');
        li2.setAttribute('fieldlabel', 'LBL_DESC');
        ul.appendChild(li1);
        ul.appendChild(li2);
        return ul;
    }

    beforeEach(function() {
        app = SUGAR.App;

        context = app.context.getContext();
        ctxModel = app.data.createBean('SugarLive');
        context.set('model', ctxModel);
        context.set('enabledModules', ['Calls', 'Messages']);
        context.set('collection', app.data.createBeanCollection('SugarLive'));

        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        parentLayout = SugarTest.createLayout('base', null, 'base');
        layout = SugarTest.createLayout('base', 'SugarLive', 'config-drawer', {}, context);
        layout.name = 'side-pane';
        layout.layout = parentLayout;

        view = SugarTest.createView('base', 'SugarLive', 'config-preview', {}, context, true, layout);
        view.model.set('enabled_modules', ['Calls']);

        var callsModel = app.data.createBean('SugarLive');
        callsModel.set('enabled_module', 'Calls');
        callsModel.set('selected_fields', ['name', 'description']);
        view.collection.models = [callsModel];
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('bindDataChange', function() {
        it('should call view.collection.on', function() {
            sinon.stub(view.collection, 'on').callsFake(function() { });
            view.bindDataChange();
            expect(view.collection.on).toHaveBeenCalledWith('add remove reset preview');
        });
    });

    describe('getAvailableModules', function() {
        it('should return only the modules that have metadata', function() {
            var metaStub = sinon.stub(app.metadata, 'getModule');
            metaStub.withArgs('Calls').returns(undefined);
            metaStub.withArgs('Messages').returns({prop: 'test'});
            var results = view.getAvailableModules();
            expect(results).toEqual(['Messages']);
        });
    });

    describe('setPreviewTabs', function() {
        it('should create the template for the preview objects', function() {
            var titleStub = sinon.stub(view, 'setPreviewTitle');
            var fieldStub = sinon.stub(view, 'setPreviewFields');
            sinon.stub(app.metadata, 'getModule').returns({prop: 'test'});
            view.setPreviewTabs();

            expect(view.tabs).toEqual({
                'Calls': {module: 'Calls'},
                'Messages': {module: 'Messages'},
            });
            expect(view.tabsLength).toEqual(2);
            expect(titleStub).toHaveBeenCalled();
            expect(fieldStub).toHaveBeenCalled();
        });

        it('should set the tab titles', function() {
            view.tabs.Calls = {};
            var langStub = sinon.stub(app.lang, 'get');
            langStub.withArgs('LBL_SUGARLIVE_PREVIEW').returns('Summary');
            langStub.withArgs('LBL_SUGARLIVE_SUMMARY_PREVIEW').returns('Summary Preview');
            sinon.stub(app.lang, 'getModuleName').withArgs('Calls').returns('Call');

            view.setPreviewTitle('Calls');

            expect(view.tabs.Calls.title).toEqual('Call Summary Preview');
            expect(view.tabs.Calls.detailTitle).toEqual('Call Summary');
        });

        it('should add the fields to the tabs list', function() {
            var ul = createListDom();
            sinon.stub(app.metadata, 'getField').returns(
                {
                    name: {name: 'name', label: 'LBL_NAME', type: 'name'},
                    description: {name: 'description', label: 'LBL_DESC', type: 'textarea'}
                }
            );
            sinon.stub(document, 'querySelector')
                .withArgs('.drawer.active #Calls-side .field-list').returns(ul);
            sinon.stub(app.metadata, 'getModule').returns({prop: 'test'});
            sinon.stub(app.metadata, '_patchFields').callsFake(function(module, meta, fields) {
                return _.reduce(fields, function(memo, field) {
                    memo.push({name: field});
                    return memo;
                }, []);
            });

            var expectedFields = [{
                name: 'name', label: 'LBL_NAME', type: 'name', module: ' ', options: []
            }, {
                name: 'description', label: 'LBL_DESC', type: 'textarea', module: ' ', options: []
            }];
            view.setPreviewTabs();

            expect(view.tabs.Calls.fields).toEqual(expectedFields);
        });
    });

    describe('render', function() {
        it('should call render specific methods', function() {
            var initStub = sinon.spy(view, 'initTabs');
            var disableStub = sinon.stub(view, 'disableInputs');

            view.render();
            expect(initStub).toHaveBeenCalled();
            expect(disableStub).toHaveBeenCalled();
        });

        it('should disable all inputs', function() {
            $(view.$el).append($('<div class="omni-cell"><input/><input/></div>'));
            view.disableInputs();
            var disabledInputs = $(view.$el).find('input[readonly=true]');
            expect(disabledInputs.length).toEqual(2);
        });

        it('should save the new active tab', function() {
            var ctxStub = sinon.stub(view.context, 'set');
            sinon.stub(view, '_getSidePanes');
            view.setTabsDisplay();
            expect(ctxStub).toHaveBeenCalled();
            expect(view._getSidePanes).toHaveBeenCalled();
        });
    });

    describe('_getSidePanes', function() {
        var jqstub = sinon.stub();
        using('different closestComponent return values', [
            {compoment: false, expected: false},
            {component: {$: jqstub}, expected: true}
        ], function(values) {
            it('should call jquery on config-drawer component if it exists', function() {
                sinon.stub(view, 'closestComponent')
                .withArgs('config-drawer')
                .returns(values.component);
                jqstub.resetHistory();
                view._getSidePanes();
                expect(jqstub.called).toEqual(values.expected);
            });
        });
    });
});


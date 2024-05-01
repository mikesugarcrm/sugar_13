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
describe('Base.View.DupeCheckList', function() {
    var app,
        moduleName = 'Contacts',
        listMeta,
        layout,
        createBeanStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'selection-list');
        SugarTest.loadComponent('base', 'view', 'dupecheck-list');
        SugarTest.testMetadata.init();
        listMeta = {
            'template': 'list',
            'panels':[
                {
                    'name':'panel_header',
                    'fields':[
                        {
                            'name':'first_name'
                        },
                        {
                            'name':'name'
                        },
                        {
                            'name':'status'
                        }
                    ]
                }
            ]
        };
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', 'Cases', 'list', null, null);
        createBeanStub = sinon.stub(app.data, 'createBean').callsFake(function() {
            var bean = new app.Bean();
            bean.copy = $.noop;
            sinon.stub(bean, 'copy').callsFake(function(sourceBean) {
                bean.set(sourceBean.attributes);
            });
            return bean;
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        createBeanStub.restore();
    });

    it('should turn off sorting on all fields', function(){
        var view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta);
        view.layout = layout;
        view.render();

        expect(view.$('.sorting').length).toBe(0);
        expect(view.$('.sorting_asc').length).toBe(0);
        expect(view.$('.sorting_desc').length).toBe(0);
    });

    it('should removing all links except rowactions', function(){
        var htmlBefore = '<a href="javascript:void(0)">unwrapped</a><a class="rowaction" href="">wrapped</a>';
        var htmlAfter = 'unwrapped<a class="rowaction" href="">wrapped</a>';
        var view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta);

        view.layout = layout;
        view.$el = $('<div>' + htmlBefore + '</div>');
        view.render();
        expect(view.$el.html()).toEqual(htmlAfter);
    });

    // FIXME: Should refactor following case on FindDuplicates.js (Filed on SC-1764)
    xit('should be able to set the model via context', function(){
        var model, context, view;

        model = new Backbone.Model();
        model.set('foo', 'bar');
        context = app.context.getContext({
            module: moduleName,
            dupeCheckModel: model
        });
        context.prepare();

        view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta, context);
        view.layout = layout;
        expect(view.model.get('foo')).toEqual('bar');
        expect(view.model.copy.callCount).toBe(1);
    });

    // FIXME: Should refactor following case on FindDuplicates.js (Filed on SC-1764)
    xit('should be calling the duplicate check api', function() {
        var ajaxStub;
        var view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta);
        view.layout = layout;

        //mock out collectionSync which gets called by overridden sync
        view.collectionSync = function(method, model, options) {
            options.endpoint(options, {'success':$.noop});
        };

        ajaxStub = sinon.stub($, 'ajax').callsFake($.noop);

        view.fetchDuplicates(new Backbone.Model());
        expect(ajaxStub.lastCall.args[0].url).toMatch(/.*\/Contacts\/duplicateCheck/);

        ajaxStub.restore();
    });
});

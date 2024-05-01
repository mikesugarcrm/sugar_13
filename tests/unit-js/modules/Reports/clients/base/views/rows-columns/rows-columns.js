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
describe('Reports.Base.Views.ReportsRowsColumnsView', function() {
    var app;
    var view;
    var context;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        app.data.declareModels();

        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'rows-columns', 'Reports');
        SugarTest.loadComponent('base', 'layout', 'rows-columns', 'Reports');

        SugarTest.loadHandlebarsTemplate('rows-columns', 'view', 'base', 'row', 'Reports');
        SugarTest.loadHandlebarsTemplate('rows-columns', 'view', 'base', 'row-header', 'Reports');
        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.set('data', {header: [], records: []});
        context.set('previewMode', false);
        context.prepare();

        sinon.stub(app.api, 'call').callsFake(function() {});
        sinon.stub(Backbone.history, 'getFragment').callsFake(function() {return 'Reports';});

        layout = SugarTest.createLayout('base', 'Reports', 'rows-columns', {});
        view = SugarTest.createView('base', 'Reports', 'rows-columns', {}, context, true, layout);

        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        layout.dispose();
        app = null;
        view.context = null;
        view.model = null;
        view = null;
    });

    describe('initialize', function() {
        var options;

        beforeEach(function() {
            options = {};
            view.initialize(options);
        });

        it('should call view._super method', function() {
            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should not have ReorderableColumns plugin', function() {
            expect(_.indexOf(view.plugins, 'ReorderableColumns')).toEqual(-1);
        });
    });

    describe('_initProperties', function() {
        it('should set the limit to be 50 by default', function() {
            view._initProperties();

            expect(view.limit).toEqual(50);
        });
    });

    describe('_loadTemplate', function() {
        it('should load templates from parent', function() {
            view._loadTemplate({});
            expect(view.tplName).toEqual('recordlist');
        });
    });
});

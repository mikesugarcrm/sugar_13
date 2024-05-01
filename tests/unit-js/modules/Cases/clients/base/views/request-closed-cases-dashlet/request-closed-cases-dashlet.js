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
describe('View.Views.Base.Cases.RequestClosedCasesDashlet', function() {
    var app;
    var layout;
    var view;
    var sandbox;
    var model;
    var context;
    var meta;
    var fields;
    var moduleName = 'Cases';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        fields = {
            case_number: {
                name: 'case_number',
                required: true,
                type: 'text',
                vname: 'LBL_CASE_NUMBER',
                link: false,
            },
            name: {
                name: 'name',
                required: true,
                type: 'name',
                vname: 'LBL_NAME',
                link: true,
            },
            priority: {
                name: 'priority',
                required: true,
                type: 'enum',
                vname: 'LBL_PRIORITY',
                link: false,
            },
            status: {
                name: 'status',
                required: true,
                type: 'enum',
                vname: 'LBL_STATUS',
                link: false,
            },
            date_modified: {
                name: 'date_modified',
                required: true,
                type: 'datetime',
                vname: 'LBL_DATE_MODIFIED',
                link: false,
            },
        };

        model = app.data.createBean(moduleName);
        model.fields = fields;

        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', model);
        context.parent = app.context.getContext({
            module: moduleName
        });
        context.prepare();

        meta = {
            config: false
        };

        sandbox.stub(context.parent, 'get').callsFake(function() {
            return new Backbone.Collection();
        });

        layout = SugarTest.createLayout('base', moduleName, 'list', null, context.parent);
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadComponent(
            'base',
            'view',
            'list',
            ''
        );
        SugarTest.loadHandlebarsTemplate(
            'request-closed-cases-dashlet',
            'view',
            'base',
            null,
            'Cases'
        );
        view = SugarTest.createView(
            'base',
            'Cases',
            'request-closed-cases-dashlet',
            meta,
            context,
            true,
            layout,
            true
        );
        sandbox.stub(view.collection, 'sync');
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        layout.dispose();
    });

    describe('Render when collection changed', function() {
        var renderStub;
        beforeEach(function() {
            renderStub = sandbox.stub(view, 'render');
            view.bindDataChange();
        });

        it('should render when added item to collection', function() {
            view.collection.trigger('add');
            expect(renderStub).toHaveBeenCalled();
        });

        it('should render when reset collection', function() {
            view.collection.trigger('reset');
            expect(renderStub).toHaveBeenCalled();
        });

        it('should render when removed item from collection', function() {
            view.collection.trigger('remove');
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('_initDisplayedFields', function() {
        it('should return the field objects', function() {
            var expected = _.values(fields);
            var actual = view._initDisplayedFields();

            expect(actual.length).toEqual(expected.length);
            for (var i = 0; i < actual.length; i++) {
                var expectedField = expected[i];
                var actualField = actual[i];
                expect(actualField.name).toEqual(expectedField.name);
                expect(actualField.required).toEqual(expectedField.required);
                expect(actualField.type).toEqual(expectedField.type);
                expect(actualField.vname).toEqual(expectedField.vname);
                expect(actualField.link).toEqual(expectedField.link);
            }
        });
    });
});

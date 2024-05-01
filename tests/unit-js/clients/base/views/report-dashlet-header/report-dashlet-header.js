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
describe('Base.View.ReportDashletHeader', function() {
    var app;
    var context;
    var layout;
    var view;
    var initOptions;
    var sandbox = sinon.createSandbox();
    var viewName = 'report-dashlet-header';

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();
        context.set({
            model: new Backbone.Model(),
            module: 'Home',
        });

        context.parent = new app.Context({
            module: 'Home',
            model: new Backbone.Model({
                defaultSelectView: 'list',
            }),
        });

        layout = SugarTest.createLayout(
            'base',
            'Home',
            'list',
            null,
            context.parent
        );

        initOptions = {
            context: context.parent
        };

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', viewName);
        SugarTest.loadComponent('base', 'view', viewName);
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();
        layout.dispose();
        app = null;
        view = null;
        layout = null;
    });

    describe('initialize()', function() {
        var testView;
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView(
                'base',
                null,
                viewName,
                null,
                context,
                true,
                layout,
                true
            );

            sandbox.spy(testView, '_initProperties');

            testView.initialize(initOptions);

        });

        it('should init the default properties, also the properties from layout model', function() {
            expect(testView._selectedView).toEqual('list');
        });

        it('it should call the _initProperties and _registerEvents', function() {
            expect(testView._initProperties.called).toEqual(true);
        });

        afterEach(function() {
            testView.dispose();
        });
    });

    describe('onClick()', function() {
        var testView;
        var clickArg;

        beforeEach(function() {
            testView = SugarTest.createView(
                'base',
                null,
                viewName,
                null,
                context,
                true,
                layout,
                true
            );

            sandbox.spy(testView.context, 'trigger');

            clickArg = {
                currentTarget: {
                    getAttribute: function() {
                        return 'chart';
                    },
                },
            };
        });

        it('show change _selectedView value', function() {
            expect(testView._selectedView).toBe('list');

            testView.onClick(clickArg);

            expect(testView._selectedView).toBe('chart');
        });

        it('show change trigger report-dashlet:change:view-type on context', function() {
            testView.onClick(clickArg);

            expect(testView.context.trigger).toHaveBeenCalledWith('report-dashlet:change:view-type');
        });

        afterEach(function() {
            testView.dispose();
        });
    });
});

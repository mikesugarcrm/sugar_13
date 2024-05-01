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
describe('DRI_Workflows.Base.View.CreateView', function() {
    var app;
    var view;
    var context;
    var initOptions;
    var meta;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.app.data.declareModels();

        context = new app.Context();

        context.set('model', new Backbone.Model());

        context.prepare();
        context.parent = app.context.getContext();

        layout = SugarTest.createLayout(
            'base',
            'DRI_Workflows',
            'base',
            null,
            context
        );

        meta = {};

        view = SugarTest.createView(
            'base',
            'DRI_Workflows',
            'create',
            meta,
            context,
            true,
            layout,
            true
        );

        initOptions = {
            context: context,
            meta: meta,
        };
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', () => {
        it('should set alert object', () => {
            view.initialize(initOptions);
            expect(Object.keys(view.alerts).includes('parentNotFoundException')).toBe(true);
        });
    });

    describe('getCustomSaveOptions', () => {
        let cl;
        let cv;

        beforeEach(function() {
            SugarTest.loadComponent('base', 'view', 'create');

            cl = SugarTest.createLayout(
                'base',
                'layout',
                'base',
                null,
                context
            );
            cv = SugarTest.createView(
                'base',
                'view',
                'create',
                meta,
                context,
                true,
                cl,
                true
            );
        });
        afterEach(function() {
            cv.dispose();
            cl.dispose();
        });
        it('should set error callback', () => {
            view.initialize(initOptions);
            cv.initialize(initOptions);
            sinon.stub(view.alerts.parentNotFoundException, 'call').returns(true);
            sinon.stub(cv, 'enableButtons').returns(true);
            view.enableButtons = cv.enableButtons;
            view.getCustomSaveOptions(view.options);
            view.options.error(null, {status: 404});
            expect(view.alerts.parentNotFoundException.call).toHaveBeenCalled();
        });
    });

    describe('getTemplate', () => {
        it('should call the api', () => {
            sinon.stub(app.api, 'call').returns(true);
            view.getTemplate(view.options);
            expect(app.api.call).toHaveBeenCalled();
        });
    });
});

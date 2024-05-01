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
describe('View.Views.Base.CjAsADashletView', function() {
    let app;
    let view;
    let model;
    let context;
    let layout;
    let initOptions;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean('Accounts');
        SugarTest.loadComponent('base', 'view', 'cj-as-a-dashlet');
        SugarTest.app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadPlugin('CJAsPanelOrTab', 'customer-journey');
        context = new app.Context();
        context.set('model', new Backbone.Model());
        context.prepare();
        context.parent = app.context.getContext();
        context.parent.parent = app.context.getContext();
        layout = SugarTest.createLayout(
            'base',
            '',
            'base',
            null,
            context
        );
        view = SugarTest.createView(
            'base',
            '',
            'cj-as-a-dashlet',
            null,
            context,
            true,
            layout,
            true
        );

        initOptions = {
            context: context,
        };
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();
        model = null;
        context = null;
        layout = null;
        view = null;
    });

    describe('initialize', function() {
        it('should call the initialize', function() {
            sinon.stub(app.template, 'get');
            view.initialize(initOptions);
            expect(app.template.get).toHaveBeenCalled();
        });
    });

    describe('_getModule', function() {
        it('should call the _getModule', function() {
            sinon.stub(view.context.parent.parent, 'get').returns('Accounts');
            let result = view._getModule();
            expect(result).toBe('Accounts');
            expect(view.context.parent.parent.get).toHaveBeenCalled();
        });
    });

    describe('_isMatchedDashBoardType', function() {
        it('should call the _isMatchedDashBoardType', function() {
            let dashboardType = 'focus';
            sinon.stub(view.context.parent.parent, 'get').returns('focus');
            let result = view._isMatchedDashBoardType(dashboardType);
            expect(result).toBe(true);
            expect(view.context.parent.parent.get).toHaveBeenCalled();
        });
    });

    describe('_render', function() {
        it('should return false if user has no access to template', function() {
            view._noAccessTemplate = function() {
                return true;
            };
            sinon.stub(view, '_isMatchedDashBoardType').returns(true);
            sinon.stub(view, '_setCurrentModel');
            sinon.stub(view.$el, 'html');
            let result = view._render();
            expect(result).toBe(false);
            expect(view._isMatchedDashBoardType).toHaveBeenCalled();
            expect(view._setCurrentModel).toHaveBeenCalled();
            expect(view.$el.html).toHaveBeenCalled();
        });
    });

    describe('_setCurrentModel', function() {
        it('should call the _setCurrentModel', function() {
            sinon.stub(view.context.parent.parent, 'get').returns(model);
            view._setCurrentModel();
            expect(view.model).toBe(model);
            expect(view.context.parent.parent.get).toHaveBeenCalled();
        });
    });

    describe('refreshClicked', function() {
        it('should call the refreshClicked', function() {
            sinon.stub(view, 'render');
            view.refreshClicked();
            expect(view.render).toHaveBeenCalled();
        });
    });
});

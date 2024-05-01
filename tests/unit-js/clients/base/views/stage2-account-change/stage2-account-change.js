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
describe('Base.View.Stage2AccountChange', function() {
    var app;
    var view;
    var module;
    var context;

    beforeEach(function() {
        app = SugarTest.app;

        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.prepare();

        module = 'Accounts';

        view = SugarTest.createView('base', module, 'stage2-account-change');

        options = {
            context: context
        };
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize()', function() {
        afterEach(function() {
            options = null;
        });

        it('should add listeners', function() {
            expect(view.events['change input[name=select]']).toEqual('isSelected');
            expect(view.events['click .save']).toEqual('savePrimary');
            expect(view.events['click .cancel']).toEqual('cancel');
            expect(view.events['mouseenter [rel="tooltip"]']).toEqual('showTooltip');
            expect(view.events['mouseleave [rel="tooltip"]']).toEqual('hideTooltip');
        });

        it('should set forceNew to be true', function() {
            expect(view.context.get('forceNew')).toBe(true);
        });
    });

    describe('_render()', function() {
        it('should render the view', function() {
            var renderStub = sinon.stub(view, '_render');
            view._render();
            expect(renderStub).toHaveBeenCalledOnce();
            renderStub.restore();
        });
    });
});

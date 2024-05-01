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
describe('View.Views.Base.QuicksearchResultsView', function() {
    var viewName = 'quicksearch-results',
        view, layout, attachKeyDownStub, disposeKeydownStub,
        triggerBeforeStub, triggerSpy;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', undefined, viewName, null, null, null, layout);

        attachKeyDownStub = sinon.stub(view, 'attachKeydownEvent');
        disposeKeydownStub = sinon.stub(view, 'disposeKeydownEvent');
        triggerBeforeStub = sinon.stub(view.layout, 'triggerBefore').callsFake(function() {
            return true;
        });
        triggerSpy = sinon.spy(view.layout, 'trigger');
        var app = SUGAR.App;
        view.collection = app.data.createMixedBeanCollection();
        view.collection.models = [0, 1, 2, 3, 4];
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.restore();
        layout.dispose();
        layout = null;
        view = null;
    });

    describe('quicksearch:close', function() {
        var closeStub;
        beforeEach(function() {
            closeStub = sinon.stub(view, 'close');
        });

        it('should call disposeKeydownEvent and close', function() {
            view.layout.trigger('quicksearch:close');
            expect(closeStub).toHaveBeenCalled();
        });
    });

    describe('quicksearch:results:close', function() {
        var closeStub;
        beforeEach(function() {
            closeStub = sinon.stub(view, 'close');
        });

        it('should call disposeKeydownEvent and close', function() {
            view.layout.trigger('quicksearch:results:close');
            expect(closeStub).toHaveBeenCalled();
        });
    });

    describe('navigate:focus:receive', function() {
        it('should set the first element active and attachKeydownEvent', function() {
            view.trigger('navigate:focus:receive', true);
            expect(attachKeyDownStub).toHaveBeenCalled();
            expect(view.activeIndex).toEqual(0);
        });

        it('should set the last element active and attachKeydownEvent', function() {
            view.trigger('navigate:focus:receive', false);
            expect(attachKeyDownStub).toHaveBeenCalled();
            expect(view.activeIndex).toEqual(4);
        });
    });

    describe('navigate:focus:lost', function() {
        it('should clear the activeIndex and disposeKeydownEvent', function() {
            view.trigger('navigate:focus:lost');
            expect(disposeKeydownStub).toHaveBeenCalled();
            expect(view.activeIndex).toBeNull();
        });
    });

    describe('isFocusable', function() {
        it('should be focusable with results and records', function() {
            var isFocusable = view.isFocusable();
            expect(isFocusable).toBeTruthy();
        });
    });

    describe('moveForward', function() {
        it('should increment the active index if we are in bounds', function() {
            view.activeIndex = 0;
            view.moveForward();
            expect(view.activeIndex).toEqual(1);
        });

        it('should move to the next component if we are out of bounds', function() {
            view.activeIndex = 4;
            view.moveForward();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:next:component');
            expect(disposeKeydownStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:next:component');
        });
    });

    describe('moveBackward', function() {
        it('should decrement the active index if we are in bounds', function() {
            view.activeIndex = 4;
            view.moveBackward();
            expect(view.activeIndex).toEqual(3);
        });

        it('should move to the previous component if we are out of bounds', function() {
            view.activeIndex = 0;
            view.moveBackward();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:previous:component');
            expect(disposeKeydownStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:previous:component');
        });
    });
});

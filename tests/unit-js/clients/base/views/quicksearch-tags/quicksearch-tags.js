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
describe('View.Views.Base.QuicksearchTagsView', function() {
    var viewName = 'quicksearch-tags',
        view, layout, attachKeyEventsStub,
        triggerBeforeStub, triggerSpy, disposeKeyEventsStub;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', undefined, viewName, null, null, null, layout);

        attachKeyEventsStub = sinon.stub(view, 'attachKeyEvents');
        disposeKeyEventsStub = sinon.stub(view, 'disposeKeyEvents');
        triggerBeforeStub = sinon.stub(view.layout, 'triggerBefore').callsFake(function() {
            return true;
        });
        triggerSpy = sinon.spy(view.layout, 'trigger');
        var app = SUGAR.App;
        view.collection = app.data.createMixedBeanCollection();
        view.tagCollection = [{id: 1, name: 'tag1'}, {id: 2, name: 'tag2'}];
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

        it('should call disposeKeyEvents and close', function() {
            view.layout.trigger('quicksearch:close');
            expect(closeStub).toHaveBeenCalled();
            expect(disposeKeyEventsStub).toHaveBeenCalled();
            expect(view.activeIndex).toBeNull();
        });
    });

    describe('quicksearch:results:close', function() {
        var closeStub;

        beforeEach(function() {
            closeStub = sinon.stub(view, 'close');
        });

        it('should call disposeKeyEvents and close', function() {
            view.layout.trigger('quicksearch:results:close');
            expect(closeStub).toHaveBeenCalled();
            expect(disposeKeyEventsStub).toHaveBeenCalled();
            expect(view.activeIndex).toBeNull();
        });
    });

    describe('navigate:focus:receive', function() {
        it('should set the first element active and attachKeydownEvent', function() {
            view.trigger('navigate:focus:receive');
            expect(attachKeyEventsStub).toHaveBeenCalled();
            expect(view.activeIndex).toEqual(0);
        });
    });

    describe('navigate:focus:lost', function() {
        it('should clear the activeIndex and disposeKeydownEvent', function() {
            view.trigger('navigate:focus:lost');
            expect(disposeKeyEventsStub).toHaveBeenCalled();
            expect(view.activeIndex).toBeNull();
        });
    });

    describe('isFocusable', function() {
        it('should be focusable with tag', function() {
            var isFocusable = view.isFocusable();
            expect(isFocusable).toBeTruthy();
        });

        it ('should not be focusable without tags', function() {
            view.tagCollection = [];
            var isFocusable = view.isFocusable();
            expect(isFocusable).not.toBeTruthy();
        });
    });

    describe('moveRight', function() {
        it('should increment the count if we are in bounds', function() {
            view.activeIndex = 0;
            view.moveRight();
            expect(view.activeIndex).toEqual(1);
        });

        it('should not increment the count if we are at the end', function() {
            view.activeIndex = 1;
            view.moveRight();
            expect(view.activeIndex).toEqual(1);
        });
    });

    describe('moveLeft', function() {
        it('should decrement the count if we are in bounds', function() {
            view.activeIndex = 1;
            view.moveLeft();
            expect(view.activeIndex).toEqual(0);
        });

        it('should not increment the count if we are at the end', function() {
            view.activeIndex = 0;
            view.moveLeft();
            expect(view.activeIndex).toEqual(0);
        });
    });

    describe('moveDown', function() {
        it('should move to the next component', function() {
            view.moveDown();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:next:component');
            expect(disposeKeyEventsStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:next:component');
        });
    });

    describe('moveUp', function() {
        it('should move to the previous component', function() {
            view.moveUp();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:previous:component');
            expect(disposeKeyEventsStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:previous:component');
        });
    });

    describe('quicksearchHandler', function() {
        var openStub, closeStub, renderStub;

        beforeEach(function() {
            openStub = sinon.stub(view, 'open');
            closeStub = sinon.stub(view, 'close');
            renderStub = sinon.stub(view, 'render');
        });

        it('should call open if handler comes back with results', function() {
            var collection = {tags: [1, 2]};
            view.quicksearchHandler(collection);

            expect(openStub).toHaveBeenCalledOnce();
            expect(closeStub).not.toHaveBeenCalled();
        });

        it('should call close if handler comes back with no results', function() {
            var collection = {tags: []};
            view.quicksearchHandler(collection);

            expect(openStub).not.toHaveBeenCalled();
            expect(closeStub).toHaveBeenCalledOnce();
        });
    });

    describe('handleTagSelection', function() {
        it('should trigger event with tag1 as a param', function() {
            var tag1 = {name: 'tag1', id: 1};
            var tag2 = {name: 'tag2', id: 2};
            var event = {target: {text: 'tag1'}};

            view.tagCollection = [tag1, tag2];
            view.handleTagSelection(event);

            expect(triggerSpy).toHaveBeenCalled();
            expect(triggerSpy).toHaveBeenCalledWith('quicksearch:tag:add', tag1);
        });
    });
});

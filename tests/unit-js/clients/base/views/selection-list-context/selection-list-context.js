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
describe('Base.View.SelectionListContext', function() {
    var view, layout, app, moduleName, viewName, attrs1, attrs2, attrs3, bean1,
        bean2, bean3, event, renderStub, beansArray;
    beforeEach(function() {
        moduleName = 'Accounts';
        viewName = 'selection-list-context';
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', viewName);

        attrs1 = {id: '1', name: 'toto'};
        attrs2 = {id: '2', name: 'tata'};
        attrs3 = {id: '3', name: 'titi'};
        bean1 = app.data.createBean(moduleName, attrs1);
        bean2 = app.data.createBean(moduleName, attrs2);
        bean3 = app.data.createBean(moduleName, attrs3);

        var context = app.context.getContext();
        context.set({
            mass_collection: app.data.createBeanCollection(moduleName, [bean1, bean2, bean3]),
            collection: app.data.createBeanCollection(moduleName)
        });

        view = SugarTest.createView('base', moduleName, viewName, null, context, null, layout);
        renderStub = sinon.stub(view, 'render');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        view = null;
    });

    describe('Initialize:', function() {
        it('should initialize view properties', function() {
           expect(view.maxPillsDisplayed).toBeDefined();
           expect(view.pills).toBeDefined();
        });
    });

    describe('addPill:', function() {
        beforeEach(function() {
            beansArray = [bean1, bean2, bean3];
        });
        it('should add the models to the pills array', function() {
            view.addPill([bean1, bean2, bean3]);

            var expectedPillsArray = [attrs1, attrs2, attrs3];
            expect(_.isEqual(view.pills, expectedPillsArray)).toBe(true);
        });
        it('should render the view', function() {
            view.addPill(beansArray);

            expect(renderStub).toHaveBeenCalledOnce();
        });
    });

    describe('removePill:', function() {
        beforeEach(function() {
            view.pills = [attrs1, attrs2, attrs3];
        });
        it('should remove the passed models from the pills array', function() {

            view.removePill(bean1);

            var expectedPillsArray = [attrs2, attrs3];
            expect(!_.contains(view.pills, attrs1)).toBe(true);
            expect(_.isEqual(view.pills, expectedPillsArray)).toBe(true);

            view.removePill([bean2, bean3]);
            expect(_.isEmpty(view.pills)).toBe(true);
        });
        it('should render the view', function() {
            view.removePill([bean1, bean2]);

            expect(renderStub).toHaveBeenCalledOnce();
        });
    });

    describe('removeAllPill:', function() {
        beforeEach(function() {
            view.pills = [attrs1, attrs2, attrs3];
        });
        it('should render the view with no pills', function() {
            view.removeAllPills();

            expect(_.isEmpty(view.pills)).toBe(true);
            expect(renderStub).toHaveBeenCalledOnce();
        });
        it('should trigger a "mass_collection:clear" event', function() {
            var triggerStub = sinon.stub(view.context, 'trigger');
            view.removeAllPills();

            expect(triggerStub).toHaveBeenCalledWith('mass_collection:clear');

        });
    });

    describe('closePill:', function() {
        beforeEach(function() {
            view.massCollection = view.context.get('mass_collection');
            var pillHtml = ' <li class="select2-search-choice" data-id="'+ attrs1.id +'"><div>' +
                '<div class="ellipsis_inline" title="toto">toto</div>' +
                '</div><a class="select2-search-choice-close" data-close-pill="true" tabindex="-1"></a></li>';
            view.$el.append(pillHtml);

        });
        it('should remove the pill and trigger a "mass_collection:remove" event', function() {
            var removePillStub = sinon.stub(view, 'removePill');
            sinon.spy(view.context, 'trigger');

            view.closePill(bean1.id.toString());

            expect(removePillStub).toHaveBeenCalledWith({id: '1'});
            expect(view.context.trigger).toHaveBeenCalledWith('mass_collection:remove', bean1);
        });
    });

    describe('resetPills:', function() {
        it('should empty the pills array if the passed collection is empty', function() {
            view.resetPills(view.collection);

            expect(_.isEmpty(view.pills)).toBe(true);
            expect(renderStub).toHaveBeenCalledOnce();
        });

        it('should just rerender if the passed collection is not empty', function() {
            view.massCollection = view.context.get('mass_collection');
            view.pills = [attrs1];

            view.resetPills(view.massCollection);

            expect(_.isEmpty(view.pills)).toBe(false);
            expect(renderStub).toHaveBeenCalledOnce();
        });
    });
});


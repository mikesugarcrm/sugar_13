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
describe('Module Menu', function() {
    var moduleName = 'Cases',
        viewName = 'module-menu',
        app,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'favorites');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'recently-viewed');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', moduleName, 'module-menu', null, null);
        app.routing.start();
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        app.router.stop();
    });

    it('should populate recently viewed and favorites on menu open', function() {
        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                favoritesEnabled: true,
                fields: { name: {} }
            };
        });

        var favStub = sinon.stub(view.getCollection('favorites'), 'fetch').callsFake(function(options) {
            options.success.call(this, []);
        });

        var recentStub = sinon.stub(view.getCollection('recently-viewed'), 'fetch').callsFake(function(options) {
            options.success.call(this, []);
        });

        view.$el.trigger('shown.bs.dropdown');

        expect(favStub.calledOnce).toBeTruthy();
        expect(recentStub.calledOnce).toBeTruthy();
    });

    it('should not populate favorites on modules that don\'t support it', function() {

        var populateStub = sinon.stub(view, 'populate');

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return {fields: { name: {} }};
        });

        view.populateMenu();

        expect(populateStub).toHaveBeenCalledWith('recently-viewed', [{
            '$tracker': '-7 DAY'
        }], 3);
    });

    it('should be able to filter menu items by acl', function() {
        var meta;

        sinon.stub(SugarTest.app.acl, 'hasAccess').callsFake(function(action) {
            return action !== 'no-access';
        });

        meta = [{
            label: 'blah',
            acl_action: 'edit',
            module: moduleName
        },{
            label: 'blah',
            acl_action: 'no-access',
            module: moduleName
        },{
            label: 'blah',
            acl_action: 'read',
            module: moduleName
        }];

        expect(view.filterByAccess(meta)).toEqual([{
            label: 'blah',
            acl_action: 'edit',
            module: moduleName
        },{
            label: 'blah',
            acl_action: 'read',
            module: moduleName
        }]);

        SugarTest.app.acl.hasAccess.restore();
    });

    it('should trigger data event on click of action links', function() {
        var eventSpy = sinon.spy();

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return { menu: { header: {meta: [{
                label: 'LBL_MENU_1',
                module: moduleName,
                event: 'sugar:app:testEvent'
            }]}}};
        });


        SugarTest.app.events.register('sugar:app:testEvent', view);
        SugarTest.app.events.on('sugar:app:testEvent', eventSpy, view);

        view.render();
        view.$('[data-event]').click();

        expect(eventSpy).toHaveBeenCalledWith();

        SugarTest.app.events.unregister(view, 'sugar:app:testEvent');
    });

    it('should call refresh when data-route matches the current route', function() {
        let refreshStub = sinon.stub(SugarTest.app.router, 'refresh');
        let navigateStub = sinon.stub(SugarTest.app.router, 'navigate');

        sinon.stub(Backbone.history, 'getFragment').callsFake(function() {
            return moduleName;
        });

        view.render();
        view.$('[data-route]').click();

        expect(refreshStub).toHaveBeenCalled();
        expect(navigateStub).not.toHaveBeenCalled();
    });

    it('should call navigate when data-route is a new route', function() {
        let refreshStub = sinon.stub(SugarTest.app.router, 'refresh');
        let navigateStub = sinon.stub(SugarTest.app.router, 'navigate');

        sinon.stub(Backbone.history, 'getFragment').callsFake(function() {
            // different route
            return 'Contacts';
        });

        view.render();
        view.$('[data-route]').click();

        expect(refreshStub).not.toHaveBeenCalled();
        expect(navigateStub).toHaveBeenCalled();
    });

    it('should open new tab on ctrl or meta key press', function() {
        let refreshStub = sinon.stub(SugarTest.app.router, 'refresh');
        let navigateStub = sinon.stub(SugarTest.app.router, 'navigate');
        let windowOpenStub = sinon.stub(window, 'open');

        view.render();

        view.$('[data-route]').trigger($.Event('click', {
            button: 0,
            metaKey: true
        }));

        expect(windowOpenStub).toHaveBeenCalledWith('#' + moduleName, '_blank');
        expect(refreshStub).not.toHaveBeenCalled();
        expect(navigateStub).not.toHaveBeenCalled();

        view.$('[data-route]').trigger($.Event('click', {
            button: 0,
            ctrlKey: true
        }));

        expect(windowOpenStub).toHaveBeenCalledWith('#' + moduleName, '_blank');
        expect(refreshStub).not.toHaveBeenCalled();
        expect(navigateStub).not.toHaveBeenCalled();
    });

    it('should open new tab for external link', function() {
        let windowOpenStub = sinon.stub(window, 'open');
        let testlink = 'http://testdomain.com';

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return { menu: { header: {meta: [{
                label: 'LBL_MENU_1',
                route : testlink,
                openwindow: true
            }]}}};
        });

        view.render();
        view.$('[data-openwindow]').click();

        expect(windowOpenStub).toHaveBeenCalledWith(testlink, '_blank');
    });
});

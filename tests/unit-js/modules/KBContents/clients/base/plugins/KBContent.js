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
describe('Plugins.KBContents', function() {
    var moduleName = 'KBContents',
        app, view, viewMeta, sandbox, context, copiedUser;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        context = app.context.getContext({
            module: moduleName
        });
        viewMeta = {
            buttons: [
                {name: 'button1'}
            ]
        };
        app.drawer = {
            open: function() {
            },
            close: function() {
            },
            off: $.noop
        };
        context.set('model', app.data.createBean(moduleName));
        context.parent = app.context.getContext({
            module: moduleName
        });
        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBContent',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'record', moduleName);
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'view', 'create', moduleName);
        layout = SugarTest.createLayout('base', moduleName, 'list', null, context.parent);
        view = SugarTest.createView('base', moduleName, 'create', viewMeta, null, moduleName, layout);
        copiedUser = app.user.toJSON();
    });

    afterEach(function() {
        sandbox.restore();
        view.model.off();
        view.context.off();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['KBContent'];
        view = null;
        layout = null;
        app.user.set(copiedUser);
    });

    it('Validations should be in inline edit.', function() {
        view = SugarTest.createView('base', moduleName, 'recordlist', viewMeta, null, moduleName, layout);
        var validationStub = sandbox.stub(view, '_initValidationHandler');
        sandbox.stub(view, 'toggleRow');
        view.context.trigger('list:editrow:fire', view.model, {def: {}});
        expect(validationStub).toHaveBeenCalledOnce();
    });

    it('Create article should call an appropriate drawer.', function() {
        var drawerStub = sandbox.stub(app.drawer, 'open');
        var model = new Backbone.Model({
            name: 'fakeName'
        });
        sandbox.stub(app.template, 'getField').callsFake(function() {
            return function() {
                return 'fakeTemplate';
            };
        });
        sandbox.stub(app.data, 'getRelateFields').callsFake(function() {
            return [];
        });
        sandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: []
            };
        });
        view.createArticle(model);
        expect(drawerStub).toHaveBeenCalled();
        expect(drawerStub.args[0][0].context.model.get('name')).toEqual('fakeName');
        expect(drawerStub.args[0][0].context.model.get('kbdocument_body')).toEqual('fakeTemplate');
    });

    it('Created localizations and revisions should have draft status.', function() {
        var fakeModel = app.data.createBean(moduleName);
        fakeModel.set('status', 'published');

        sandbox.stub(app.data, 'createBean').callsFake(function() {
            return fakeModel;
        });
        sandbox.stub(fakeModel, 'fetch').callsFake(function(options) {
            options.success();
        });
        var createLocStub = sandbox.stub(view, '_onCreateLocalization');
        var createRevStub = sandbox.stub(view, '_onCreateRevision');

        view.createLocalization(fakeModel);
        expect(createLocStub).toHaveBeenCalled();
        expect(createLocStub.args[0][0].get('status')).toEqual('draft');

        fakeModel.set('status', '');
        view.createRevision(fakeModel);
        expect(createRevStub).toHaveBeenCalled();
        expect(createRevStub.args[0][0].get('status')).toEqual('draft');
    });

    it('Created localizations and revisions should change authorship to a current user.', function() {
        var fakeModel = app.data.createBean(moduleName);
        fakeModel.set('status', 'published');

        sandbox.stub(app.data, 'createBean').callsFake(function() {
            return fakeModel;
        });
        sandbox.stub(fakeModel, 'fetch').callsFake(function(options) {
            options.success();
        });
        var createLocStub = sandbox.stub(view, '_onCreateLocalization');
        var createRevStub = sandbox.stub(view, '_onCreateRevision');

        // Change current system's user.
        app.user.set({id: 'user2Id', full_name: 'user2Name'});

        view.createLocalization(fakeModel);
        expect(createLocStub).toHaveBeenCalled();
        expect(createLocStub.args[0][0].get('assigned_user_id')).toEqual('user2Id');
        expect(createLocStub.args[0][0].get('assigned_user_name')).toEqual('user2Name');

        view.createRevision(fakeModel);
        expect(createRevStub).toHaveBeenCalled();
        expect(createRevStub.args[0][0].get('assigned_user_id')).toEqual('user2Id');
        expect(createRevStub.args[0][0].get('assigned_user_name')).toEqual('user2Name');
    });

    it('Check possibility to create a localization.', function() {
        var fakeModel = new Backbone.Model({
            module: moduleName,
            name: 'fakeName',
            related_languages: []
        });
        sandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                languages: ['en', 'fr']
            };
        });

        // No related languages.
        expect(view.checkCreateLocalization(fakeModel)).toBe(true);

        // The same language count.
        fakeModel.set('related_languages', ['en', 'fr']);
        expect(view.checkCreateLocalization(fakeModel)).toBe(false);

        // Not the same language count.
        fakeModel.set('related_languages', ['en', 'fr', 'de']);
        expect(view.checkCreateLocalization(fakeModel)).toBe(true);
    });

    it('Create localization.', function() {
        var fakeModel = new Backbone.Model({
            module: moduleName,
            language: 'fakeLang',
            kbarticle_id: 'fakeArticleId'
        });
        var createRelatedDrawerStub = sandbox.stub(view, '_openCreateRelatedDrawer');
        var checkLocStub = sandbox.stub(view, 'checkCreateLocalization').callsFake(function() {
            return false;
        });
        sandbox.stub(view, 'getAvailableLangsForLocalization').callsFake(function() {
            return ['en', 'fr'];
        });

        sandbox.stub(app.alert, 'show').callsFake(function() {});
        sandbox.stub(view, '_getNextAvailableLanguage').callsFake(function() {
            return 'de';
        });

        view._onCreateLocalization(fakeModel);
        expect(createRelatedDrawerStub).not.toHaveBeenCalled();
        checkLocStub.restore();

        sandbox.stub(view, 'checkCreateLocalization').callsFake(function() {
            return true;
        });
        view._onCreateLocalization(fakeModel);
        expect(createRelatedDrawerStub).toHaveBeenCalled();
        expect(fakeModel.get('language')).toBe('de');
        expect(fakeModel.get('kbarticle_id')).toBe(undefined);
        expect(fakeModel.get('related_languages')).toEqual(['en', 'fr']);
    });

    it('Create revision. Should inherit parents data.', function() {
        var fakePrefillModel = new Backbone.Model({
            module: moduleName,
            useful: 1,
            notuseful: 0,
            related_languages: ['fr']
        });
        var fakeParentModel = new Backbone.Model({
            module: moduleName,
            useful: 0,
            notuseful: 1,
            language: 'en'
        });
        var createRelatedDrawerStub = sandbox.stub(view, '_openCreateRelatedDrawer');

        view._onCreateRevision(fakePrefillModel, fakeParentModel);
        expect(createRelatedDrawerStub).toHaveBeenCalled();
        expect(fakePrefillModel.get('useful')).toBe(0);
        expect(fakePrefillModel.get('notuseful')).toBe(1);
        expect(fakePrefillModel.get('related_languages')).toEqual(['en']);
    });

    it('Expiration date dependencies. Error when expiration date is lower than publishing.', function() {
        var fakeModel = app.data.createBean(moduleName);
        fakeModel.set('exp_date', '2010-10-10');
        fakeModel.set('active_date', '');
        fakeModel.set('status', 'published');
        var errors = {};
        sandbox.stub(fakeModel, 'getSynced');
        sandbox.stub(fakeModel, 'changedAttributes').callsFake(function() {
            return [];
        });
        sandbox.stub(app.date.fn, 'formatServer').callsFake(function() {
            return '2011-11-11';
        });
        sandbox.stub(view, 'getField').callsFake(function(name) {
            return name !== 'exp_date';
        });

        // Publish article with exp date. Should set the active_date automatically.
        view._doValidateExpDateField(fakeModel, [], errors, sandbox.stub());
        expect(errors['active_date']).not.toBe(undefined);
    });

    it('Change publishing and expiration dates to current on manual change after validation.', function() {
        var fakeModel = app.data.createBean(moduleName);
        var expectedDate = '2010-10-10';
        fakeModel.set('active_date', '');
        fakeModel.set('exp_date', '');
        sandbox.stub(fakeModel, 'getSynced');
        sandbox.stub(fakeModel, 'changedAttributes').callsFake(function() {
            return [];
        });
        sandbox.stub(app.date.fn, 'formatServer').callsFake(function() {
            return expectedDate;
        });

        fakeModel.set('status', 'expired');
        view._validationComplete(fakeModel, true);
        expect(fakeModel.get('exp_date')).toEqual(expectedDate);

        fakeModel.set('status', 'published');
        view._validationComplete(fakeModel, true);
        expect(fakeModel.get('active_date')).toEqual(expectedDate);
    });

});

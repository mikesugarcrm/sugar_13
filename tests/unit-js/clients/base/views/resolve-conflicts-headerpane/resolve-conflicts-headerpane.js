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
describe('Resolve Conflicts Headerpane View', function() {
    var view, app,
        moduleName = 'Accounts',
        appLangGetStub,
        context;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        appLangGetStub = sinon.stub(app.lang, 'get').callsFake(function(key, module, context) {
            return Handlebars.compile('foo {{name}}')(context);
        });

        context = app.context.getContext();
        context.set({
            module: moduleName,
            modelToSave: new Backbone.Model()
        });
        context.prepare();

        view = SugarTest.createView('base', moduleName, 'resolve-conflicts-headerpane', null, context);
    });

    afterEach(function() {
        appLangGetStub.restore();
        view.dispose();

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('_formatTitle', function() {
        it('should set the title for the headerpane', function() {
            context.get('modelToSave').set('name', 'bar');
            expect(view._formatTitle()).toBe('foo bar');
        });
    });

    describe('selectClicked', function() {
        var originalDrawer, drawerCloseSpy;

        beforeEach(function() {
            originalDrawer = app.drawer;
            app.drawer = {
                close: function(){}
            };
            drawerCloseSpy = sinon.spy(app.drawer, 'close');
        });

        afterEach(function() {
            drawerCloseSpy.restore();
            app.drawer = originalDrawer;
        });

        it('should close the drawer indicating that it is from the client data', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                date_modified: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'client'
            }));

            view.selectClicked();

            expect(drawerCloseSpy.calledOnce).toBe(true);
            expect(drawerCloseSpy.calledWith(modelToSave, false)).toBe(true);
        });

        it('should close the drawer indicating that it is from the database data', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                data: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'database'
            }));

            view.selectClicked();

            expect(drawerCloseSpy.calledOnce).toBe(true);
            expect(drawerCloseSpy.calledWith(modelToSave, true)).toBe(true);
        });

        it('should copy the date_modified in the database to the model when client data is selected', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                date_modified: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'client'
            }));

            view.selectClicked();

            expect(modelToSave.get('date_modified')).toBe(123);
        });

        it('should copy over the data from the database when the database data is selected', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                data: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'database'
            }));

            view.selectClicked();

            expect(modelToSave.get('data')).toBe(123);
        });
    });
});

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
describe('KBContents.Base.View.Massupdate', function() {
    var view, app, layout, sandbox, module = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBContent',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });

        layout = SugarTest.createLayout('base', module, 'list');
        view = SugarTest.createView('base', module, 'massupdate', {}, null, true, layout);
        view.model = app.data.createBean(module, {id: 'massupdatetest'}, []);

        SugarTest.testMetadata.set();
        sandbox = sinon.createSandbox();
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['KBContent'];
        sandbox.restore();
    });


    it('shouldn\'t display confirmation without status field', function() {
        var models = [
                app.data.createBean(module, {id: 1, status: 'draft', name: 'test1'})
            ],
            callback = sandbox.stub($.noop()),
            fields = [{name: 'name'}, {name: 'language'}],
            alert = sandbox.stub(app.alert, 'show');
        view._doValidateMassUpdate(models, fields, callback);
        expect(callback).toHaveBeenCalled();
        expect(alert).not.toHaveBeenCalled();
    });

    it('shouldn\'t display confirmation without any error', function() {
        var models = [
                app.data.createBean(module, {id: 1, status: 'draft', name: 'test1'})
            ],
            callback = sandbox.stub($.noop()),
            fields = [{name: 'name'}, {name: 'status'}],
            alert = sandbox.stub(app.alert, 'show');
        view._doValidateMassUpdate(models, fields, callback);
        expect(callback).toHaveBeenCalled();
        expect(alert).not.toHaveBeenCalled();
    });

    it('should display confirmation with status error', function() {
        var models = [
                app.data.createBean(module, {id: 1, status: 'draft', name: 'test1'})
            ],
            callback = sandbox.stub($.noop()),
            fields = [{name: 'name'}, {name: 'status'}],
            alert = sandbox.stub(app.alert, 'show').withArgs('save_without_publish_date_confirmation');
        view.model.set('status', 'approved');
        view._doValidateMassUpdate(models, fields, callback);
        expect(callback).not.toHaveBeenCalled();
        expect(alert).toHaveBeenCalled();
    });
});

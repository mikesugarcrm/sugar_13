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
describe('Base.View.Docusign', function() {
    var app;
    var view;
    var model;
    var context;
    var meta;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        context = new app.Context();

        context.set('model', new Backbone.Model());

        context.parent = new app.Context({module: 'Home'});

        layout = SugarTest.createLayout(
            'base',
            'Home',
            'list',
            null,
            context
        );

        meta = {
            config: false
        };
        view = SugarTest.createView(
            'base',
            'Home',
            'docusign',
            meta,
            new app.Context(),
            false,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        model = null;
        layout = null;
        context = null;
    });

    describe('_render', function() {
        it('should render the docusign dashlet', function() {
            view.loaded = true;
            view._render();

            expect(view.$el.hasClass('docusign')).toEqual(true);
            expect(view.chartModel instanceof Backbone.Model).toEqual(true);
        });
    });

    describe('sendToDocuSign', function() {
        beforeEach(function() {
            sinon.stub(window, 'open');
        });

        it('should alert if user not logged', function() {
            var alertShowStub = sinon.stub(app.alert, 'show');
            view.userIsConfigured = false;
            view.sendToDocuSign();

            expect(alertShowStub).toHaveBeenCalledOnce();
            expect(alertShowStub.lastCall.args[0]).toEqual('warn-docusign-user-not-logged-in');

            alertShowStub.restore();
        });

        it('should send the envelope', function() {
            var triggerStub = sinon.stub(app.events, 'trigger');
            view.userIsConfigured = true;

            app.controller.context.set('model', new Backbone.Model());
            view.documentCollection = {
                models: []
            };
            view.sendToDocuSign();

            expect(triggerStub).toHaveBeenCalledOnce();
            expect(triggerStub.lastCall.args[0]).toEqual('docusign:send:initiate');

            triggerStub.restore();
        });
    });

    describe('showDraft', function() {
        beforeEach(function() {
            sinon.stub(window, 'open');
        });

        it('should alert if user is not the one who created the envelope', function() {
            var alertShowStub = sinon.stub(app.alert, 'show');
            app.user.id = '1';
            var model = new Backbone.Model();
            model.set('created_by_link' , {id: '2'});
            view.userIsConfigured = true;
            view.showDraft(model);

            expect(alertShowStub).toHaveBeenCalledOnce();
            expect(alertShowStub.lastCall.args[0]).toEqual('warn-docusign-create-user');

            alertShowStub.restore();
        });

        it('should open the draft', function() {
            var triggerStub = sinon.stub(app.events, 'trigger');
            app.user.id = '1';
            var model = new Backbone.Model();
            model.set('created_by_link' , {id: '1'});

            view.documentCollection = {
                models: []
            };
            view.userIsConfigured = true;
            view.showDraft(model);

            expect(triggerStub).toHaveBeenCalledOnce();
            expect(triggerStub.lastCall.args[0]).toEqual('docusign:send:initiate');

            triggerStub.restore();
        });
    });

    describe('dispose', function() {
        it('should dispose the dashlet', function() {
            view.dispose();

            expect(view.disposed).toEqual(true);
            expect(view.$el).toBe(null);
        });
    });
});

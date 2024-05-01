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
describe('Base.Layouts.PreviewActivityStream', function() {
    var layout;
    var app;
    var module = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('preview-activitystream', 'layout', 'base');
        SugarTest.loadComponent('base', 'layout', 'preview-activitystream');
        SugarTest.loadComponent('base', 'layout', 'activitystream');
        SugarTest.testMetadata.addLayoutDefinition('preview-activitystream', {
            type: 'preview-activitystream'
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        var ctx = app.context.getContext({
            forceNew: true,
            module: 'Activities'
        });
        ctx.prepare();
        // This is the preview context created by the preview.js layout.
        ctx.parent = app.context.getContext({module: module});

        layout = SugarTest.createLayout('base', module, 'preview-activitystream', null, ctx);
    });

    afterEach(function() {
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('Initialize', function() {
        it('Should not render any activities', function() {
            expect(layout.$('.activitystream-list').children().length).toBe(0);
        });
    });

    describe('fetchActivities()', function() {
        var getModuleStub;
        beforeEach(function() {
            getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    isBwcEnabled: false
                };
            });
        });

        afterEach(function() {
            getModuleStub.restore();
        });

        it('Should fetch a collection of activities', function() {
            var collectionStub = sinon.stub(layout.collection, 'fetch');
            layout.fetchActivities(new Backbone.Model());
            expect(collectionStub.calledOnce).toBe(true);
            collectionStub.restore();
        });
    });

    describe('renderActivities()', function() {
        it('Should render two activities when the collection size is two but add event', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.collection.reset([new Backbone.Model(), new Backbone.Model()]);
            layout.renderActivities(layout.collection);

            expect(renderPostStub.calledTwice).toBe(true);
            renderPostStub.restore();
        });

        it('Should render two activities when the collection size is two but reset event', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.collection.add([new Backbone.Model(), new Backbone.Model()]);

            expect(renderPostStub.calledTwice).toBe(true);
            renderPostStub.restore();
        });

        it('Should not render any activities when the collection is empty', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.renderActivities(layout.collection);

            expect(renderPostStub.called).toBe(false);
            renderPostStub.restore();
        });
    });
});

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
describe("Base.View.Preview", function() {

    var preview, layout, app, meta;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition("record", {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", {"name":"favorite", "type":"favorite"}, {"name":"follow", "type":"follow"}]
            }, {
                "name": "panel_body",
                "label": "LBL_PANEL_2",
                "columns": 1,
                "labels": true,
                "labelsOnTop": false,
                "placeholders":true,
                'fields': ['description', 'case_number', 'type', []] // empty array is a filler field
            }, {
                "name": "panel_hidden",
                "hide": true,
                "labelsOnTop": false,
                "placeholders": true,
                "fields": ["created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, "Cases");
        SugarTest.loadHandlebarsTemplate('preview', 'view', 'base');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.loadComponent('base', 'view', 'record');
        layout = SugarTest.createLayout('base', "Cases", "preview");
        preview = SugarTest.createView('base', 'Cases', 'preview', {}, null);
        preview.layout = layout;
        app = SugarTest.app;
        meta = app.metadata.getView('Cases', 'record');
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        preview = null;
        meta = null;
    });

    describe("_previewifyMetadata", function(){
        it("should not modify the global metadata", function(){
            var modified = preview._previewifyMetadata(meta),
                unmodified = app.metadata.getView('Cases', 'record');

            expect(modified).toNotBe(unmodified);
            expect(modified).toNotEqual(unmodified);
        });
        it("should convert header to regular panel", function(){
            expect(meta.panels[0].header).toEqual(true);
            var trimmed = preview._previewifyMetadata(meta);
            expect(trimmed.panels[0].header).toEqual(false);
            var headers = _.filter(trimmed.panels, function(panel){
                return panel.header == true;
            });
            expect(headers).toEqual([]);
        });
        it("should remove favorites field from metadata", function(){
            var fav = _.find(meta.panels[0].fields, function(field){
                return field.type === "favorite";
            });
            expect(fav).toBeTruthy();
            var trimmed = preview._previewifyMetadata(meta);
            fav = _.find(trimmed.panels[0].fields, function(field){
                return field.type === "favorite";
            });
            expect(fav).toBeUndefined();
        });
        it("should remove follow field from metadata", function(){
            var follow = _.find(meta.panels[0].fields, function(field){
                return field.type === "follow";
            });
            expect(follow).toBeTruthy();
            var trimmed = preview._previewifyMetadata(meta);
            follow = _.find(trimmed.panels[0].fields, function(field){
                return field.type === "follow";
            });
            expect(follow).toBeUndefined();
        });

        it("should detect if at least one of the panels is hidden", function(){
            expect(preview.hiddenPanelExists).toBe(true);
            meta.panels[2].hide = false;
            preview._previewifyMetadata(meta);
            expect(preview.hiddenPanelExists).toBe(false);
        });

    });

    it("should trigger 'preview:close' and 'list:preview:decorate' when source model destroy", function() {
        var dummySourceModel = app.data.createBean("Cases", {"id":"testid", "_module": "Cases"});
        var dummyModel = app.data.createBean("Cases", {"id":"testid", "_module": "Cases"});
        var closePreviewFired = false;
        var listPreviewDecorateFired = false;
        sinon.stub(app.events, 'trigger').callsFake(function(event) {
            expect(event).not.toBeEmpty();
            if(event == "preview:close"){
                closePreviewFired = true;
            } else if(event == "list:preview:decorate"){
                listPreviewDecorateFired = true;
            }
        });
        sinon.stub(preview, '_initDropdownBasedViewsForModel');

        preview.model = dummyModel;
        preview.switchModel(dummySourceModel);

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("DELETE", /.*rest\/v10\/Cases\/testid.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({})]);
        dummySourceModel.destroy();
        SugarTest.server.respond();

        expect(closePreviewFired).toBe(true);
        expect(listPreviewDecorateFired).toBe(true);
    });

    it("should trigger 'preview:close' and 'list:preview:decorate' when model remove from collection", function() {
        var dummyModel = app.data.createBean("Cases", {"id":"testid", "_module": "Cases"});
        var dummyCollection = {};
        var closePreviewFired = false;
        var listPreviewDecorateFired = false;
        sinon.stub(app.events, 'trigger').callsFake(function(event) {
            expect(event).not.toBeEmpty();
            if(event == "preview:close"){
                closePreviewFired = true;
            } else if(event == "list:preview:decorate"){
                listPreviewDecorateFired = true;
            }
        });
        sinon.stub(preview, '_initDropdownBasedViewsForModel');
        dummyCollection.models = [dummyModel];

        preview.renderPreview(dummyModel, dummyCollection);
        preview.collection.remove(dummyModel);

        expect(closePreviewFired).toBe(true);
        expect(listPreviewDecorateFired).toBe(true);
    });

    describe("renderPreview", function(){
        it("should trigger 'preview:open' and 'list:preview:decorate' events", function(){
            var dummyModel = app.data.createBean("Cases", {"id":"testid", "_module": "Cases"});
            var dummyCollection = {};
            dummyCollection.models = [dummyModel];
            var openPreviewFired = false;
            var listPreviewDecorateFired = false;
            sinon.stub(app.events, 'trigger').callsFake(function(event, model) {
                expect(event).not.toBeEmpty();
                if(event == "preview:open"){
                    openPreviewFired = true;
                } else if(event == "list:preview:decorate"){
                    listPreviewDecorateFired = true;
                    expect(model.get("id")).toEqual("testid");
                }
            });
            sinon.stub(preview, '_initDropdownBasedViewsForModel');
            preview.renderPreview(dummyModel, dummyCollection);
            expect(openPreviewFired).toBe(true);
            expect(listPreviewDecorateFired).toBe(true);
        });
    });

    describe('Switching to next and previous record', function() {

        var createListCollection;

        beforeEach(function() {
            createListCollection = function(nbModels, offsetSelectedModel) {
                     var collection = new Backbone.Collection();

                     var modelIds = [];
                     for (var i=0;i<=nbModels;i++) {
                         var model = new Backbone.Model(),
                             id = i + '__' + Math.random().toString(36).substr(2,16);

                         model.set({id: id, index: i});
                         if (i === offsetSelectedModel) {
                             preview.model.set(model.toJSON());
                             collection.add(model);
                         }
                         collection.add(model);
                         modelIds.push(id);
                     }
                     preview.collection.reset(collection.models);
                     return modelIds;
                 };
        });

        it("Should find previous and next model from list collection", function() {
            var modelIds = createListCollection(5, 3);
            preview.showPreviousNextBtnGroup();
            expect(preview.layout.previous).toBeDefined();
            expect(preview.layout.next).toBeDefined();
            expect(preview.layout.previous.get('id')).toEqual(modelIds[2]);
            expect(preview.layout.next.get('id')).toEqual(modelIds[4]);
            expect(preview.layout.hideNextPrevious).toBe(false);
        });

        it("Should find previous model from list collection", function() {
            var modelIds = createListCollection(5, 5);
            preview.showPreviousNextBtnGroup();
            expect(preview.layout.previous).toBeDefined();
            expect(preview.layout.next).not.toBeDefined();
            expect(preview.layout.previous.get('id')).toEqual(modelIds[4]);
            expect(preview.layout.hideNextPrevious).toBe(false);
        });

        it("Should find next model from list collection", function() {
            var modelIds = createListCollection(5, 0);
            preview.showPreviousNextBtnGroup();
            expect(preview.layout.previous).not.toBeDefined();
            expect(preview.layout.next).toBeDefined();
            expect(preview.layout.next.get('id')).toEqual(modelIds[1]);
            expect(preview.layout.hideNextPrevious).toBe(false);
        });

        it("Should hide next/previous buttons when collection has one or is empty", function() {
            createListCollection(0, 0);
            preview.showPreviousNextBtnGroup();
            expect(preview.layout.previous).not.toBeDefined();
            expect(preview.layout.next).not.toBeDefined();
            expect(preview.layout.hideNextPrevious).toBe(true);

            preview.collection = null;
            preview.showPreviousNextBtnGroup();
            expect(preview.layout.previous).not.toBeDefined();
            expect(preview.layout.next).not.toBeDefined();
            expect(preview.layout.hideNextPrevious).toBe(true);
        });

        it("should filter out the collection sets that only has access for view", function() {

            var accessIds;

            accessIds = [
                false,
                true,
                true,
                false,
                true
            ];
            sinon.stub(app.acl, 'hasAccessToModel').callsFake(function(method, model) {
                return accessIds[model.get("index")];
            });

            var modelIds = createListCollection(accessIds.length - 1, 0);
            expect(modelIds.length).toBe(accessIds.length);

            _.each(modelIds, function(modelId, index){
                var model = preview.collection.where({id: modelId});
                if(accessIds[index]) {
                    expect(_.isEmpty(model)).toBe(false);
                    expect(model.length).toBe(1);
                    expect(accessIds[_.first(model).get("index")]).toBeTruthy();
                } else {
                    expect(model.length).toBe(0);
                }
            }, this);
        });

        it("Should find previous and next actual module when collection has models with different modules.", function() {
            var models = [];

            models.push(app.data.createBean("Cases", {"id":"testid_1", "module": "Cases"}));
            models.push(app.data.createBean("Cases", {"id":"testid_2", "module": "Cases"}));
            models.push(app.data.createBean("Accounts", {"id":"testid_3", "module": "Accounts"}));

            preview.model.set(models[1].toJSON());
            preview.collection.reset(models);
            preview.showPreviousNextBtnGroup();

            expect(preview.layout.previous).toBeDefined();
            expect(preview.layout.next).toBeDefined();
            expect(preview.layout.previous.module).toEqual(models[0].module);
            expect(preview.layout.next.module).toEqual(models[2].module);

            var currIndex = _.indexOf(preview.collection.models, models[1]);
            var currModule = preview.collection.at(currIndex).module;

            expect(currModule).toBeDefined();
            expect(currModule).toEqual(models[1].module);

            currIndex += 1;
            currModule = preview.collection.at(currIndex).module;

            expect(currModule).toBeDefined();
            expect(currModule).toEqual(models[2].module);

            currIndex -= 2;
            currModule = preview.collection.at(currIndex).module;

            expect(currModule).toBeDefined();
            expect(currModule).toEqual(models[0].module);
        });
    });

    it('should not show filler fields', function() {
        preview.render();
        // We should get 8 fields here because we start with 11 fields but favorite, following
        // and filler fields get removed
        expect(_.size(preview.fields)).toEqual(8);
    });
});

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
describe("Base.Field.Follow", function() {
    var app, field, getModuleStub, activityEnabled = true;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');

        getModuleStub = sinon.stub(app.metadata, 'getModule').callsFake(function(module) {
            return {activityStreamEnabled:activityEnabled};
        });

        var model = new Backbone.Model({
                id: '1234567890'
            });
        field = SugarTest.createField("base", "follow", "follow", "edit", null, null, model);

    });

    afterEach(function() {
        getModuleStub.restore();
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
    });

    describe("Label", function() {
        it('should assign the label as LBL_FOLLOW when the following value is empty', function() {
            expect(field.label).toBe('LBL_FOLLOW');
        });

        it('should assign the label as LBL_UNFOLLOW once following set as true', function() {
            field.model.set("following", true);
            expect(field.label).toBe('LBL_UNFOLLOW');
        });

        it('should assign the label back to LBL_FOLLOW once following set as false', function() {
            field.model.set("following", false);
            expect(field.label).toBe('LBL_FOLLOW');
        });

        it('should trigger "show" listener once label is updated', function() {
            var showStub = sinon.stub(field, 'trigger');
            field.model.set("following", true);
            expect(showStub).toHaveBeenCalled();
            expect(showStub).toHaveBeenCalledWith('show');
            showStub.restore();
        });
    });

    describe("Label for detail view", function() {
        beforeEach(function() {
            field.setMode("detail");
        });

        it('should assign the label as LBL_FOLLOW when the following value is empty', function() {
            expect(field.label).toBe('LBL_FOLLOW');
        });

        it('should assign the label as LBL_FOLLOWING once following set as true', function() {
            field.model.set("following", true);
            expect(field.label).toBe('LBL_FOLLOWING');
        });

        it('should assign the label back to LBL_FOLLOW once following set as false', function() {
            field.model.set("following", false);
            expect(field.label).toBe('LBL_FOLLOW');
        });

    });

    it("Should set the model when the 'favorite:active' event is triggered on the context.", function() {
        field.model.set("following", false);
        field.model.trigger("favorite:active");
        expect(field.model.get("following")).toBeTruthy();
    });

    describe("_render", function() {
        it('should show if activity stream is enabled', function() {
            field._render();
            expect(field.isHidden).toBeFalsy();
        });

        it('should be hidden if activity stream is not enabled', function() {
            activityEnabled = false;
            field._render();
            expect(field.isHidden).toBeTruthy();
        });
    });
});

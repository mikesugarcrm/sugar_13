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
describe('Base.View.PanelTop', function() {
    var app, view, context, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        SugarTest.loadComponent('base', 'view', 'panel-top');
        var parentContext = app.context.getContext();
        parentContext.set("module", "Accounts");
        context = app.context.getContext();
        context.parent = parentContext;
        view = SugarTest.createView("base","Contacts", "panel-top", null, context);
        view.model = new Backbone.Model();
    });
    afterEach(function() {
        sinonSandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        Handlebars.templates = {};
        view.model = null;
        view = null;
    });

    describe('Create Link model', function() {
        var parentModel, createBeanStub, relateFieldStub;

        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin'
            }),
            createBeanStub = sinonSandbox.stub(app.data, 'createRelatedBean').callsFake(function() {
               return new Backbone.Model();
            }),
            relateFieldStub = sinonSandbox.stub(app.data, 'getRelateFields').callsFake(function() {
                return [{
                    name: 'product_template_name',
                    rname: 'name',
                    id_name: 'product_template_id',
                    populate_list: {
                        account_id: 'account_id',
                        account_name: 'account_name',
                        assigned_user_name: 'user_name'
                    }
                }];
            });
        });
        afterEach(function() {
            parentModel = null;
        });

        it('should populate related fields when it creates linked record', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.get('product_template_id')).toBe(parentModel.get('id'));
            expect(newModel.get('product_template_name')).toBe(parentModel.get('name'));
            expect(newModel.get('account_id')).toBe(parentModel.get('account_id'));
            expect(newModel.get('account_name')).toBe(parentModel.get('account_name'));
            expect(newModel.get('user_name')).toBe(parentModel.get('assigned_user_name'));
        });
        it('should store the relate fields in default to keep the values when creating a new linked model', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.relatedAttributes['product_template_id']).toBe(parentModel.get('id'));
            expect(newModel.relatedAttributes['product_template_name']).toBe(parentModel.get('name'));
            expect(newModel.relatedAttributes['account_id']).toBe(parentModel.get('account_id'));
            expect(newModel.relatedAttributes['account_name']).toBe(parentModel.get('account_name'));
            expect(newModel.relatedAttributes['user_name']).toBe(parentModel.get('assigned_user_name'));
        });
    });

    describe('createRelatedRecord', function() {
        var openCreateDrawerStub, bwcEnabledStub, bwcCreateRelated;
        beforeEach(function() {
            openCreateDrawerStub = sinonSandbox.stub(view, 'openCreateDrawer').callsFake($.noop());
            bwcCreateRelated = sinonSandbox.stub(app.bwc, 'createRelatedRecord').callsFake($.noop());
        });
        afterEach(function(){
            bwcEnabledStub.restore();
        });

        it('should route to BWC create for related BWC modules', function() {
            bwcEnabledStub = sinonSandbox.stub(app.metadata, 'getModule').returns({isBwcEnabled: true});
            view.createRelatedRecord();
            expect(bwcEnabledStub.called).toBe(true);
            expect(openCreateDrawerStub.called).toBe(false);
            expect(bwcCreateRelated.called).toBe(true); //make sure BWC create is called
        });

        it('should open create drawer for related sidecar modules', function() {
            var routeToBwcCreateStub = sinonSandbox.stub(view, 'routeToBwcCreate').callsFake($.noop());
            bwcEnabledStub = sinonSandbox.stub(app.metadata, 'getModule').returns({isBwcEnabled: false});
            view.createRelatedRecord();
            expect(bwcEnabledStub.called).toBe(true);
            expect(routeToBwcCreateStub.called).toBe(false);
            expect(openCreateDrawerStub.called).toBe(true);
        });
    });

});

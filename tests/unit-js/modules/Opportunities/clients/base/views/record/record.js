
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

describe('Opportunities.Base.Views.Record', function() {
    var app,
        layout,
        view,
        options,
        sinonSandbox;

    afterEach(function() {
        sinonSandbox.restore();
    });

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.createSandbox();
        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: 'name'
                            },{
                                name: 'commit_stage',
                                label: 'LBL_COMMIT_STAGE'
                            }
                        ]
                    }
                ]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        SugarTest.seedMetadata(true, './fixtures');
        SugarTest.loadPlugin('CommittedDeleteWarning');


        var context = app.context.getContext();

        var model = app.data.createBean('Opportunities'),
            tmpModel = new Backbone.Model();
        model.getRelatedCollection = function() { return tmpModel; };
        sinonSandbox.stub(tmpModel, 'fetch').callsFake(function() {});
        context.set({
            model: model,
            module: 'Opportunities'
        });
        context.prepare();

        layout = SugarTest.createLayout('base', 'Opportunities', 'record', {});
        view = SugarTest.createView('base', 'Opportunities', 'record', options.meta, context, true, layout);
    });

    afterEach(function() {
        sinonSandbox.restore();
    });


    describe('addInitListener', function() {
        beforeEach(function() {
            sinonSandbox.stub(view, 'once');
        });
        it('should call view.once', function() {
            sinonSandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return true;
            });

            view.addInitListener();

            expect(view.once).toHaveBeenCalled();
        });
        it('should not call view.once', function() {
            sinonSandbox.stub(app.acl, 'hasAccess').callsFake(function() {
                return false;
            });

            view.addInitListener();

            expect(view.once).not.toHaveBeenCalled();
        });
    });

    describe('rliCreateClose', function() {
        afterEach(function() {
            view.context.children = [];
        });

        it('should find and call child context loadData method', function() {
            let rliCtx = app.context.getContext();
            rliCtx.set('module', 'RevenueLineItems');
            sinon.stub(rliCtx, 'loadData').callsFake(function() {});
            view.context.children.push(rliCtx);

            view.rliCreateClose({});

            expect(rliCtx.loadData).toHaveBeenCalled();

            rliCtx.loadData.restore();
        });
    });

    describe('createLinkModel', function() {
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
                    return [
                        {
                            name: 'product_template_name',
                            rname: 'name',
                            id_name: 'product_template_id',
                            populate_list: {
                                account_id: 'account_id',
                                account_name: 'account_name',
                                assigned_user_name: 'user_name'
                            }
                        }
                    ];
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

});

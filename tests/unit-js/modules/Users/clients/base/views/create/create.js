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
describe('Users.Base.View.Create', function() {
    let app;
    let context;
    let layout;
    let view;
    let groupMeta = {
        panels: [
            {
                fields: [
                    {name: 'group-field'}
                ]
            }
        ]
    };
    let portalMeta = {
        panels: [
            {
                fields: [
                    {name: 'portal-field'}
                ]
            }
        ]
    };

    let createView = function(meta, context) {
        return SugarTest.createView('base', 'Users', 'create', meta, context,
            true, layout);
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');

        sinon.stub(app.metadata, 'getView').withArgs('Users', 'record-group').returns(groupMeta)
            .withArgs('Users', 'record-portalapi').returns(portalMeta);

        layout = SugarTest.createLayout('base', 'Users', 'create', {});
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();

        if (view && !view.disposed) {
            view.dispose();
        }
    });

    describe('initialize', function() {
        describe('for group-type users', function() {
            beforeEach(function() {
                context = app.context.getContext({
                    module: 'Users',
                    userType: 'group',
                    model: new Backbone.Model()
                });
                view = createView({}, context);
            });

            it('should set group meta for group-type users', function() {
                expect(view.meta.panels).toEqual(groupMeta.panels);
            });
        });

        describe('for group-type users', function() {
            beforeEach(function() {
                context = app.context.getContext({
                    module: 'Users',
                    userType: 'portalapi',
                    model: new Backbone.Model()
                });
                view = createView({}, context);
            });

            it('should set group meta for group-type users', function() {
                expect(view.meta.panels).toEqual(portalMeta.panels);
            });
        });
    });
});

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
describe('Emails.Field.Name', function() {
    var app;
    var field;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('name', 'field', 'base', 'list', 'Emails');
        SugarTest.testMetadata.set();

        field = SugarTest.createField('base', 'name', 'name', 'list', {}, 'Emails', null, null, true);
        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete field.model;
        field = null;
        SugarTest.testMetadata.dispose();
        sandbox.restore();
        Handlebars.templates = {};
    });

    describe('render', function() {
        it('should show a paperclip if attachments exist', function() {
            field.model.set('total_attachments', 3);
            field.render();

            expect(field.$('.email-has-attachments .sicon-attach').length).toBe(1);
        });

        it('should not show a paperclip if attachments do not exist', function() {
            field.model.set('total_attachments', 0);
            field.render();

            expect(field.$('.email-has-attachments .sicon-attach').length).toBe(0);
        });
    });

    describe('format()', function() {
        var result;

        it('should return with no subject if value is empty', function() {
            sandbox.stub(app.lang, 'get').callsFake(function() { return 'LBL_NO_SUBJECT'; });
            result = field.format('');

            expect(result).toBe('LBL_NO_SUBJECT');
        });

        it('should return the value if value is not empty', function() {
            result = field.format('testValue');

            expect(result).toBe('testValue');
        });
    });

    describe('buildHref()', function() {
        var oldAppRouter;

        beforeEach(function() {
            oldAppRouter = app.router;
            app.router = {
                buildRoute: sandbox.stub()
            };
            field.model.set('id', _.uniqueId());
        });

        afterEach(function() {
            app.router = oldAppRouter;
        });

        using('different email states and email client settings',
            [
                ['Draft', true, 'compose'],
                ['Draft', false, null],
                ['Archived', true, null],
                ['Archived', false, null]
            ],
            function(state, useSugarClient, action) {
                it('should return the correct href', function() {
                    field.model.set('state', state);
                    sandbox.stub(field, '_useSugarEmailClient').returns(useSugarClient);
                    sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);

                    field.buildHref();
                    expect(app.router.buildRoute).toHaveBeenCalledWith('Emails', field.model.get('id'), action);
                });
            }
        );

        it('should use the action from the route def if one is given in the view metadata', function() {
            field.def.route = {
                action: 'edit'
            };
            field.model.set('state', 'Draft');
            sandbox.stub(field, '_useSugarEmailClient').returns(true);
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);

            field.buildHref();
            expect(app.router.buildRoute).toHaveBeenCalledWith('Emails', field.model.get('id'), field.def.route.action);
        });

        it('should not add the compose action if the current user cannot edit the draft', function() {
            field.model.set('state', 'Draft');
            sandbox.stub(field, '_useSugarEmailClient').returns(true);
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(false);

            field.buildHref();
            expect(app.router.buildRoute).toHaveBeenCalledWith('Emails', field.model.get('id'), null);
        });
    });
});

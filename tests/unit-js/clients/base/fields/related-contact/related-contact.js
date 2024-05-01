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
describe('Base.Field.RelatedContact', function() {
    var app,
        field,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        var model = new Backbone.Model();
        model.set({
            contact_id: 'testContactId'
        });
        model.module = 'Contacts';

        field = SugarTest.createField('base', 'related-contact', 'related-contact', 'list', null, null, model);

        sandbox.stub(field, '_super').callsFake(function() {});
    });

    afterEach(function() {
        sandbox.restore();
        app = null;
        field = null;
    });

    describe('buildHref()', function() {
        it('should call buildCSSClasses to set css class', function() {
            var url = field.buildHref();
            expect(url).toBe('#Contacts/testContactId');
        });
    });

    describe('onLinkClicked()', function() {
        var routerRefreshSpy,
            backboneFragmentStub;

        beforeEach(function() {
            routerRefreshSpy = sandbox.spy(app.router, 'refresh');
            backboneFragmentStub = sandbox.stub(Backbone.history, 'getFragment').callsFake(function() {
                return 'Contacts/testContactId'
            });
        });

        it('should call buildCSSClasses to set css class', function() {
            var url = field.buildHref();
            field.onLinkClicked();
            expect(routerRefreshSpy).toHaveBeenCalled();
        });
    });
});

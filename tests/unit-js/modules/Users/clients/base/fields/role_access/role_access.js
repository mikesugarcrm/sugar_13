
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
describe('Base.Users.RoleAccess', function() {
    let app;
    let field;
    let model;
    let module = 'Users';

    beforeEach(function() {
        app = SugarTest.app;

        model = app.data.createBean(module);

        SugarTest.loadComponent('base', 'field', 'base');
        field = SugarTest.createField(
            'base',
            'select_test',
            'role_access',
            'detail',
            {},
            module,
            model,
            null,
            true
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('initialize', function() {
        it('should call the _getAccessData function', function() {
            sinon.stub(field, '_super');
            sinon.stub(field, '_getAccessData');
            field.initialize();

            expect(field._getAccessData).toHaveBeenCalled();
        });
    });

    describe('_getAccessData', function() {
        it('should call the api.call function', function() {
            sinon.stub(app.api, 'buildURL');
            sinon.stub(app.api, 'call');
            field._getAccessData();

            expect(app.api.call).toHaveBeenCalled();
        });
    });
});

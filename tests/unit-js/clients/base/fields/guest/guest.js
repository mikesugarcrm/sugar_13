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
describe('Base.Field.Guest', function() {
    var field;
    var fieldDef;
    var app;

    beforeEach(function() {
        app = SugarTest.app;

        fieldDef = {
            'name': 'invitees',
            'type': 'guest',
            'links': [
                'contacts',
                'leads',
                'users',
            ],
            'label': 'LBL_INVITEES',
        };

        field = SugarTest.createField('base', 'invitees', 'guest',
            'detail', fieldDef);
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
    });

    describe('search', function() {
        it('should call `getFieldValue`', function() {
            sandbox = sinon.createSandbox();
            sandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks, options) {
                if (callbacks.success) {
                    callbacks.success({});
                }
            });
            var getFieldValueStub = sinon.stub(field, 'getFieldValue');
            var queryObj = {
                    term: 'asdf',
                    context: {},
                    callback: sandbox.spy()
                };

            field.search(queryObj);
            expect(getFieldValueStub).toHaveBeenCalled();
            sandbox.restore();
        });
    });
});

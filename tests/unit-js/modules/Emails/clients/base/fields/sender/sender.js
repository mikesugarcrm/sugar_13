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
describe('Emails.Field.Sender', function() {
    var app, field, ajaxStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'sender', 'Emails');

        field = SugarTest.createField('base', 'email_config', 'sender', 'edit', {}, 'Emails', null, null, true);
        field.endpoint = {
            module: 'OutboundEmailConfiguration',
            action: 'list'
        };

        //used as mock for select2 library
        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on: function() {
                        return obj;
                    }
                };

                return obj;
            };
        }

        ajaxStub = sinon.stub($, 'ajax').callsFake($.noop);
    });

    afterEach(function() {
        ajaxStub.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete field.model;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    it('should call custom endpoint on render when tplName is "edit"', function() {
        var url, regex, apiCallStub;
        url = 'rest/v10/OutboundEmailConfiguration/list';
        regex = new RegExp('.*'+url);
        apiCallStub = sinon.stub(app.api, 'call');
        field.options.viewName = 'edit';
        field._render();
        expect(apiCallStub.calledOnce).toBeTruthy();
        expect(apiCallStub.args[0][0]).toEqual('GET');
        expect(apiCallStub.args[0][1]).toMatch(/.*rest\/v10\/OutboundEmailConfiguration\/list/);
        apiCallStub.restore();
    });

    it('should not call custom endpoint on render when tplName is not "edit"', function() {
        var apiCallStub, populateValues;
        populateValues = sinon.spy(field, 'populateValues');
        apiCallStub = sinon.stub(app.api, 'call');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', /.*rest\/v10\/OutboundEmailConfiguration\/list.*/,
            [200, {'Content-Type': 'application/json'}, '']);

        field.options.viewName = 'foo';
        field._render();
        SugarTest.server.respond();
        expect(populateValues.calledOnce).toBeFalsy();

        populateValues.restore();
        apiCallStub.restore();
    });

    it('should set the default value if custom endpoint returns data and the model does not yet have a value', function() {
        var results = [
            {id: 'abcd', display: 'Configuration A', type: 'system', 'default': true},
            {id: 'efgh', display: 'Configuration B', type: 'user', 'default': false}
        ];
        field.disposed = false;
        field.model.unset('email_config', {silent: true});
        field.populateValues(results);
        expect(field.model.get('email_config')).toBe(results[0].id);
    });
});

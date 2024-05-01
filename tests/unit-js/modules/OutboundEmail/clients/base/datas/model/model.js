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

describe('Data.Base.OutboundEmailBean', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', 'OutboundEmail', true, false);
        app.data.declareModels();

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        sandbox.restore();
        SugarTest.testMetadata.dispose();
    });

    it('should default `name`, `email_address`, and `email_address_id`', function() {
        var model;
        var name = 'Jack Edwards';
        var primary = 'foo@bar.com';
        var email = [{
            'email_address': primary,
            'email_address_id': _.uniqueId(),
            'opt_out': false,
            'invalid_email': false,
            'primary_address': true,
            'reply_to_address': true
        }];
        var teamId = _.uniqueId();
        var stub = sandbox.stub(app.user, 'get');

        stub.withArgs('full_name').returns(name);
        stub.withArgs('email').returns(email);
        stub.withArgs('private_team_id').returns(teamId);
        stub.withArgs('my_teams').returns([{id: teamId, name: 'my name'}]);

        sandbox.stub(app.utils, 'getPrimaryEmailAddress').returns(primary);

        model = app.data.createBean('OutboundEmail');

        // Defaults are defined.
        expect(model.getDefault('name')).toBe(name);
        expect(model.getDefault('email_address')).toBe(primary);
        expect(model.getDefault('email_address_id')).toBe(email[0].email_address_id);
        expect(model.getDefault('team_name')[0].id).toBe(teamId);

        // Defaults are applied.
        expect(model.get('name')).toBe(name);
        expect(model.get('email_address')).toBe(primary);
        expect(model.get('email_address_id')).toBe(email[0].email_address_id);
        expect(model.get('team_name')[0].id).toBe(teamId);
    });
});

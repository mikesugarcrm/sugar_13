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
const {Agent, Fixtures} = require('@sugarcrm/thorn');
const chakram = require('chakram');
const expect = chakram.expect;

describe('Filters.Bool', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        this.johnId = records.Users[0].id;

        records = [
            {attributes: {do_not_call: true, assigned_user_id: this.johnId}},
            {attributes: {do_not_call: true, assigned_user_id: this.johnId}},
            {attributes: {do_not_call: false, assigned_user_id: this.johnId}},
        ];

        yield Fixtures.create(records, {module: 'Contacts'});
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should filter records with truthy values', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            qs: {
                max_num: 3,
                filter: [{
                    do_not_call: 1,
                    assigned_user_id: this.johnId,
                }]
            },
        });

        expect(response).to.have.json('records', function(records) {
            expect(records).to.have.length(2);
            expect(records[0]).to.have.property('do_not_call', true);
        });
    });

    it('should filter records with falsy values', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            qs: {
                max_num: 2,
                filter: [{
                    do_not_call: 0,
                    assigned_user_id: this.johnId,
                }],
            },
        });

        expect(response).to.have.json('records', function(records) {
            expect(records).to.have.length(1);
            expect(records[0]).to.have.property('do_not_call', false);
        });
    });
});

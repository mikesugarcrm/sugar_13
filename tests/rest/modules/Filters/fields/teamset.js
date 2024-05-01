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

describe('Filters.Teamset', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        this.johnId = records.Users[0].id;

        records = [
            {attributes: {}},
            {attributes: {}},
            {attributes: {}},
        ];
        records = yield Fixtures.create(records, {module: 'Teams'});

        [this.team1, this.team2, this.team3] = records.Teams;

        records = [
            {attributes: {team_name: [{id: this.team1.id}], assigned_user_id: this.johnId}},
            {attributes: {team_name: [{id: this.team2.id}], assigned_user_id: this.johnId}},
            {attributes: {team_name: [{id: this.team3.id}], assigned_user_id: this.johnId}},
        ];

        this.records = yield Fixtures.create(records, {module: 'Accounts'});

        [this.account1, this.account2, this.account3] = this.records.Accounts;
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should filter records whose field value is any of the given values', function*() {
        let response = yield Agent.as('John').get('Accounts', {
            max_num: 3,
            qs: {
                filter: [{
                    assigned_user_id: {'$in': [this.johnId]},
                    team_id: {'$in': [this.team1.id, this.team2.id]},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            let account1 = records.find((r) => r.id === this.account1.id);
            let account2 = records.find((r) => r.id === this.account2.id);

            expect(records).to.have.length(2);
            expect(account1).to.exist;
            expect(account2).to.exist;
        });
    });

    it('should filter records whose field value is not any of the given values', function*() {
        let response = yield Agent.as('John').get('Accounts', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    team_id: {'$not_in': [this.team1.id, this.team2.id]},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            let account3 = records.find((r) => r.id === this.account3.id);

            expect(records).to.have.length(1);
            expect(account3).to.exist;
        });
    });
});

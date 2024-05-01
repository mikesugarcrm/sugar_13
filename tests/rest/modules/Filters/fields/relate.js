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

describe('Filters.Relate', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        this.johnId = records.Users[0].id;

        let attributes = {assigned_user_id: this.johnId};

        records = [
            {module: 'Contacts', attributes: attributes},
            {module: 'Contacts', attributes: attributes},
            {module: 'Contacts', attributes: attributes},
            {module: 'Accounts', attributes: attributes},
            {module: 'Accounts', attributes: attributes},
        ];

        this.records = yield Fixtures.create(records);

        [this.relAccount1, this.relAccount2] = this.records.Accounts;
        [this.contact1, this.contact2, this.contact3] = this.records.Contacts;

        yield Promise.all([
            Fixtures.link(this.contact1, 'accounts', this.relAccount1),
            Fixtures.link(this.contact2, 'accounts', this.relAccount2),
        ]);
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should filter records whose field value is any of the given values', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            max_num: 3,
            qs: {
                filter: [{
                    account_id: {'$in': [this.relAccount1.id, this.relAccount2.id]},
                    assigned_user_id: this.johnId,
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            let contact1 = records.find((r) => r.id === this.contact1.id);
            let contact2 = records.find((r) => r.id === this.contact2.id);

            expect(records).to.have.length(2);
            expect(contact1).to.exist;
            expect(contact2).to.exist;
        });
    });

    // FIXME TR-16193: Filtering with a related record "not in" returns empty results
    it.skip('should filter records whose field value is not any of the given values', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    account_id: {'$not_in': [this.relAccount1.id, this.relAccount2.id]},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            let contact3 = records.find((r) => r.id === this.contact3.id);

            expect(records).to.have.length(1);
            expect(contact3).to.exist;
        });
    });
});

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

describe('Filters.Tag', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        this.johnId = records.Users[0].id;

        this.tag1 = 'filterTag1';
        this.tag2 = 'filterTag2';

        records = [
            {module: 'Contacts', attributes: {assigned_user_id: this.johnId, tag: [this.tag1]}},
            {module: 'Contacts', attributes: {assigned_user_id: this.johnId, tag: [this.tag1, this.tag2]}},
            {module: 'Contacts', attributes: {assigned_user_id: this.johnId}},
        ];

        this.records = yield Fixtures.create(records);

        [this.contact1, this.contact2] = this.records.Contacts;
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should filter records whose field value is any of the given values', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            max_num: 3,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    tag: {'$in': [this.tag1, this.tag2]},
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

    it('should filter records whose field value is not any of the given values', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    tag: {'$not_in': [this.tag1, this.tag2]},
                }]
            },
        });

        expect(response).to.have.json('records', function(records) {
            expect(records).to.have.length(1);
            expect(records[0].tag).to.be.empty;
        });
    });

    it('should filter records whose field value is empty', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    tag: {'$empty': ''},
                }]
            },
        });

        expect(response).to.have.json('records', function(records) {
            expect(records).to.have.length(1);
            expect(records[0].tag).to.be.empty;
        });
    });

    it('should filter records whose field value is not empty', function*() {
        let response = yield Agent.as('John').get('Contacts', {
            max_num: 3,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    tag: {'$not_empty': ''},
                }]
            },
        });

        expect(response).to.have.json('records', function(records) {
            expect(records).to.have.length(2);
            expect(records[0].tag).to.not.be.empty;
            expect(records[1].tag).to.not.be.empty;
        });
    });
});

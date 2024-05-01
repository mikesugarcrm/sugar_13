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
const expect = require('chakram').expect;

describe('DataPrivacy', function() {
    before(function*() {
        let records = [
            {attributes: {user_name: 'John', last_name: 'Smith', status: 'Active'}}
        ];

        let users = yield Fixtures.create(records, {module: 'Users'});
        this.johnId = users.Users[0].id;

        records = [
            {attributes: {
                name: 'DPR1',
                type: 'Receipt of consent',
                source: 'Email',
                date_due: '2020-01-01',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                name: 'DPR2',
                type: 'Request for data privacy and usage policy',
                source: 'Phone',
                date_due: '2020-01-01',
                assigned_user_id: this.johnId
            }}
        ];

        let dprs = yield Fixtures.create(records, {module: 'DataPrivacy'});
        [this.dpr1, this.dpr2] = dprs.DataPrivacy;

        records = [
            {attributes: {
                last_name: 'Lead1',
                converted: 0,
                lead_source: 'Self Generated',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'Lead2',
                converted: 0,
                lead_source: 'Cold Call',
                assigned_user_id: this.johnId
            }}
        ];

        let leads = yield Fixtures.create(records, {module: 'Leads'});
        [this.lead1, this.lead2] = leads.Leads;

        records = [
            {attributes: {
                last_name: 'Con1',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'Con2',
                assigned_user_id: this.johnId
            }}
        ];

        let cons = yield Fixtures.create(records, {module: 'Contacts'});
        [this.con1, this.con2] = cons.Contacts;

        records = [
            {attributes: {
                last_name: 'Tar1',
                assigned_user_id: this.johnId
            }},
            {attributes: {
                last_name: 'Tar2',
                assigned_user_id: this.johnId
            }}
        ];

        let tars = yield Fixtures.create(records, {module: 'Prospects'});
        [this.tar1, this.tar2] = tars.Prospects;

        yield Promise.all([
            Fixtures.link(this.lead1, 'dataprivacy', this.dpr1),
            Fixtures.link(this.dpr2, 'leads', this.lead2),
            Fixtures.link(this.con1, 'dataprivacy', this.dpr1),
            Fixtures.link(this.dpr2, 'contacts', this.con2),
            Fixtures.link(this.tar1, 'dataprivacy', this.dpr1),
            Fixtures.link(this.dpr2, 'prospects', this.tar2),
        ]);
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should link dprs to leads, contacts, and targets', function*() {
        let response = yield Agent.as('John').get('Leads/' + this.lead1.id + '/link/dataprivacy?view=subpanel-list');
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].name).to.be.equal('DPR1');
        });

        response = yield Agent.as('John').get('Contacts/' + this.con1.id + '/link/dataprivacy?view=subpanel-list');
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].name).to.be.equal('DPR1');
        });

        response = yield Agent.as('John').get('Prospects/' + this.tar1.id + '/link/dataprivacy?view=subpanel-list');
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].name).to.be.equal('DPR1');
        });

        response = yield Agent.as('John').get('DataPrivacy/' + this.dpr2.id + '/link/leads?view=subpanel-list');
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].last_name).to.be.equal('Lead2');
        });

        response = yield Agent.as('John').get('DataPrivacy/' + this.dpr2.id + '/link/contacts?view=subpanel-list');
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].last_name).to.be.equal('Con2');
        });

        response = yield Agent.as('John').get('DataPrivacy/' + this.dpr2.id + '/link/prospects?view=subpanel-list');
        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].last_name).to.be.equal('Tar2');
        });
    });
});

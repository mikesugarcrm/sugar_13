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

describe('Archived Emails', function() {
    before(function*() {
        // Create normal users.
        const response = yield Fixtures.create([{
            module: 'Users',
            attributes: {
                user_name: 'bob',
                status: 'Active',
                email: [{
                    email_address: 'bob@example.com',
                    primary_address: true,
                    invalid_email: false,
                    opt_out: false,
                }],
            },
        }, {
            module: 'Users',
            attributes: {
                user_name: 'pam',
                status: 'Active',
                email: [{
                    email_address: 'pam@example.com',
                    primary_address: true,
                    invalid_email: false,
                    opt_out: false,
                }],
            }
        }]);
        this.bob = response.Users[0];
        this.pam = response.Users[1];
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should create an archived email', function*() {
        // Create a contact.
        let response = yield Fixtures.create({
            module: 'Contacts',
            attributes: {
                first_name: 'Stan',
                last_name: 'Miller',
                email: [{
                    email_address: 'smiller@example.com',
                    primary_address: true,
                    invalid_email: false,
                    opt_out: false,
                }],
            },
        });
        const contact = response.Contacts[0];
        const content = '<p>some <em>text</em> and &lt;ab@cd.com&gt; and &lt;i&gt;text&lt;/i&gt;</p>';

        // Use view=record to force the from, to, cc, bcc, and attachments
        // collection fields to be included in the response.
        try {
            response = yield Agent.as('bob').post('Emails?view=record', {
                module: 'Emails',
                state: 'Archived',
                name: content,
                description_html: content,
                from: {
                    create: [{
                        parent_type: contact._module,
                        parent_id: contact.id,
                    }],
                },
                to: {
                    create: [{
                        parent_type: this.bob._module,
                        parent_id: this.bob.id,
                    }],
                },
                cc: {
                    create: [{
                        parent_type: this.pam._module,
                        parent_id: this.pam.id,
                    }],
                },
                bcc: {
                    create: [{
                        parent_type: 'Users',
                        parent_id: '1',
                    }],
                },
                parent_type: contact._module,
                parent_id: contact.id,
                assigned_user_id: this.bob.id,
            });
        } catch (error) {
            response = error;
        }

        expect(response).to.have.status(200);

        // Delete the email.
        const email = response.response.body;
        yield Agent.as(Agent.ADMIN).delete(['Emails', email.id].join('/'));

        expect(email.state).to.equal('Archived');
        expect(email.name).to.equal(content);
        expect(email.description_html).to.equal(content);
        //FIXME: Should be "some text and <ab@cd.com> and <i>text</i>" after MAR-5050.
        expect(email.description).to.equal('some text and &lt;ab@cd.com&gt; and &lt;i&gt;text&lt;/i&gt;');
        expect(email.parent.type).to.equal('Contacts');
        expect(email.parent.id).to.equal(contact.id);
        expect(email.parent.name).to.equal(contact.name);

        // From
        expect(email.from_collection.records.length).to.equal(1);
        expect(email.from_collection.records[0].parent.type).to.equal('Contacts');
        expect(email.from_collection.records[0].parent.id).to.equal(contact.id);
        expect(email.from_collection.records[0].parent.name).to.equal(contact.name);
        expect(email.from_collection.records[0].email_address).to.equal('smiller@example.com');

        // To
        expect(email.to_collection.records.length).to.equal(1);
        expect(email.to_collection.records[0].parent.type).to.equal('Users');
        expect(email.to_collection.records[0].parent.id).to.equal(this.bob.id);
        expect(email.to_collection.records[0].parent.name).to.equal(this.bob.name);
        expect(email.to_collection.records[0].email_address).to.equal('bob@example.com');

        // CC
        expect(email.cc_collection.records.length).to.equal(1);
        expect(email.cc_collection.records[0].parent.type).to.equal('Users');
        expect(email.cc_collection.records[0].parent.id).to.equal(this.pam.id);
        expect(email.cc_collection.records[0].parent.name).to.equal(this.pam.name);
        expect(email.cc_collection.records[0].email_address).to.equal('pam@example.com');

        // BCC
        expect(email.bcc_collection.records.length).to.equal(1);
        expect(email.bcc_collection.records[0].parent.type).to.equal('Users');
        expect(email.bcc_collection.records[0].parent.id).to.equal('1');
    });
});

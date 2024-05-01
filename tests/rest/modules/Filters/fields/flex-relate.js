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

describe('Filters.FlexRelate', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        this.johnId = records.Users[0].id;

        let attributes = {assigned_user_id: this.johnId};

        records = [
            {module: 'Notes', attributes: attributes},
            {module: 'Notes', attributes: attributes},
            {module: 'Accounts', attributes: attributes},
        ];

        this.records = yield Fixtures.create(records);
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should filter records with given values', function*() {
        let noteId = this.records.Notes[0].id;
        let parentId = this.records.Accounts[0].id;
        let parentType = 'Accounts';

        yield Agent.as(Agent.ADMIN).put(`Notes/${noteId}`, {
            parent_id: parentId,
            parent_type: parentType
        });

        let response = yield Agent.as('John').get('Notes', {
            qs: {
                max_num: 2,
                filter: [{
                    assigned_user_id: this.johnId,
                    parent_id: parentId,
                    parent_type: parentType,
                }]
            },
        });

        expect(response).to.have.json('records', function(records) {
            expect(records).to.have.length(1);
            expect(records[0]).to.have.property('parent_id', parentId);
            expect(records[0]).to.have.property('parent_type', parentType);
        });
    });
});

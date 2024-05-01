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

describe('Reports', function() {
    before(function*() {
        let users = [
            {
                attributes: {
                    user_name: 'John',
                    status: 'Active',
                }
            }
        ];

        yield Fixtures.create(users, {module: 'Users'});
        this.john = Agent.as('John');
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    describe('Updating a Report', function() {
        before(function*() {
            let report = {

                name: 'Report1',
                report_type: 'summary',
                module: 'Accounts',

            };
            let response = yield this.john.post('Reports', report);
            this.id = response.body.id;
        });

        after(function*() {
            yield this.john.delete('Reports/' + this.id);
        });

        it('should let you edit a report name', function*() {
            let response = yield this.john.put('Reports/' + this.id, {name: 'Changed Name'});
            expect(response).to.have.status(200);
            expect(response.body.name).to.equal('Changed Name');

        });
    });

    describe('Creating a Report and checking the module field is set', function() {
        after(function*() {
            yield this.john.delete('Reports/' + this.id);
        });

        it('should have the target module field set correctly', function*() {
            //let response = yield this.john.put('Reports/' + this.id, {name: 'Changed Name'});
            let report = {
                name: 'Report1',
                report_type: 'summary',
                module: 'Accounts',

            };
            let response = yield this.john.post('Reports', report);
            expect(response).to.have.status(200);
            this.id = response.body.id;
            expect(response.body.module).to.equal('Accounts');
        });
    });
});

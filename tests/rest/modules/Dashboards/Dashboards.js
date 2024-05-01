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

describe('Dashboards', function() {
    before(function*() {
        let users = [
            {
                attributes: {
                    user_name: 'John',
                    status: 'Active',
                }
            },
            {
                attributes: {
                    user_name: 'Jane',
                    status: 'Active',
                }
            }
        ];

        yield Fixtures.create(users, {module: 'Users'});
        this.john = Agent.as('John');
        this.jane = Agent.as('Jane');
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    describe('Accessing one\'s own dashboard', function() {
        it('should allow user to manage his own dashboard', function*() {
            let testDashboard = {
                definition: {
                    name: 'TestDashboard'
                },
                record: null
            };

            // create test
            let response = yield this.john.post('Dashboards', testDashboard.definition);
            expect(response).to.have.status(200);
            testDashboard.record = response.response.body;

            // read test
            response = yield this.john.get('Dashboards/' + testDashboard.record.id);
            expect(response).to.have.status(200);

            // edit test
            response = yield this.john.put('Dashboards/' + testDashboard.record.id, {name: 'UpdatedTestDashboard'});
            expect(response).to.have.status(200);
            expect(response.response.body.name).to.equal('UpdatedTestDashboard');

            // delete test
            response = yield this.john.delete('Dashboards/' + testDashboard.record.id);
            expect(response).to.have.status(200);

            // delete test verification
            try {
                yield this.john.get('Dashboards/' + testDashboard.record.id);
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });
    });

    describe('Accessing someone else\'s dashboard', function() {
        before(function*() {
            let response = yield this.john.post('Dashboards', {name: 'JohnsDashboard'});
            this.johnsDashboard = response.response.body;
            this.johnsDashboardEndpoint = 'Dashboards/' + this.johnsDashboard.id;
        });

        after(function*() {
            yield this.john.delete(this.johnsDashboardEndpoint);
        });

        it('should not let a user view another user\'s dashboard', function*() {
            try {
                yield this.jane.get(this.johnsDashboardEndpoint);
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });

        it('should not let a user edit another user\'s dashboard', function*() {
            try {
                yield this.jane.put(this.johnsDashboardEndpoint, {name: 'UpdateName'});
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });

        it('should not let a user delete another user\'s dashboard', function*() {
            try {
                yield this.jane.delete(this.johnsDashboardEndpoint);
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });
    });

    describe('default field', function() {
        before(function*() {
            let response = yield Agent.as('John').post('Dashboards', {name: 'JohnsDashboard'});
            this.johnsDashboard = response.response.body;
            this.johnsDashboardEndpoint = `Dashboards/${this.johnsDashboard.id}?fields=default_dashboard`;
        });

        after(function*() {
            yield Agent.as('John').delete(this.johnsDashboardEndpoint);
        });

        it('should be readonly for regular users', function*() {
            let response = yield Agent.as('John').get(this.johnsDashboardEndpoint);
            expect(response).to.have.status(200);
            expect(response.response.body._acl.fields.default_dashboard).to.eql({
                create: 'no',
                write: 'no',
            });
        });

        it('should be editable for admins', function*() {
            let response = yield Agent.as(Agent.ADMIN).get(this.johnsDashboardEndpoint);
            expect(response).to.have.status(200);
            expect(response.response.body._acl.fields).to.be.empty;
        });
    });
});

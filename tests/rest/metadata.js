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
const chakram = require('chakram');
const expect = chakram.expect;
const {Agent, Fixtures} = require('@sugarcrm/thorn');

describe('Metadata', function() {
    before(function*() {
        this.url = `${process.env.THORN_SERVER_URL}/rest/v10`;

        let users = {attributes: {user_name: 'John', status: 'Active'}};

        yield Fixtures.create(users, {module: 'Users'});
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should get the public metadata', function*() {
        // Make sure we can get the public metadata without access token.
        let response = yield chakram.get(`${this.url}/metadata/public`);

        expect(response).to.have.status(200);
        expect(response).to.have.json('modules', function(modules) {
            expect(modules).to.be.an('object');
        });
        expect(response).to.have.json('fields', function(fields) {
            expect(fields).to.be.an('object');
        });
        expect(response).to.have.json('views', function(views) {
            expect(views).to.be.an('object');
        });
        expect(response).to.have.json('layouts', function(layouts) {
            expect(layouts).to.be.an('object');
        });
        expect(response).to.have.json('labels.default', 'en_us');

        let [source, language] = yield Promise.all([
            chakram.get(`${process.env.THORN_SERVER_URL}/${response.body.jssource}`),
            chakram.get(`${process.env.THORN_SERVER_URL}/${response.body.labels.en_us}`),
        ]);

        expect(source).to.have.status(200);
        expect(language).to.have.status(200);
    });

    it('should properly handle private metadata access', function*() {
        // Make sure we can't get the private metadata without an access token.
        let response = yield chakram.get(`${this.url}/metadata/`);

        expect(response).to.have.status(401);

        // Make sure we can get the private metadata with a logged in user.
        response = yield Agent.as('John').get(`metadata`);

        expect(response).to.have.status(200);
        expect(response).to.have.json('full_module_list', function(fullModuleList) {
            expect(fullModuleList).to.be.an('object');
            expect(fullModuleList).to.not.be.empty;
        });

        // FIXME: This test should pass. Currently it returns a 200 OK, that's a security issue.
        // let source = yield chakram.get(`${process.env.THORN_SERVER_URL}/${response.body.jssource}`);
        // expect(source).to.have.status(404);

        source = yield chakram.get(`${process.env.THORN_SERVER_URL}/${response.body.jssource_public}`);
        expect(source).to.have.status(200);

        expect(response).to.have.json('modules', function(modules) {
            expect(modules).to.be.an('object');
            expect(modules).to.not.be.empty;
        });
        expect(response).to.have.json('fields', function(fields) {
            expect(fields).to.be.an('object');
        });
        expect(response).to.have.json('views', function(views) {
            expect(views).to.be.an('object');
        });
        expect(response).to.have.json('layouts', function(layouts) {
            expect(layouts).to.be.an('object');
        });
    });

    it('should get filtered metadata', function*() {
        // Make sure we can filter the metadata by type of data.
        let response = yield Agent.as('John').get('metadata', {
            qs: {
                type_filter: 'full_module_list,config'
            },
        });

        expect(response).to.have.status(200);
        expect(response).to.have.json('full_module_list', function(fullModuleList) {
            expect(fullModuleList).to.be.an('object');
            expect(fullModuleList).to.not.be.empty;
        });
        expect(response).to.have.json('config', function(currencies) {
            expect(currencies).to.be.an('object');
            expect(currencies).to.not.be.empty;
        });
        expect(response).to.not.have.json('modules');

        // Grab 4 available modules from the metadata.
        let moduleList = response.body.full_module_list;
        let module1 = moduleList[Object.keys(moduleList)[0]];
        let module2 = moduleList[Object.keys(moduleList)[1]];
        let module3 = moduleList[Object.keys(moduleList)[2]];
        let module4 = moduleList[Object.keys(moduleList)[3]];

        // Make sure we can get specific module metadata.
        response = yield Agent.as('John').get('metadata', {
            qs: {
                module_filter: `${module1},${module2}`
            },
        });

        expect(response).to.have.json('modules', function(modules) {
            expect(modules[module1]).to.not.be.undefined;
            expect(modules[module2]).to.not.be.undefined;
            expect(modules[module3]).to.be.undefined;
            expect(modules[module4]).to.be.undefined;
        });
    });
});

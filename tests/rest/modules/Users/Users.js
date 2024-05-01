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

describe('Users', function() {
    before(function*() {
        this.url = `${process.env.THORN_SERVER_URL}/rest/v10`;
        let response = yield chakram.post(`${this.url}/oauth2/token`,
            {
                'grant_type': 'password',
                'username': process.env.THORN_ADMIN_USERNAME,
                'password': process.env.THORN_ADMIN_PASSWORD,
                'client_id': 'sugar',
                'platform': 'base',
                'client_secret': '',
            }
        );

        this.adminToken = response.body.access_token;
    });

    it('should create an active user and be able to login', function*() {
        // Create a user
        let response = yield chakram.post(`${this.url}/Users`,
            {
                'user_name': 'yvan',
                'last_name': 'leterrible',
                'user_hash': '123abc',
                'status': 'Active',
            },
            {
                headers: {
                    'OAuth-Token': this.adminToken
                }
            }
        );
        expect(response).to.have.status(200);
        let userId = response.body.id;

        // Make sure login with valid credentials works.
        response = yield chakram.post(`${this.url}/oauth2/token`,
            {
                'grant_type': 'password',
                'username': 'yvan',
                'password': '123abc',
                'client_id': 'sugar',
                'platform': 'base',
                'client_secret': '',
            }
        );

        expect(response).to.have.status(200);
        expect(response).to.have.json('access_token' , function(accessToken) {
            expect(accessToken).to.have.lengthOf(36);
        });
        expect(response).to.have.json('refresh_token' , function(refreshToken) {
            expect(refreshToken).to.have.lengthOf(36);
        });
        expect(response).to.have.json('download_token' , function(downloadToken) {
            expect(downloadToken).to.have.lengthOf(36);
        });
        expect(response).to.comprise.of.json(
            {
                expires_in: 3600,
                token_type: 'bearer',
            }
        );
        expect(response).to.have.json('refresh_expires_in' , function(refeshExpiresIn) {
            // Normally the server returns 1209600 but sometimes it returns
            // 1209599. Hence we need to use `above`.
            expect(refeshExpiresIn).to.be.above(1209500);
        });

        let userToken = response.body.access_token;
        // Make sure the user is able to access his record data.
        response = yield chakram.get(`${this.url}/Users/${userId}`,
            {
                headers: {
                    'OAuth-Token': userToken,
                },
            }
        );

        expect(response).to.have.status(200);
        expect(response).to.have.json('last_name', 'leterrible');

        // Login with invalid password
        response = yield chakram.post(`${this.url}/oauth2/token`,
            {
                'grant_type': 'password',
                'username': 'yvan',
                'password': '1234abc',
                'client_id': 'sugar',
                'platform': 'base',
                'client_secret': '',
            }
        );

        expect(response).to.have.status(401);
        expect(response).to.have.json('error', 'need_login');
        expect(response).to.have.json('error_message', 'You must specify a valid username and password.');

        // Delete the user we created.
        yield chakram.delete(`${this.url}/Users/${userId}`, {},
            {
                headers: {
                    'OAuth-Token': this.adminToken,
                },
            }
        );

        // Make sure the user is deleted
        response = yield chakram.get(`${this.url}/Users/${userId}`,
            {
                headers: {
                    'OAuth-Token': this.adminToken,
                },
            }
        );

        expect(response).to.have.status(404);
    });

    it('should fail to login with an invalid username', function*() {
        let response = yield chakram.post(`${this.url}/oauth2/token`,
            {
                'grant_type': 'password',
                'username': 'yvano',
                'password': '123abc',
                'client_id': 'sugar',
                'platform': 'base',
                'client_secret': '',
            }
        );

        expect(response).to.have.status(401);
        expect(response).to.have.json('error', 'need_login');
        expect(response).to.have.json('error_message', 'You must specify a valid username and password.');
    });

    it('should create an inactive user and not be able to login', function*() {
        // Create an inactive user
        let response = yield chakram.post(`${this.url}/Users`,
            {
                'user_name': 'yvan',
                'last_name': 'leterrible',
                'user_hash': '123abc',
                'status': 'Inactive',
            },
            {
                headers: {
                    'OAuth-Token': this.adminToken
                }
            }
        );
        let userId = response.body.id;

        expect(response).to.have.status(200);

        // Make sure login is denied
        response = yield chakram.post(`${this.url}/oauth2/token`,
            {
                'grant_type': 'password',
                'username': 'yvan',
                'password': '123abc',
                'client_id': 'sugar',
                'platform': 'base',
                'client_secret': '',
            }
        );

        expect(response).to.have.status(401);

        // Delete the user we created.
        yield chakram.delete(`${this.url}/Users/${userId}`, {},
            {
                headers: {
                    'OAuth-Token': this.adminToken,
                },
            }
        );
    });
});

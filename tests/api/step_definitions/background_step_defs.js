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
const {Given, After, Before} = require('cucumber');
const app = require('../init');

Before(async function() {
    app.featureData = {};
    app.tempData = {};
});

After({timeout: 60 * 1000}, async function() {
    for (let index in app.featureData) {
        let record = app.featureData[index];
        await(new Promise(function(resolve, reject) {
            app.SugarApi.records('delete', record.module, {id: record.id}, null,
            {
                success: function(data) {
                    delete app.featureData[index];

                    return resolve(data);
                },
                error: function(data) {
                    console.log(`failed to delete ${record.module} : ${record.id}`);
                    console.log(data);
                    delete app.featureData[index];

                    return reject(data);
                },
            },
        null);
        }));
    }
});

Given('I am logged in',  {timeout: 60 * 1000}, async function() {
    let credentials = {username: 'admin', password: 'asdf'};
    await(new Promise(function(resolve, reject) {
        app.SugarApi.login(credentials, null, {
            success: function(data) {
                return resolve(data);
            },
            error: function(data) {
                console.log('failed login');
                return reject(data);
            },
        });
    }));

    if (Object.keys(app.metaData).length) {
        return;
    }
    return await(new Promise(function(resolve, reject) {
        app.SugarApi.getMetadata({callbacks: {
                success: function(data) {
                    Object.assign(app.metaData, data);

                    return resolve(data);
                },
                error: function(data) {
                    console.log('failed to retrieve metadata for instance');
                    return reject(data);
                },
            }});
    }));
});

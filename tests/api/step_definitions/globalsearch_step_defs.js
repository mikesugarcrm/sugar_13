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

const assert = require('assert');
const {When, Then} = require('cucumber');
const app  = require('../init');

When('I query using terms {string} with modules list {string}',  async function(searchTerms, moduleList) {
    let payload = {};

    await (new Promise(function(resolve, reject) {
        app.SugarApi.search(
            {
                q: searchTerms,
                module_list: moduleList,
            },
            {
                success: function(data) {
                    payload = data;
                    return resolve(data);
                },
                error: function(data) {
                    console.log('fail search step');
                    return reject(data);
                },
            },
            null);
    }));
    app.tempData.temp = payload;
});

function sleep(ms) {
    return new Promise(resolve=> {
        setTimeout(resolve, ms);
    });
}

const getSearchData = async function(dataTable, queryNum) {
    let values = dataTable.rawTable[queryNum];
    let payload = {};
    let query = values[0];
    let modList = values[1];
    await (new Promise(function(resolve, reject) {
        app.SugarApi.search(
            {
                q: query,
                module_List: modList,
            },
            {
                success: function(data) {
                    payload = data;
                    return resolve(data);
                },
                error: function(data) {
                    console.log('fail search step');
                    return reject(data);
                },
            },
            null);
    }));
    let numQueryReturn = payload.records.length;
    let expected = values[2];
    let expectedFieldVal = values[3];
    let expectedField = values[4];
    let numQueryExpected = parseInt(expected);
    let res = payload.records.filter(x => x[expectedField] === expectedFieldVal);
    assert(res.length > 0);
    if (numQueryReturn !== numQueryExpected) {
        console.log(app.featureData);
        console.log(app.featureData.length);
    }
    assert.equal(numQueryReturn, numQueryExpected, `failed assertion for ${numQueryReturn}, ${numQueryExpected}`);
};

Then('the following queries should have the following results:',  {timeout: 60 * 1000}, async function(dataTable) {
    let numOfQueries = dataTable.rawTable.length;
    //wait one second to allow Es to catch up
    sleep(1000);
    let count;
    for (count = 1; count < numOfQueries; count = count + 1) {
        await getSearchData(dataTable, count);
    }
});





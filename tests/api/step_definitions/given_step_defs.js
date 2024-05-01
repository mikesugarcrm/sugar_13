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
const {Given} = require('cucumber');
const app  = require('../init');

const createOneRecord = async function(item, dataTable, valueVal) {
    let labels = dataTable.rawTable[0];
    let values = dataTable.rawTable[valueVal];
    let params = {};
    for (let i = 0; i < labels.length; i++) {
        if (labels[i].charAt(0) === '*') {
            labels[i] = labels[i].slice(1);
        }
        params[labels[i]] = values[i];
    }

    return await (new Promise(function(resolve, reject) {
        app.SugarApi.records('create', item, params, null,
            {
                success: function(data) {
                    if (!app.featureData[values[0]]) {
                        app.featureData[values[0]] = {};
                    }
                    app.featureData[values[0]] = {module: item, id: data.id};
                    return resolve(data);
                },
                error: function(data) {
                    console.log('failed creation');
                    return reject(data);
                },
            },
            null);
    }));
};

Given('{word} records exist:', {timeout: 60 * 1000}, async function(item, dataTable) {
    let numOfRecords = dataTable.rawTable.length;
    let count = 0;
    for (count = 1; count < numOfRecords; count = count + 1) {
        await createOneRecord(item, dataTable, count);
    }
});

Given('{word} records exist related via {word} link to \(*\){word}:', {timeout: 60 * 1000},
    async function(item, item2, item3, dataTable) {
        let labels = dataTable.rawTable[0];
        let values = dataTable.rawTable[1];
        let params = {};
        for (let i = 0; i < labels.length; i++) {
            if (labels[i].charAt(0)  === '*') {
                labels[i] = labels[i].slice(1);
            }
            params[labels[i]] = values[i];
        }
        let payload;
        await (new Promise(function(resolve, reject) {
            app.SugarApi.records('create', item, params, null,
                {
                    success: function(data) {
                        payload = data;
                        app.featureData[values[0]] = {module: item, id: data.id};
                        return resolve(data);
                    },
                    error: function(data) {
                        console.log('failed creation opp');
                        return reject(data);
                    },
                },
                null);
        }));
        let relatedId = payload.id;
        let id = app.featureData[item3].id;
        let dataParams = {id: id, link: item2, relatedId: relatedId, related: payload};

        return await (new Promise(function(resolve, reject) {
            app.SugarApi.relationships('create', app.featureData[item3].module, dataParams, null,
                {
                    success: function(data) {
                        return resolve(data);
                    },
                    error: function(data) {
                        console.log('failed relationship');
                        return reject(data);
                    },
                },
                null);
        }));
    });


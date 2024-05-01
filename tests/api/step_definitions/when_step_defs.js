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

const {When} = require('cucumber');
const app  = require('../init');

When('I update {word} \(*\){word} with the following values:', async function(moduleType, name, dataTable) {
    let labels = dataTable.rawTable[0];
    let values = dataTable.rawTable[1];
    let params = {};
    for (let i = 0; i < labels.length; i = i + 1) {
        if (labels[i].charAt(0) === '*') {
            labels[i] = labels[i].slice();
        }
        let fieldDef = app.getFieldDef(moduleType, labels[i]);
        if (fieldDef) {
            if (fieldDef.type === 'relate') {
                labels[i] = fieldDef.id_name;
                values[i] = app.featureData[values[i]].id;
            }
            params[labels[i]] = values[i];
        } else {
            console.warn(`\nunable to get field def in when for ${moduleType} field ${labels[i]}\n`);
        }
    }
    params.id = app.featureData[name].id;

    return await(new Promise(function(resolve, reject) {
        app.SugarApi.records('update', moduleType, params, null,
            {
                success: function(data) {
                    return resolve(data);
                },
                error: function(data) {
                    console.log('update failure');
                    return reject(data);
                },
            },
            null);
    }));
});

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
const {Then} = require('cucumber');
const app  = require('../init');

Then('{word} \(*\){word} should have the following values:', async function(moduleType, name, dataTable) {
    let labels = dataTable.rawTable.map(function(arr) {
        return arr[0];
    });
    let values = dataTable.rawTable.map(function(arr) {
        return arr[1];
    });

    labels.shift();
    values.shift();

    let params = {};
    let fieldDefs = {};

    for (let i = 0; i < labels.length; i++) {
        if (labels[i].charAt(0)  === '*') {
            labels[i] = labels[i].slice(1);
        }
        let fieldDef = fieldDefs[labels[i]] = app.getFieldDef(moduleType, labels[i]);
        if (fieldDef) {
            if (fieldDef.type === 'relate' && app.featureData[values[i]]) {
                labels[i] = fieldDef.id_name;
                values[i] = app.featureData[values[i]].id;
            }
            params[labels[i]] = values[i];
        } else {
            console.warn(`\nunable to get field def in then for ${moduleType} field ${labels[i]}\n`);
        }
    }

    let payload = {};
    let rec = app.featureData[name];

    await(new Promise(function(resolve, reject) {
        app.SugarApi.records('read', moduleType, {id: rec.id}, null,
        {
            success: function(data) {
                payload = data;
                return resolve(data);
            },
            error: function(data) {
                console.log('data_two');
                console.log('failed read rli');
                return reject(data);
            },
        },
        null);
    }));

    for (let param in params) {
        if (fieldDefs[param] && ['currency', 'int', 'decimal'].indexOf(fieldDefs[param].type) > -1) {
            let t = params[param].replace(/[^0-9\.]+/g, '');
            t = parseFloat(t);
            if (!isNaN(t)) {
                params[param] = t;
            }
            t = parseFloat(payload[param]);
            if (!isNaN(t)) {
                payload[param] = t;
            }
        }
        if (payload[param] !== params[param]) {
            console.log(fieldDefs[param]);
        }
        assert.equal(payload[param], params[param], `failed assertion on ${moduleType} ${param} field`);
    }
});

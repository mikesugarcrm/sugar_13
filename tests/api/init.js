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

global._ = require('underscore');
const {JSDOM} = require('jsdom');
const jsdom = new JSDOM('<DOCTYPE html>', {url: process.env.SERVER_CUCUMBER_URL});
const {window} = jsdom;
const {document} = window;
global.window = window;
global.document = document;
global.FormData = window.FormData;
global.$ = global.jQuery = require('jquery');

const Ventana = require('@sugarcrm/ventana');
const SugarApi = Ventana.getInstance({
    serverUrl: `${process.env.SERVER_CUCUMBER_URL}rest/v11_2`,
    platform: 'base',
    timeout: 30,
    clientID: 'sugar',
});

let featureData = {};
let metaData = {};
let tempData = {};

const getFieldDef = (module, fieldName) => {
    if (metaData && metaData.modules && metaData.modules[module] && metaData.modules[module].fields) {
        let fields = metaData.modules[module].fields;
        if (fields[fieldName]) {
            return fields[fieldName];
        }
    }
    return null;
};

module.exports = {
    SugarApi,
    featureData,
    metaData,
    tempData,
    getFieldDef,
};

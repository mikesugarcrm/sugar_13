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
(function(app) {

    app.augment('config', {

        appId: 'portal',
        env: 'dev',
        debugSugarApi: true,
        logger: {
            level: 'FATAL'
        },
        logWriter: 'ConsoleWriter',
        logFormatter: 'SimpleFormatter',

        // For tests our context is the runner which is one level lower!
        serverUrl: '../../../rest/v10',
        siteUrl: '../../..',

        serverTimeout: 30,
        maxQueryResult: 20,

        maxSearchQueryResult: 3,
        unsecureRoutes: ['signup', 'error'],
        platform: 'base',
        defaultModule: 'Cases',
        metadataTypes: [],

        orderByDefaults: {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            },
            'Bugs': {
                field: 'bug_number',
                direction: 'asc'
            },
            'Notes': {
                field: 'date_modified',
                direction: 'desc'
            }
        },

        additionalComponents: {
            'header-nav': {
                target: '#header-nav'
            },
            alert: {
                target: '#alert'
            }
        },

        displayModules: [
            'Bugs',
            'Cases',
            'KBDocuments'
        ],

        clientID: 'sugar',
        syncConfig: false,
        loadCss: false,
        allowedLinkSchemes: [
            'http',
            'https',
            'ftp',
            'mms'
        ],

    }, false);

})(SUGAR.App);

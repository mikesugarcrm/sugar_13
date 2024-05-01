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
if (!(fixtures)) {
    var fixtures = {};
}
// Make forecastsMetadata play nice if fixtures has already been defined for other tests
// so we dont overwrite data
if(!_.has(fixtures, 'metadata')) {
    fixtures.metadata = {};
}
if(!_.has(fixtures.metadata, 'modules')) {
    fixtures.metadata.modules = {};
}
if(!_.has(fixtures.metadata.modules, 'Forecasts')) {
    fixtures.metadata.modules.Forecasts = {};
}
if(!_.has(fixtures.metadata.modules, 'ForecastWorksheets')) {
    fixtures.metadata.modules.ForecastWorksheets = {
        '_hash': '12345678910asdgae',
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                name: {
                    audited: true,
                    dbType: "varchar",
                    full_text_search: {boost: 3},
                    len: 255,
                    link: true,
                    merge_filter: "selected",
                    name: "name",
                    type: "name",
                    unified_search: true,
                    vname: "LBL_SUBJECT"
                },
                "best_case": {
                    "name": "best_case",
                    "type": "currency"
                },
                "worst_case": {
                    "name": "worst_case",
                    "type": "currency"
                },
                "likely_case": {
                    "name": "likely_case",
                    "type": "currency"
                }
            }
    };
}
if(!_.has(fixtures.metadata.modules, 'ForecastManagerWorksheets')) {
    fixtures.metadata.modules.ForecastManagerWorksheets = {
        '_hash': '123456asdfaasdgae',
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                name: {
                    audited: true,
                    dbType: "varchar",
                    full_text_search: {boost: 3},
                    len: 255,
                    link: true,
                    merge_filter: "selected",
                    name: "name",
                    type: "name",
                    unified_search: true,
                    vname: "LBL_SUBJECT"
                },
                "quota": {
                    "name": "quota",
                    "type": "currency"
                },
                "best_case": {
                    "name": "best_case",
                    "type": "currency"
                },
                "worst_case": {
                    "name": "worst_case",
                    "type": "currency"
                },
                "likely_case": {
                    "name": "likely_case",
                    "type": "currency"
                },
                "best_case_adjusted": {
                    "name": "best_case_adjusted",
                    "type": "currency"
                },
                "worst_case_adjusted": {
                    "name": "worst_case_adjusted",
                    "type": "currency"
                },
                "likely_case_adjusted": {
                    "name": "likely_case_adjusted",
                    "type": "currency"
                }
            }
    };
}

if(!_.has(fixtures.metadata.modules, 'Opportunities')) {
    fixtures.metadata.modules.Opportunities = {
        '_hash': '12345678910asdgae',
            "config": {
                "opps_view_by": "RevenueLineItems"
            },
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                name: {
                    audited: true,
                    dbType: "varchar",
                    full_text_search: {boost: 3},
                    len: 255,
                    link: true,
                    merge_filter: "selected",
                    name: "name",
                    type: "name",
                    unified_search: true,
                    vname: "LBL_SUBJECT"
                },
                "best_case": {
                    "name": "best_case",
                    "type": "currency"
                },
                "worst_case": {
                    "name": "worst_case",
                    "type": "currency"
                },
                "amount": {
                    "name": "amount",
                    "type": "currency"
                },
                "sales_stage": {
                    "name": "sales_stage",
                    "vname": "LBL_SALES_STAGE",
                    "type": "enum",
                    "options": "sales_stage_dom",
                    "len": "255",
                    "audited": true,
                    "comment": "Indication of progression towards closure",
                    "merge_filter": "enabled",
                    "importable": "required",
                    "required": true,
                },
                "sales_status": {
                    "name": "sales_status",
                    "vname": "LBL_SALES_STATUS",
                    "type": "enum",
                    "options": "sales_status_dom",
                    "len": "255",
                    "audited": true,
                    "studio": false,
                },
                "closed_revenue_line_items": {
                    "name": "closed_revenue_line_items",
                    "vname": "LBL_CLOSED_RLIS",
                    "type": "int",
                    "enforced": true,
                    "studio": false,
                },
            }
    };
}

// Add config metadata
fixtures.metadata.modules.Forecasts.config = {
    "is_setup":1,
    "timeperiod_type":"fiscal",
    "timeperiod_interval":"Annual",
    "timeperiod_leaf_interval":"Quarter",
    "timeperiod_start_date":"2012-01-01",
    "timeperiod_shown_forward":4,
    "timeperiod_shown_backward":4,
    "forecast_ranges":"show_binary",
    "buckets_dom":"commit_stage_dom",
    "category_ranges":{
        "include":{
            "min":"70",
            "max":"100"
        },
        "exclude":{
            "min":"0",
            "max":"69"
        }
    },
    "sales_stage_won":["Closed Won"],
    "sales_stage_lost":["Closed Lost"],
    "show_worksheet_likely":1,
    "show_worksheet_best":1,
    "show_worksheet_worst":1,
    "show_projected_likely":1,
    "show_projected_best":1,
    "show_projected_worst":1,
    "show_print_button":1
}

if(!_.has(fixtures.metadata.modules, "RevenueLineItems")) {
    fixtures.metadata.modules.RevenueLineItems = {
        "_hash": "123456asdfaasdgae",
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                "name": {
                    "name": "name",
                    "vname": "LBL_NAME",
                    "dbType": "varchar",
                    "type": "name",
                    "len": "50",
                    "comment": "Name of the product",
                    "reportable": true,
                    "importable": "required"
                },
                "commit_stage": {
                    "name": "commit_stage",
                    "vname": "LBL_COMMIT_STAGE_FORECAST",
                    "type": "enum",
                    "default": "exclude",
                    "options": "commit_stage_binary_dom",
                    "len": "50",
                    "comment": "Forecast commit category: Include, Likely, Omit etc."
                },
            }
    }
}

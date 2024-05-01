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
describe('Sugar.Analytics', function() {
    var app;

    var oldConfig = SUGAR.App.config.analytics;

    SUGAR.App.analytics = SUGAR.App.analytics || {};
    SUGAR.App.analytics.connectors = SUGAR.App.analytics.connectors || {};
    SUGAR.App.analytics.connectors['FakeConnector']  = {
        initialized: false,
        connectorId: null,
        pageLog: [],
        eventLog: [],
        initialize: function() {
            this.initialized = true;
            return true;
        },
        start: function(id, options) {
            this.connectorId = id;
            return id;
        },
        trackPageView: function(pageUri) {
            this.pageLog.push(pageUri);
            return pageUri;
        },
        trackEvent: function(event) {
            this.eventLog.push(event);
            return event;
        },
        reset: function() {
            this.connectorId = null;
            this.pageLog = [];
            this.eventLog = [];
        },
        set: function() {}
    };

    // Config has to be here because sidecar modules are loaded before SugarTest app init.
    SUGAR.App.config.analytics = {
        enabled: true,
        connector: 'FakeConnector',
        id: 'FakeId'
    };

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        SUGAR.App.config.analytics = {
            enabled: true,
            connector: 'FakeConnector',
            id: 'FakeId',
        };
        // Called on "app:start" normally
        app.analytics.connector.start(app.config.analytics.id, app.config.analytics);
    });

    afterEach(function() {
        app.analytics.dispose();
        // App is already loaded with config, but cleanup anyways.
        SUGAR.App.config.analytics = oldConfig;
        SUGAR.App.analytics.connectors["FakeConnector"].reset();
    });

    it('should have initialized and start connector', function() {
        expect(SUGAR.App.analytics.connectors["FakeConnector"].initialized).toBeTruthy();
        expect(SUGAR.App.analytics.connectors["FakeConnector"].connectorId).toEqual("FakeId");
        expect(SUGAR.App.analytics.connector).toEqual(SUGAR.App.analytics.connectors["FakeConnector"]);
    });

    it('should track a page view', function() {
        app.analytics.trackPageView(SUGAR.App.analytics.connectors["FakeConnector"].connectorId + "/fakeURL");
        expect(SUGAR.App.analytics.connectors["FakeConnector"].pageLog[0]).toEqual(SUGAR.App.analytics.connectors["FakeConnector"].connectorId + "/fakeURL");
    });

    it('should track an event', function() {
        app.analytics.trackEvent("action", "category", "event", "value");
        expect(SUGAR.App.analytics.connectors["FakeConnector"].eventLog[0].category).toEqual("category");
    });
    it('should parse tracking strings', function(){
       var input = ['event:action.css','event:action', 'event1 event2 event3:action.css'];
        var result = {};
        var expected = [
            {
                action: 'action',
                value: 'css'
            },
            {
                action: 'action',
                value: null
            },
            {
                action: 'action',
                value: 'css'
            }
        ];
        _.each(input, function(inputStr, key){
            var result =app.analytics._parseTrackTag(inputStr);
            expect(result).toEqual(expected[key])
        });
    });
});

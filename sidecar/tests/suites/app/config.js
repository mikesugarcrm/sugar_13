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
describe('Application configuration', function() {

    it('should have all properties defined', function() {
        let config = SUGAR.App.config;

        expect(config.appId).toBeDefined();
        expect(config.env).toBeDefined();
        expect(config.logger.level).toBeDefined();
        expect(config.platform).toBeDefined();
        expect(config.maxQueryResult).toBeDefined();
        expect(config.serverUrl).toBeDefined();
        expect(config.debugSugarApi).toBeDefined();
        expect(config.metadataTypes).toBeDefined();
        expect(config.unsecureRoutes).toBeDefined();
    });

});

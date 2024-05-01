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

require('../src/entry.js');

require('../lib/sugarlogic/expressions.js');
require('../lib/sugarlogic/sidecarExpressionContext.js');

let fixtures = {};
fixtures.api = require('./fixtures/api.js');

window.SUGAR = require('exports-loader?SUGAR!./fixtures/components.js');
let metadata = require('./fixtures/metadata.js');
fixtures.jssource = metadata.jssource;
fixtures.metadata = metadata.metadata;
fixtures.user = require('./fixtures/user.js');
window.fixtures = fixtures;

require('script-loader!./config');
require('./spec-helper');

var testsContext = require.context('./suites', true);
testsContext.keys().forEach(testsContext);

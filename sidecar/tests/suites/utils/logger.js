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
describe('Utils/Logger', function() {
    var sandbox;
    var config;
    var logger;
    var mockConsole;

    beforeEach(function() {
        sandbox = sinon.createSandbox();
        config = {
                level: 'FATAL',
                formatter: 'SimpleFormatter',
                consoleWriter: 'ConsoleWriter',
                serverWriter: 'ServerWriter',
        };

        logger = require('../../../src/utils/logger.js')(config);

        mockConsole = sandbox.mock(console);
    });

    afterEach(function() {
        sandbox.restore();
        config = null;
    });

    it('should be able to log a message', function() {
        var date = new Date(Date.UTC(2012, 2, 3, 6, 15, 32));
        sandbox.useFakeTimers(date.getTime());

        config.level = 'ERROR';

        mockConsole.expects('error').once().withArgs('ERROR[2012-3-3 6:15:32]: Test message');
        logger.error('Test message');
        mockConsole.verify();
    });

    it('should be able to log with a function', function() {
        var mockConsoleExpect = mockConsole.expects('log').once();
        var testMsg = 'foo';

        config.level = 'INFO';
        logger.info(function() {
            return 'Test message ' + testMsg;
        });

        expect(mockConsoleExpect.args[0]).toMatch(/INFO\[.{14,20}\]: Test message foo/);
        mockConsole.verify();
    });

    it('should be able to log an object', function() {
        var mockConsoleExpect = mockConsole.expects('log').once();
        var testMsg = { bar: 'some bar'};

        config.level = 'TRACE';
        logger.trace(testMsg);
        expect(mockConsoleExpect.args[0]).toMatch(/TRACE\[.{14,20}\]: \{"bar":"some bar"\}/);
        mockConsole.verify();
    });

    it('should not log a message if log level is below the configured one', function() {
        mockConsole.expects('log').never();
        config.level = 'INFO';
        logger.debug('');
        mockConsole.verify();
    });

    describe('getLevel', function() {
        var errorMessage = 'Your logger level is set to an invalid value. ' +
            'Please redefine it in Administration > System Settings. ' +
            'If you continue to see this warning, please ' +
            'contact your Admin.';

        it('should fallthrough to ERROR and not throw any warning', function() {
            logger = require('../../../src/utils/logger.js')();

            expect(logger.getLevel()).toEqual(logger.levels.ERROR);
            mockConsole.verify();
        });

        it('should fallthrough to ERROR and throw an error warning', function() {
            config.level = 'NOTSURE';
            // FIXME this needs to be done after SC-5483 is implemented
            // mockConsole.expects('error').once().withArgs(errorMessage);
            expect(logger.getLevel()).toEqual(logger.levels.ERROR);
            mockConsole.verify();
        });
    });
});

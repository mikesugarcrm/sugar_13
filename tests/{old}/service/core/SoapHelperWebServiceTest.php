<?php
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

use PHPUnit\Framework\TestCase;

/**
 * SoapHelperWebServiceTest.php
 *
 * This test may be used to write tests against the SoapHelperWebService.php file and the utility functions found there.
 *
 * @author Collin Lee
 */

require_once 'service/core/SoapHelperWebService.php';

class SoapHelperWebServiceTest extends TestCase
{
    private static $original_service_object;

    public static function setUpBeforeClass(): void
    {
        global $service_object;
        if (!empty($service_object)) {
            self::$original_service_object = $service_object;
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$original_service_object)) {
            global $service_object;
            $service_object = self::$original_service_object;
        }
    }

    /**
     * retrieveCheckQueryProvider
     */
    public function retrieveCheckQueryProvider()
    {
        global $service_object;
        $service_object = new ServiceMockObject();
        $error = new SoapError();
        return [
            [$error, "id = 'abc'", true],
            [$error, 'user.id = prospects.id', true],
            [$error, "id $% 'abc'", false],
        ];
    }

    /**
     * testCheckQuery
     * This function tests the checkQuery function in the SoapHelperWebService class
     *
     * @dataProvider retrieveCheckQueryProvider();
     */
    public function testCheckQuery($errorObject, $query, $expected)
    {
        $helper = null;
        $result = $helper->checkQuery($errorObject, $query);
        $this->assertEquals($expected, $result, 'SoapHelperWebService->checkQuery functions as expected');
    }

    public function openSslDecryptTripledesMethodProvider()
    {
        return [
            ['232c4daa440989ec433ec701d06bcd92', '123456789012345678901234'],
            ['baa6d122523d31b0bf5be57999525e0b', '123456789012345678901234'],
        ];
    }
}

/**
 * ServiceMockObject
 *
 * Used to override global service_object
 */
class ServiceMockObject
{
    public function error()
    {
    }
}

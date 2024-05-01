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

namespace Sugarcrm\SugarcrmTestsUnit;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class TestMockHelper
 *
 * Helper class to create Mock Object, wrapper of original TestCase methods
 */
class TestMockHelper
{
    /**
     * Helper method, creates a mock object using a fluent interface.
     *
     * @param TestCase $testCase
     * @param string $className
     * @param array|null $methods
     * @param bool $disableOriginalConstructor
     *
     * @return MockObject
     */
    public static function getObjectMock(
        TestCase $testCase,
        $className,
        array    $methods = null,
        $disableOriginalConstructor = true
    ) {

        $mockObject = $testCase->getMockBuilder($className);

        if ($disableOriginalConstructor) {
            $mockObject->disableOriginalConstructor();
        }

        $mockObject->setMethods($methods);
        return $mockObject->getMock();
    }

    /**
     * helper method, create mock for abstract class
     *
     * @param TestCase $testCase
     * @param $className
     * @param array|null $methods
     * @param bool $disableOriginalConstructor
     * @return MockObject
     */
    public static function getMockForAbstractClass(
        TestCase $testCase,
        $className,
        array    $methods = null,
        $disableOriginalConstructor = true
    ) {

        $mockObject = $testCase->getMockBuilder($className);

        if ($disableOriginalConstructor) {
            $mockObject->disableOriginalConstructor();
        }

        $mockObject->setMethods($methods);
        return $mockObject->getMockForAbstractClass();
    }

    /**
     * Returns a test double for the specified class.
     *
     * @param TestCase $testCase
     * @param string $originalClassName
     *
     * @return MockObject
     *
     * @throws Exception
     */
    public static function createMock(TestCase $testCase, $originalClassName)
    {
        return TestReflection::callProtectedMethod($testCase, 'createMock', [$originalClassName]);
    }

    /**
     * Returns a partial test double for the specified class.
     *
     * @param TestCase $testCase
     * @param string $originalClassName
     * @param array $methods
     *
     * @return MockObject
     *
     * @throws Exception
     */
    public static function createPartialMock(TestCase $testCase, $originalClassName, array $methods)
    {
        return TestReflection::callProtectedMethod($testCase, 'createPartialMock', [$originalClassName, $methods]);
    }
}

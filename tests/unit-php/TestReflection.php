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

/**
 * Class TestReflection
 *
 * Helper class to work with Classes that have Protected methods and variables
 *
 * @package Sugarcrm\SugarcrmTestsUnit
 */
class TestReflection
{
    /**
     * Call a protected method on a class
     *
     * @param string|object $classOrObject The Class we are working on
     * @param string $method The method name to call
     * @param array $args Arguments to pass to the method
     * @return mixed What ever is returned from the called method
     */
    public static function callProtectedMethod($classOrObject, $method, $args = [])
    {
        $rm = new \ReflectionMethod($classOrObject, $method);
        $rm->setAccessible(true);
        $object = is_object($classOrObject) ? $classOrObject : null;
        return $rm->invokeArgs($object, $args);
    }

    /**
     * Used to set the value of a protected or private variable
     *
     * @param Object $object THe Class we are trying to set a property on
     * @param string $property The name of the property
     * @param mixed $value The value for the property
     */
    public static function setProtectedValue($object, $property, $value)
    {
        $ro = new \ReflectionObject($object);
        $rp = $ro->getProperty($property);
        $rp->setAccessible(true);
        $rp->setValue($object, $value);
    }

    /**
     * Used to get the value of a protected or private variable
     *
     * @param Object $object THe Class we are trying to set a property on
     * @param string $property The name of the property
     * @return mixed What ever is stored in the property
     */
    public static function getProtectedValue($object, $property)
    {
        $ro = new \ReflectionObject($object);
        $rp = $ro->getProperty($property);
        $rp->setAccessible(true);
        return $rp->getValue($object);
    }
}

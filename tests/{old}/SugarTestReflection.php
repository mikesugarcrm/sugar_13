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

/*
 * This class helps testing by exposing protected and private elements to the tests
 */

class SugarTestReflection
{
    protected static $supported;

    public static function callProtectedMethod($classOrObject, $method, $args = [])
    {
        $rm = new ReflectionMethod($classOrObject, $method);
        $rm->setAccessible(true);
        $object = is_object($classOrObject) ? $classOrObject : null;
        return $rm->invokeArgs($object, $args);
    }

    public static function setProtectedValue($classOrObject, $property, $value)
    {
        $rp = new ReflectionProperty($classOrObject, $property);
        $rp->setAccessible(true);
        $object = is_object($classOrObject) ? $classOrObject : null;
        $rp->setValue($object, $value);
    }

    public static function getProtectedValue($classOrObject, $property)
    {
        $rp = new ReflectionProperty($classOrObject, $property);
        $rp->setAccessible(true);
        $object = is_object($classOrObject) ? $classOrObject : null;
        return $rp->getValue($object);
    }
}

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

require_once 'include/Sugarpdf/sugarpdf_config.php';
require_once 'vendor/tcpdf/tcpdf.php';

class SerializeEvilTest extends TestCase
{
    public static function getDestructors(): iterable
    {
        return [
            [SugarTheme::class],
            [tcpdf::class],
            [ImportFile::class],
            [Zend_Http_Response_Stream::class],
        ];
    }

    /**
     * @dataProvider getDestructors
     */
    public function testUnserializeExcept(string $class): void
    {
        $this->assertTrue(class_exists($class));
        $len = strlen($class);

        $this->expectException(Exception::class);
        unserialize(sprintf('O:%d:"%s":0:{}', $len, $class));
    }
}

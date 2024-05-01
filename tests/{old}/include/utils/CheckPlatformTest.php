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

require_once 'include/utils.php';

class CheckPlatformTest extends TestCase
{
    /**
     * @var bool|mixed
     */
    //@codingStandardsIgnoreStart
    public $_isOnWindows;

    //@codingStandardsIgnoreEnd

    protected function setUp(): void
    {
        $this->_isOnWindows = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
    }

    public function testVerifyIfWeAreOnWindows()
    {
        $this->assertEquals(is_windows(), $this->_isOnWindows);
    }
}

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

class SugarCacheApcuTest extends SugarCacheAbstractTest
{
    protected function newInstance()
    {
        return new SugarCacheApcu();
    }

    /**
     * @see http://php.net/manual/en/function.apcu-store.php#refsect1-function.apcu-store-parameters
     */
    public function testExpiration()
    {
        $this->markTestSkipped('Cannot test APCu expiration since the value is cleaned on the next request.');
    }
}

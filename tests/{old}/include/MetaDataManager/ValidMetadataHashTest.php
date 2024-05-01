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
 * Testing valid caches to prevent error 412 loops.
 */
class ValidMetadataHashTest extends TestCase
{
    protected $baseHash = '1234asdf';
    protected $portalHash = 'zzz123';

    public function testHashValid()
    {
        $hashes = [
            'meta:hash:base' => $this->baseHash,
            'meta:hash:portal,base' => $this->portalHash,
        ];

        // Get the base metadata manager
        $mm = new ValidMetadataHashMetadataManager();
        $mm->setHashCacheForTest($hashes);

        $this->assertTrue(
            $mm->isMetadataHashValid($this->baseHash),
            'Base metadata hash should have been valid but was not'
        );
        $this->assertFalse(
            $mm->isMetadataHashValid('invalid Hash'),
            'Base metadata hash should have been invalid, but was valid'
        );

        // Get the portal metadata manager
        $mm = new ValidMetadataHashMetadataManager(['portal']);
        $this->assertTrue(
            $mm->isMetadataHashValid($this->portalHash),
            'Portal metadata hash should have been valid but was not'
        );
        $this->assertFalse(
            $mm->isMetadataHashValid($this->baseHash),
            'Portal metadata hash should have been invalid, but was valid'
        );
    }
}

class ValidMetadataHashMetadataManager extends MetaDataManager
{
    public function setHashCacheForTest($hashes)
    {
        foreach ($hashes as $key => $hash) {
            $this->addToHashCache($key, $hash);
        }
    }
}

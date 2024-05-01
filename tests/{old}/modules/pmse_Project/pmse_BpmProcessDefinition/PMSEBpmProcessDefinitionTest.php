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
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class PMSEBpmProcessDefinitionTest extends TestCase
{
    public function testBugBr8762()
    {
        $utilsMock = new class () extends PMSEEngineUtils {
            public function getKey()
            {
                return self::getModuleLockedFieldsCacheKey('Contacts');
            }
        };
        $cacheKey = $utilsMock->getKey();
        SugarCache::instance()->{$cacheKey} = true;

        /** @var pmse_BpmProcessDefinition $bean */
        $processDefinition = BeanFactory::newBean('pmse_BpmProcessDefinition');
        $contact = BeanFactory::newBean('Contacts');

        $ids = range(1, 1001);
        $result = $processDefinition->getRelatedModuleRecords($contact, $ids);
        $this->assertEmpty($result);

        unset(SugarCache::instance()->{$cacheKey});
    }
}

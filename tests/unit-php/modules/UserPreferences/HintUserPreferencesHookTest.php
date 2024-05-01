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

namespace Sugarcrm\SugarcrmTestsUnit\modules\UserPreferences;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\modules\UserPreferences\HintUserPreferencesHook;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\modules\UserPreferences\HintUserPreferencesHook
 */
class HintUserPreferencesHookTest extends TestCase
{
    /**
     * @covers ::afterSave
     */
    public function testAfterSave()
    {
        //serialized base64-encoded representation of Injection class
        $payload = 'Tzo2MDoiU3VnYXJjcm1cU3VnYXJjcm1UZXN0c1VuaXRcbW9kdWxlc1xVc2VyUHJlZmVyZW5jZXNcSW5qZWN0aW9uIjowOnt9';
        $hook = $this->getMockBuilder(HintUserPreferencesHook::class)
            ->disableOriginalConstructor()
            ->getMock();
        $arguments = [
            'isUpdate' => true,
            'dataChanges' => [
                'contents' => [
                    'before' => $payload,
                    'after' => $payload,
                ],
            ],
        ];
        $userPreferences = $this->getMockBuilder(\UserPreference::class)
            ->disableOriginalConstructor()
            ->getMock();
        ob_start();
        /**
         * Injection::__desctruct() will be called on unserialize() if the second param ['allowed_classes' => false]
         * is not provided
         */
        $hook->afterSave($userPreferences, 'event', $arguments);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertStringNotContainsString('INJECTION', $output);
    }
}

class Injection
{
    public function __destruct()
    {
        $this->test();
    }

    protected function test()
    {
        echo 'INJECTION';
    }
}

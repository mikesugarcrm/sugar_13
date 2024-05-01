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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModuleNamesAndIconsApiTest extends TestCase
{
    private $language = 'en_us';
    private $language_contents;
    private $global_language_contents;

    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @var \AdministrationApi|MockObject
     */
    private $api;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->api = $this->createPartialMock(
            \ModuleNamesAndIconsApi::class,
            ['ensureAdminUser',]
        );
        $this->api->method('ensureAdminUser')->willReturn(true);

        $mods = ['Accounts', 'Contacts', 'Campaigns'];
        foreach ($mods as $mod) {
            if (file_exists("custom/modules/{$mod}/language/en_us.lang.php")) {
                $this->language_contents[$mod] = file_get_contents("custom/modules/{$mod}/language/en_us.lang.php");
                unlink("custom/modules/{$mod}/language/en_us.lang.php");
            }
        }

        // check the global lang file
        if (file_exists('custom/include/language/' . $this->language . '.lang.php')) {
            $this->global_language_contents = file_get_contents('custom/include/language/' . $this->language . '.lang.php');
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->removeCustomAppStrings();
        $this->removeModuleStrings(['Accounts']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarCache::$isCacheReset = false;

        if (!empty($this->language_contents)) {
            foreach ($this->language_contents as $key => $contents) {
                file_put_contents("custom/modules/{$key}/language/en_us.lang.php", $contents);
            }
        }

        if (!empty($this->global_language_contents)) {
            file_put_contents(
                'custom/include/language/' . $this->language . '.lang.php',
                $this->global_language_contents
            );
        }
        parent::tearDown();
    }

    /**
     * Remove any custon language file
     */
    private function removeCustomAppStrings()
    {
        $fileName = 'custom/include/language/' . $this->language . '.lang.php';
        if (file_exists($fileName)) {
            @unlink($fileName);
        }
    }

    /**
     * Unlink custom module language files
     *
     * @param $modules
     */
    private function removeModuleStrings($modules)
    {
        foreach ($modules as $module => $v) {
            $fileName = 'custom/modules/' . $module . '/language/' . $this->language . '.lang.php';
            if (file_exists($fileName)) {
                @unlink($fileName);
            }
        }
    }

    /**
     * Provide test data for renaming module-related strings.
     *
     * @return array
     */
    public function fieldNameProvider()
    {
        return [
            // Test empty label.
            ['', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], '', ''],
            // Test whole words.
            ['Account', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'Client', 'Client'],
            ['Accounts', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'Clients', 'Clients'],
            ['Purchase', ['prev_singular' => 'Purchase', 'prev_plural' => 'Purchases', 'singular' => 'Subscription', 'plural' => 'Subscriptions'], 'Subscription', 'Subscription'],
            ['Purchases', ['prev_singular' => 'Purchase', 'prev_plural' => 'Purchases', 'singular' => 'Subscription', 'plural' => 'Subscriptions'], 'Subscriptions', 'Subscriptions'],
            ['PurchaseLineItem', ['prev_singular' => 'PurchaseLineItem', 'prev_plural' => 'PurchaseLineItems', 'singular' => 'SubscriptionLineItem', 'plural' => 'SubscriptionLineItems'], 'SubscriptionLineItem', 'SubscriptionLineItem'],
            ['PurchaseLineItems', ['prev_singular' => 'PurchaseLineItem', 'prev_plural' => 'PurchaseLineItems', 'singular' => 'SubscriptionLineItem', 'plural' => 'SubscriptionLineItems'], 'SubscriptionLineItems', 'SubscriptionLineItems'],
            // Test empty field values.
            ['Contacts', ['prev_singular' => '', 'prev_plural' => '', 'singular' => '', 'plural' => ''], 'Contacts', 'Contacts'],
            ['Contact', ['prev_singular' => 'Contact', 'prev_plural' => '', 'singular' => 'Client', 'plural' => 'Clients'], 'Contact', 'Contact'],
            ['Contacts', ['prev_singular' => '', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => 'Clients'], 'Contacts', 'Contacts'],
            ['Contact', ['prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => '', 'plural' => 'Clients'], 'Contact', 'Contact'],
            ['Contacts', ['prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => ''], 'Contacts', 'Contacts'],
            ['Contacts', ['prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => '', 'plural' => 'Clients'], 'Contacts', 'Clients'],
            ['Contact', ['prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => ''], 'Client', 'Contact'],
            ['Purchase', ['prev_singular' => 'Purchase', 'prev_plural' => 'Purchases', 'singular' => 'Subscription', 'plural' => ''], 'Subscription', 'Purchase'],
            ['Purchases', ['prev_singular' => 'Purchase', 'prev_plural' => 'Purchases', 'singular' => '', 'plural' => 'Subscriptions'], 'Purchases', 'Subscriptions'],
            ['PurchaseLineItem', ['prev_singular' => 'PurchaseLineItem', 'prev_plural' => 'PurchaseLineItems', 'singular' => 'SubscriptionLineItem', 'plural' => ''], 'SubscriptionLineItem', 'PurchaseLineItem'],
            ['PurchaseLineItems', ['prev_singular' => 'PurchaseLineItem', 'prev_plural' => 'PurchaseLineItems', 'singular' => '', 'plural' => 'SubscriptionLineItems'], 'PurchaseLineItems', 'SubscriptionLineItems'],
            // Test multiple words in labels.
            ['My Account:', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'My Client:', 'My Client:'],
            ['View Accounts Module', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'View Clients Module', 'View Clients Module'],
            ['Generated Purchases', ['prev_singular' => 'Purchase', 'prev_plural' => 'Purchases', 'singular' => 'Subscription', 'plural' => 'Subscriptions'], 'Generated Subscriptions', 'Generated Subscriptions'],
            // Test labels without previous values.
            ['View Module', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'View Module', 'View Module'],
            ['Settings', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'Settings', 'Settings'],
            // Test multiple replacements.
            ['Account Accounts', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'Client Clients', 'Client Clients'],
            ['Account Accounts Account', ['prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'], 'Client Clients Client', 'Client Clients Client'],
            // Test labels with same previous values.
            ['Account', ['prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => 'Client', 'plural' => 'Clients'], 'Client', 'Clients'],
            ['Account Accounts', ['prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => 'Client', 'plural' => 'Clients'], 'Client Accounts', 'Clients Accounts'],
            // Test fields with only spaces.
            ['Account', ['prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => ' ', 'plural' => ' '], 'Account', 'Account'],
            // Test fields with special characters.
            ['Account', ['prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => '<script>alert("hello");</script>', 'plural' => ''], 'alert(&quot;hello&quot;);', 'Account'],
            ['Account', ['prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => '', 'plural' => '<script>alert("hello");</script>'], 'Account', 'alert(&quot;hello&quot;);'],
        ];
    }

    /**
     * Test renaming module-related string functionality.
     *
     * @dataProvider fieldNameProvider
     */
    public function testModuleRelatedStringRenaming($label, $renameFields, $newLabel, $newLabelPluralFirst)
    {

        include_once 'include/utils/db_utils.php';
        // Perform the same sanitization checks done during the actual request.
        $renameFields['singular'] = SugarCleaner::stripTags($renameFields['singular']);
        $renameFields['plural'] = SugarCleaner::stripTags($renameFields['plural']);
        $renameFields['singular'] = trim($renameFields['singular']);
        $renameFields['plural'] = trim($renameFields['plural']);

        $renamedLabelSingularFirst = $this->api->renameModuleRelatedStrings($label, $renameFields, false);
        $renamedLabelDefault = $this->api->renameModuleRelatedStrings($label, $renameFields);

        $this->assertEquals($newLabel, $renamedLabelSingularFirst);
        $this->assertEquals($newLabelPluralFirst, $renamedLabelDefault);
    }
}

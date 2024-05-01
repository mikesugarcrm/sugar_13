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

class Bug48800Test extends TestCase
{
    public $emailTemplate;
    public $user;

    protected function setUp(): void
    {
        global $current_user, $app_list_strings;
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $current_user = $this->user;
        $app_list_strings = return_app_list_strings_language('en_us');
        $this->user->setPreference('default_locale_name_format', 's f l');
        $this->user->savePreferencesToDB();
        $this->user->save();

        $this->emailTemplate = new EmailTemplate();
        $this->emailTemplate->name = 'Bug48800Test';
        $this->emailTemplate->assigned_user_id = $this->user->id;
        $this->emailTemplate->team_id = $this->user->team_id;
        $this->emailTemplate->team_set_id = $this->user->team_id;
        $this->emailTemplate->save();
        //$this->useOutputBuffering = false;
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query("DELETE FROM email_templates WHERE id = '{$this->emailTemplate->id}'");
    }

    public function testAssignedUserName()
    {
        global $locale;
        $locale = Localization::getObject();
        $testName = $locale->formatName($this->user);
        $testTemplate = new EmailTemplate();
        $testTemplate->retrieve($this->emailTemplate->id);
        $this->assertEquals(
            $testName,
            $testTemplate->assigned_user_name,
            'Assert that the assigned_user_name is the locale formatted name value'
        );
    }
}

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
 * @coversDefaultClass EmailTemplate
 */
class EmailTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    /**
     * @coversNothing
     */
    public function testSystemTemplates_ProperConfiguration_ExistWithProperTypes()
    {
        $this->assertNotEmpty(
            $GLOBALS['sugar_config']['passwordsetting']['lostpasswordtmpl'],
            'lostpasswordtmpl id not set in config'
        );

        $lostPasswordTemplateId = $GLOBALS['sugar_config']['passwordsetting']['lostpasswordtmpl'];

        $lostPasswordTemplateType =
            $GLOBALS['db']->getOne("SELECT type FROM email_templates WHERE id='$lostPasswordTemplateId'");
        $this->assertEquals('system', $lostPasswordTemplateType, "Lost Password Template Type not 'system'");
    }

    /**
     * @covers ::parse_template_bean
     * @covers ::add_replacement
     * @covers ::convertToType
     * @covers ::parseUserValues
     */
    public function testParseTemplateBean_BeanIsContact()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact('', [
            'account_id' => $account->id,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            // Need to set the name because we're pretending that all data has been read from the database.
            'account_name' => $account->name,
        ]);

        $template = 'Welcome $contact_first_name from $contact_account_name, ' .
            'I am your account manager $contact_user_name.';
        $expected = "Welcome {$contact->first_name} from {$account->name}, " .
            "I am your account manager {$GLOBALS['current_user']->name}.";
        $actual = EmailTemplate::parse_template_bean($template, 'Contacts', $contact);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::parse_template_bean
     */
    public function testParseTemplateBean_BeanIsCase()
    {
        $case = SugarTestCaseUtilities::createCase('', [
            'name' => 'test name',
            'source' => 'Internal',
            'case_number' => 1,
        ]);
        $case->case_number = 1;
        $template = 'This is about $case_name. Case number $case_case_number. ' .
            'This case source is $case_source.';
        $expected = 'This is about test name. Case number 1. ' .
            'This case source is Internal.';
        $actual = EmailTemplate::parse_template_bean($template, 'Cases', $case);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::parse_template_bean
     * @covers ::add_replacement
     * @covers ::convertToType
     * @covers ::parseUserValues
     */
    public function testParseTemplateBean_BeanIsUser()
    {
        $template = 'Welcome $contact_user_first_name $contact_user_last_name, ' .
            'Your username is $contact_user_user_name.';
        $expected = "Welcome {$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}, " .
            "Your username is {$GLOBALS['current_user']->user_name}.";
        $actual = EmailTemplate::parse_template_bean($template, 'Users', $GLOBALS['current_user']);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::parse_template_bean
     * @covers ::add_replacement
     * @covers ::convertToType
     */
    public function testParseTemplateBean_BeanIsAccount_Description_Has_Multiple_Lines_TargetIsHtml()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->description = <<<EOD1
Description Line One
Description Line Two
Description Line Three
EOD1;

        $template = <<<EOT1
        <html>
        <body>
           <div> This is some HTML followed by a template variable that refers to the account description field </div>
\$account_description
           <div> And this is more HTML </div>
        </body>
        </html>
EOT1;

        $expected = <<<EOR1
        <html>
        <body>
           <div> This is some HTML followed by a template variable that refers to the account description field </div>
Description Line One<br />Description Line Two<br />Description Line Three
           <div> And this is more HTML </div>
        </body>
        </html>
EOR1;

        $actual = EmailTemplate::parse_template_bean($template, 'Accounts', $account, true);
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::parse_template_bean
     * @covers ::add_replacement
     * @covers ::convertToType
     */
    public function testParseTemplateBean_BeanIsAccount_Description_Has_Multiple_Lines_TargetIsNotHtml()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->description = <<<EOD2
Description Line One
Description Line Two
Description Line Three
EOD2;

        $template = <<<EOT2
        <html>
        <body>
           <div> This is some HTML followed by a template variable that refers to the account description field </div>
\$account_description
           <div> And this is more HTML </div>
        </body>
        </html>
EOT2;

        $expected = <<<EOR2
        <html>
        <body>
           <div> This is some HTML followed by a template variable that refers to the account description field </div>
Description Line One
Description Line Two
Description Line Three
           <div> And this is more HTML </div>
        </body>
        </html>
EOR2;

        $actual = EmailTemplate::parse_template_bean($template, 'Accounts', $account, false);
        $this->assertSame($expected, $actual);
    }
}

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

class ActionButtonApiTest extends TestCase
{
    /**
     * @var ActionButtonApi
     */
    private $api;

    /**
     * List of items to delete at the end of the test
     * @var array
     */
    protected $deleteAssets = [];

    /**
     * Module used for testing
     * @var testModule
     */
    private $testModule = 'Accounts';

    /**
     * @var RestService
     */
    protected $serviceMock;

    /**
     * setUpBeforeClass function
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        global $current_user;

        /**
         * @var User
         */
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();
    }

    /**
     * tearDownAfterClass function
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        global $current_user;
        $current_user = null;
    }

    /**
     * setUp function
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->api = $this->getMockBuilder('ActionButtonApi')
            ->onlyMethods([])
            ->getMock();

        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    /**
     * tearDown function
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    /**
     * providerEvaluateEmailTemplate function
     *
     * demo data for evaluateEmailTemplate API
     *
     * @return array
     */
    public function providerEvaluateEmailTemplate()
    {
        return [
            [
                'accountMeta' => [
                    'name' => 'AccountName',
                    'website' => 'https://www.sugarcrm.com/',
                ],
                'testModule' => $this->testModule,
                'template' => [
                    'subject' => 'Test Subject',
                    'base_module' => $this->testModule,
                    'body_html' => 'Hi $account_name, Please check my website: $account_website',
                ],
                'expected' => [
                    'body' => 'Hi AccountName, Please check my website: https://www.sugarcrm.com/',
                    'subject' => 'Test Subject',
                    'description' => null,
                    'emailTo' => false,
                ],
                'expectedWithSender' => [
                    'body' => 'Hi AccountName, Please check my website: https://www.sugarcrm.com/',
                    'subject' => 'Test Subject',
                    'description' => null,
                    'emailTo' => [
                        [
                            'email_address' => 'test@sugarcrm.com',
                            'email_address_id' => '<new_id>',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get Email Template with values
     *
     * testEvaluateEmailTemplate function
     *
     * @param array $accountMeta
     * @param string $testModule
     * @param array $template
     * @param array $expected
     * @param array $expectedWithSender
     * @return void
     *
     * @dataProvider providerEvaluateEmailTemplate
     */
    public function testEvaluateEmailTemplate(
        array  $accountMeta,
        string $testModule,
        array  $template,
        array  $expected,
        array  $expectedWithSender
    ): void {

        $demoData = [
            'targetRecordModule' => $testModule,
            'targetRecordId' => $this->getNewAccountRecord($accountMeta),
            'targetTemplateId' => $this->getNewEmailTemplateRecord($template),
            'emailToField' => false,
        ];

        $result = $this->api->evaluateEmailTemplate(
            $this->serviceMock,
            $demoData
        );

        $this->assertEquals($result, $expected);

        $demoData['emailToField'] = true;
        $demoData['fieldName'] = 'email_template_c';
        $demoData['emailToData'] = [
            'formulaElement' => 'toString("test@sugarcrm.com")',
            'validFormula' => true,
            'validationMessage' => '',
        ];

        $result = $this->api->evaluateEmailTemplate(
            $this->serviceMock,
            $demoData
        );

        $emailAddressId = $result['emailTo'][0]['email_address_id'];

        SugarTestEmailAddressUtilities::setCreatedEmailAddress($emailAddressId);

        foreach ($expectedWithSender as $key => $value) {
            $this->assertArrayHasKey($key, $result);
        }

        foreach ($expectedWithSender['emailTo'] as $key => $value) {
            $this->assertArrayHasKey($key, $result['emailTo'], "Invalid key {$key}");
        }

        $this->assertEquals($result['emailTo'][0]['email_address'], $expectedWithSender['emailTo'][0]['email_address']);
        $this->assertIsString($result['emailTo'][0]['email_address_id']);
    }

    /**
     * providerTestEvaluateBPMEmailTemplate function
     *
     * demo data for EvaluateBPMEmailTemplate API
     *
     * @return array
     */
    public function providerTestEvaluateBPMEmailTemplate()
    {
        return [
            [
                'accountMeta' => [
                    'name' => 'AccountName',
                    'website' => 'https://www.sugarcrm.com/',
                ],
                'testModule' => $this->testModule,
                'template' => [
                    'name' => 'Test BPM Template',
                    'subject' => 'Subject Test',
                    'base_module' => $this->testModule,
                    'body_html' => 'Hi {::Accounts::name::}, Please check my website: {::Accounts::website::}',
                ],
                'expected' => [
                    'body' => 'Hi AccountName, Please check my website: https://www.sugarcrm.com/',
                    'subject' => 'Subject Test',
                    'description' => null,
                    'emailTo' => false,
                ],
                'expectedWithSender' => [
                    'body' => 'Hi AccountName, Please check my website: https://www.sugarcrm.com/',
                    'subject' => 'Test Subject',
                    'description' => null,
                    'emailTo' => [
                        [
                            'email_address' => 'test@sugarcrm.com',
                            'email_address_id' => '<new_id>',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get PMSE Template with values
     *
     * testEvaluateBPMEmailTemplate function
     *
     * @param array $accountMeta
     * @param string $testModule
     * @param array $template
     * @param array $expected
     * @param array $expectedWithSender
     * @return void
     *
     * @dataProvider providerTestEvaluateBPMEmailTemplate
     */
    public function testEvaluateBPMEmailTemplate(
        array  $accountMeta,
        string $testModule,
        array  $template,
        array  $expected,
        array  $expectedWithSender
    ): void {

        $demoData = [
            'targetRecordModule' => $testModule,
            'targetRecordId' => $this->getNewAccountRecord($accountMeta),
            'targetTemplateId' => $this->getNewPmseEmailTemplateRecord($template),
            'emailToField' => false,
        ];

        $result = $this->api->evaluateBPMEmailTemplate(
            $this->serviceMock,
            $demoData
        );

        $this->assertEquals($result, $expected);

        $demoData['emailToField'] = true;
        $demoData['fieldName'] = 'email_template_c';
        $demoData['emailToData'] = [
            'formulaElement' => 'toString("test@sugarcrm.com")',
            'validFormula' => true,
            'validationMessage' => '',
        ];

        $result = $this->api->evaluateBPMEmailTemplate(
            $this->serviceMock,
            $demoData
        );

        $emailAddressId = $result['emailTo'][0]['email_address_id'];

        SugarTestEmailAddressUtilities::setCreatedEmailAddress($emailAddressId);

        foreach ($expectedWithSender as $key => $value) {
            $this->assertArrayHasKey($key, $result);
        }

        foreach ($expectedWithSender['emailTo'] as $key => $value) {
            $this->assertArrayHasKey($key, $result['emailTo'], "Invalid key {$key}");
        }

        $this->assertEquals($result['emailTo'][0]['email_address'], $expectedWithSender['emailTo'][0]['email_address']);
        $this->assertIsString($result['emailTo'][0]['email_address_id']);
    }

    /**
     * providerEvaluateExpression function
     *
     * demo data for EvaluateExpression API
     *
     * @return array
     */
    public function providerEvaluateExpression()
    {
        return [
            [
                'accountMeta' => [
                    'name' => 'AccountName',
                    'description' => 'www.sugarcrm.com',
                ],
                'testModule' => $this->testModule,
                'formula' => [
                    'website' => [
                        'formula' => 'concat("https://",toString($description),"/")',
                        'isCalculated' => true,
                        'fieldName' => 'website',
                    ],
                ],
                'expected' => [
                    'website' => 'https://www.sugarcrm.com/',
                ],
            ],
            [
                'accountMeta' => [
                    'name' => 'AccountName',
                    'description' => 'www.sugarcrm.com',
                ],
                'testModule' => $this->testModule,
                'formula' => [
                    'website' => [
                        'formula' => 'strToUpper(concat("https://",toString($description),"/"))',
                        'isCalculated' => true,
                        'fieldName' => 'website',
                    ],
                ],
                'expected' => [
                    'website' => 'HTTPS://WWW.SUGARCRM.COM/',
                ],
            ],
        ];
    }

    /**
     * testEvaluateExpression function
     *
     * @param array $accountMeta
     * @param string $testModule
     * @param array $formula
     * @param array $expected
     * @return void
     *
     * @dataProvider providerEvaluateExpression
     */
    public function testEvaluateExpression(
        array  $accountMeta,
        string $testModule,
        array  $formula,
        array  $expected
    ): void {

        $demoData = [
            'targetModule' => $testModule,
            'targetRecordId' => $this->getNewAccountRecord($accountMeta),
            'targetFields' => $formula,
        ];

        $result = $this->api->evaluateExpression(
            $this->serviceMock,
            $demoData
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * providerEvaluateCalculatedUrl function
     *
     * demo data for EvaluateCalculatedUrl API
     *
     * @return array
     */
    public function providerEvaluateCalculatedUrl()
    {
        return [
            [
                'accountMeta' => [
                    'name' => 'AccountName',
                    'description' => 'www.sugarcrm.com',
                ],
                'testModule' => $this->testModule,
                'meta' => [
                    'buildUrlTempField' => [
                        'formula' => 'strToUpper(concat("https://",toString($description),"/"))',
                        'targetField' => 'buildUrlTempField',
                    ],
                ],
                'expected' => [
                    'buildUrlTempField' => [
                        'value' => 'HTTPS://WWW.SUGARCRM.COM/',
                        'fieldName' => 'buildUrlTempField',
                    ],
                ],
            ],
        ];
    }

    /**
     * providerEvaluateCalculatedUrl function
     *
     * @param array $accountMeta
     * @param string $testModule
     * @param array $meta
     * @param array $expected
     * @return void
     *
     * @dataProvider providerEvaluateCalculatedUrl
     */
    public function testEvaluateCalculatedUrl(array $accountMeta, string $testModule, array $meta, array $expected): void
    {
        $demoData = [
            'recordType' => $testModule,
            'recordId' => $this->getNewAccountRecord($accountMeta),
            'keepTempFieldForCalculatedURL' => $meta,
        ];

        $result = $this->api->evaluateCalculatedUrl(
            $this->serviceMock,
            $demoData
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Gets a new pmse email template record
     *
     * @param array $meta
     *
     * @return string
     */
    protected function getNewPmseEmailTemplateRecord(array $meta): string
    {
        $bean = BeanFactory::newBean('pmse_Emails_Templates');
        $bean->name = $meta['name'];
        $bean->subject = $meta['subject'];
        $bean->base_module = $meta['base_module'];
        $bean->body_html = $meta['body_html'];
        $bean->save();

        $this->addBeanToDeleteAssets($bean);

        return $bean->id;
    }

    /**
     * Create a new EmailTemplate bean based on incoming meta
     *
     * @param array $emailTemplateMeta
     *
     * @return string
     */
    protected function getNewEmailTemplateRecord(array $emailTemplateMeta): string
    {
        $id = '';
        $bean = SugarTestEmailTemplateUtilities::createEmailTemplate($id, $emailTemplateMeta);

        return $bean->id;
    }

    /**
     * Create a new Account bean based on incoming meta
     *
     * @param array $meta
     *
     * @return string
     */
    protected function getNewAccountRecord(array $meta): string
    {
        $id = '';
        $bean = SugarTestAccountUtilities::createAccount($id, $meta);

        return $bean->id;
    }

    /**
     * Tracks which beans were added so that they can be deleted later
     * @param SugarBean $bean
     */
    protected function addBeanToDeleteAssets(SugarBean $bean): void
    {
        $this->deleteAssets[$bean->getTableName()][] = $bean->id;
    }

    /**
     * cleanUp function
     *
     * Remove demo data
     *
     * @return void
     */
    protected function cleanUp(): void
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestEmailTemplateUtilities::removeAllCreatedEmailTemplates();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        foreach ($this->deleteAssets as $table => $ids) {
            $qb = \DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
            $qb->delete($table)->where(
                $qb->expr()->in(
                    'id',
                    $qb->createPositionalParameter($ids, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                )
            )->execute();
        }
    }
}

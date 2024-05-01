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

class DocumentMergeApiTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\ServiceMock|mixed
     */
    public $service;
    /**
     * @var DocumentMergeApi
     */
    private $api;

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
        $this->api = $this->getMockBuilder('DocumentMergeApi')
            ->onlyMethods(['createMergeRequest', 'createSugarDocument', 'uploadFile',
                'updateMergeRequest', 'createDocumentRelationship', 'getService'])
            ->getMock();

        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        $this->service = $this->getMockBuilder('ServiceMock')->onlyMethods(['merge'])->getMock();

        $this->api->expects($this->any())
            ->method('createMergeRequest')
            ->will($this->returnValue('dmId'));

        $this->api->expects($this->any())
            ->method('createSugarDocument')
            ->will($this->returnValue('docId'));

        $this->api->expects($this->any())
            ->method('getService')
            ->will($this->returnValue($this->service));
    }

    /**
     * tearDown function
     *
     * @return void
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for the merge test
     *
     * @return array
     */
    public function providerMerge(): array
    {
        return [
            [
                'args' => [
                    'mergeType' => 'merge',
                    'useRevision' => true,
                    'templateName' => 'SomeTestTemplate.docx',
                    'templateId' => '123',
                    'recordId' => 'accountId',
                    'recordModule' => 'Accounts',
                    'parentId' => null,
                    'parentModule' => null,
                    'maxRelate' => -1,
                ],
            ],
        ];
    }

    /**
     * Perform merge api call
     *
     * @param array $args
     *
     * @dataProvider providerMerge
     */
    public function testMerge(array $args): void
    {
        $this->api->expects($this->once())->method('getService');
        $this->service->expects($this->once())
            ->method('merge')
            ->with($this->callback(function ($input) {
                $this->assertArrayHasKey('params', $input);
                $this->assertEquals('merge', $input['params']['type']);
                $this->assertEquals(true, $input['params']['use_revision']);
                $this->assertEquals('SomeTestTemplate.docx', $input['params']['file_name']);
                $this->assertEquals('123', $input['params']['document_id']);
                $this->assertEquals('accountId', $input['params']['record_id']);
                $this->assertEquals('Accounts', $input['params']['record_module']);
                return true;
            }));
        $result = $this->api->merge($this->serviceMock, $args);

        $this->assertEquals($result, 'dmId');
    }

    /**
     * Data provider for the createDocument test
     *
     * @return array
     */
    public function providerCreateDocument(): array
    {
        return [
            [
                'args' => [
                    'sugar_mr_id' => 'dmId',
                    'gen_doc_id' => 'docId',
                    'use_revision' => true,
                    'current_user_id' => 'jim',
                    'record_module' => 'Accounts',
                    'record_id' => 'accountId',
                    'extension' => 'docx',
                ],
            ],
        ];
    }

    /**
     * createDocument api test
     *
     * @param array $args
     *
     * @dataProvider providerCreateDocument
     */
    public function testCreateDocument(array $args): void
    {
        $mockedAccount = $this->getMockBuilder(\Account::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $mockedAccount->id = 'accountId';
        $mockedAccount->module_name = 'Accounts';
        $mockedAccount->currency_id = '-99';
        \BeanFactory::registerBean($mockedAccount);

        $mockedDM = $this->getMockBuilder(\DocumentMerge::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $mockedDM->id = 'dmId';
        $mockedDM->module_name = 'DocumentMerges';
        \BeanFactory::registerBean($mockedDM);

        $this->api->expects($this->once())->method('createSugarDocument')->with(
            $this->callback(function ($input) {
                $this->assertEquals('docId', $input['documentId']);
                $this->assertEquals('jim', $input['userId']);
                $this->assertEquals(true, $input['useRevision']);
                return true;
            })
        );

        $result = $this->api->createDocument($this->serviceMock, $args);

        $this->assertEquals($result, [
            'success' => true,
            'message' => 'No Errors',
        ]);

        BeanFactory::unregisterBean($mockedAccount, $mockedAccount->id);
        BeanFactory::unregisterBean($mockedDM, $mockedDM->id);
    }

    /**
     * Data provider for the getuserpreferences test
     *
     * @return array
     */
    public function providerGetUserPreferences(): array
    {
        global $current_user;

        return [
            [
                'args' => [
                    'user_id' => $current_user->id,
                ],
            ],
        ];
    }

    /**
     * Get file content test
     *
     * @param array $args
     * @dataProvider providerGetUserPreferences
     *
     * @return void
     */
    public function testGetUserPreferences(array $args): void
    {
        $result = $this->api->getUserPreferences($this->serviceMock, $args);
        $this->assertArrayHasKey('current_language', $result);
        $this->assertArrayHasKey('currency_symbol', $result);
        $this->assertArrayHasKey('default_currency_significant_digits', $result);
        $this->assertArrayHasKey('num_grp_sep', $result);
        $this->assertArrayHasKey('dec_sep', $result);

        $this->assertEquals('en_us', $result['current_language']);
        $this->assertEquals('$', $result['currency_symbol']);
        $this->assertEquals(2, $result['default_currency_significant_digits']);
        $this->assertEquals(',', $result['num_grp_sep']);
        $this->assertEquals('.', $result['dec_sep']);
    }

    /**
     * Data provider for the getCurrencySymbol test
     *
     * @return array
     */
    public function providerGetCurrencySymbol(): array
    {
        return [
            [
                'args' => [
                    'record_id' => 'accountId',
                    'module' => 'Accounts',
                ],
            ],
        ];
    }

    /**
     * Get currency symbol test
     *
     * @param array $args
     * @dataProvider providerGetCurrencySymbol
     *
     * @return void
     */
    public function testGetCurrencySymbol(array $args): void
    {
        $mockedAccount = $this->getMockBuilder(\Account::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $mockedAccount->id = 'testId';
        $mockedAccount->module_name = 'Account';
        $mockedAccount->currency_id = '-99';
        \BeanFactory::registerBean($mockedAccount);

        $mockedCurrency = $this->getMockBuilder(\Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $mockedCurrency->id = '-99';
        $mockedCurrency->module_name = 'Currencies';
        $mockedCurrency->symbol = '$';
        \BeanFactory::registerBean($mockedCurrency);

        $result = $this->api->getCurrencySymbol($this->serviceMock, $args);
        $this->assertEquals($result, '$');

        BeanFactory::unregisterBean($mockedAccount, $mockedAccount->id);
        BeanFactory::unregisterBean($mockedCurrency, $mockedCurrency->id);
    }
}

/**
 * @class
 * Service Mock
 */
class ServiceMock
{
    public function merge(array $options)
    {
        return true;
    }
}

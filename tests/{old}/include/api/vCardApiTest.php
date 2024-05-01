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

/*
 * Tests vCard Rest api.
 */

class vCardApiTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('ACLStatic');
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        unset($_FILES);
    }

    protected function getApi()
    {
        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $api->setResponse(new RestResponse([]));
        return $api;
    }

    public function testvCardSave()
    {
        $contact = SugarTestContactUtilities::createContact();

        $api = $this->getApi();
        $args = [
            'module' => 'Contacts',
            'id' => $contact->id,
        ];

        $apiClass = new vCardApi();
        $result = $apiClass->vCardSave($api, $args);

        $this->assertStringContainsString('BEGIN:VCARD', $result);
    }

    /**
     * @group vcardapi_vCardImportPost
     */
    public function testvCardImportPost_NoFilePosted_ReturnsError()
    {
        unset($_FILES);
        $api = $this->getApi();

        $args = [
            'module' => 'Contacts',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);

        $apiClassMock = $this->createPartialMock('vCardApi', ['isUploadedFile']);

        $apiClassMock->expects($this->never())
            ->method('isUploadedFile');

        $apiClassMock->vCardImport($api, $args);
    }

    /**
     * @group vcardapi_vCardImportPost
     */
    public function testvCardImportPost_FileExists_ImportsPersonRecord()
    {
        $_FILES = [
            'vcard_import' => [
                'name' => 'simplevcard.vcf',
                'tmp_name' => __DIR__ . '/SimpleVCard.vcf',
                'type' => 'text/directory',
                'size' => 42,
                'error' => 0,
            ],
        ];

        $api = $this->getApi();

        $args = [
            'module' => 'Contacts',
        ];

        $apiClassMock = $this->createPartialMock('vCardApi', ['isUploadedFile']);

        $apiClassMock->expects($this->once())
            ->method('isUploadedFile')
            ->will($this->returnValue(true));

        $results = $apiClassMock->vCardImport($api, $args);

        $this->assertEquals(true, is_array($results), 'Incorrect number of items returned');
        $this->assertEquals(true, array_key_exists('vcard_import', $results), 'Incorrect field name returned');

        //verifying that the contact and account was created from vcard.
        $contact = BeanFactory::getBean('Contacts', $results['vcard_import']);

        SugarTestContactUtilities::setCreatedContact([$results['vcard_import']]);
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (!empty($contact->account_id)) {
            SugarTestAccountUtilities::setCreatedAccount([$contact->account_id]);
            SugarTestAccountUtilities::removeAllCreatedAccounts();
        }
    }

    /**
     * @group vcardapi_vCardImportPost
     */
    public function testvCardImportPost_FailsACLCheck_ThrowsNotAuthorizedException()
    {
        $_FILES = [
            'vcard_import' => [
                'name' => 'simplevcard.vcf',
                'tmp_name' => __DIR__ . '/SimpleVCard.vcf',
                'type' => 'text/directory',
                'size' => 42,
                'error' => 0,
            ],
        ];
        //Setting access to be denied for import and read
        $acldata = [];
        $acldata['module']['access']['aclaccess'] = ACL_ALLOW_DISABLED;
        $acldata['module']['import']['aclaccess'] = ACL_ALLOW_DISABLED;
        ACLAction::setACLData($GLOBALS['current_user']->id, 'Contacts', $acldata);
        // reset cached ACLs
        SugarACL::$acls = [];

        $api = $this->getApi();

        $args = [
            'module' => 'Contacts',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);

        $apiClassMock = $this->createPartialMock('vCardApi', ['isUploadedFile']);
        $apiClassMock->vCardImport($api, $args);
    }

    /**
     * @group vcardapi_vCardImportPost2
     */
    public function testvCardImportPost_NoFileExists_ThrowsMissingParameterException()
    {
        $api = $this->getApi();

        $args = [
            'module' => 'Contacts',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);

        $apiClassMock = $this->createPartialMock('vCardApi', ['isUploadedFile']);
        $apiClassMock->vCardImport($api, $args);
    }
}

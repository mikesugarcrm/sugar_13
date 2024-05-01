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
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass DocuSignEnvelopesApi
 */
class DocuSignApiTest extends TestCase
{
    /**
     * @var DocuSignApi
     */
    protected $api;


    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->api = new DocuSignApi();
    }

    /**
     * @covers ::sendReturn
     */
    public function testSendReturn()
    {
        $serviceBase = SugarTestRestUtilities::getRestServiceMock();
        $res = $this->api->sendReturn($serviceBase, []);

        $hasHtmlTagExists = strpos($res, '<html>') !== false;

        $this->assertSame(true, $hasHtmlTagExists);
    }

    /**
     * @covers ::docusignSaveBean
     */
    public function testDocusignSaveBean()
    {
        $serviceBase = SugarTestRestUtilities::getRestServiceMock();

        $envelopeId = Uuid::uuid4();

        $envelopeBean = BeanFactory::newBean('DocuSignEnvelopes');
        $envelopeBean->id = Uuid::uuid4();
        $envelopeBean->new_with_id = true;
        $envelopeBean->envelope_id = $envelopeId;
        $envelopeBean->save();

        $res = $this->api->docusignSaveBean($serviceBase, [
            'envelopeId' => $envelopeId,
            'status' => 'sent',
        ]);

        $envelopeBean->retrieve();

        $this->assertEquals(true, $res);
        $this->assertEquals('sent', $envelopeBean->status);

        $envelopeBean->deleted = 1;
        $envelopeBean->save();
    }

    /**
     * @covers ::getSugarEnvelopeIdByDsEnvelopeId
     */
    public function testGetSugarEnvelopeIdByDsEnvelopeId()
    {
        $envelopeId = Uuid::uuid4();

        $envelopeBean = BeanFactory::newBean('DocuSignEnvelopes');
        $envelopeBean->id = Uuid::uuid4();
        $envelopeBean->new_with_id = true;
        $envelopeBean->envelope_id = $envelopeId;
        $envelopeBean->save();

        $sugarEnvelopeId = $this->api->getSugarEnvelopeIdByDsEnvelopeId($envelopeId);

        $this->assertEquals($envelopeBean->id, $sugarEnvelopeId);

        $envelopeBean->deleted = 1;
        $envelopeBean->save();
    }

    /**
     * @covers ::createDocumentInSugar
     */
    public function testCreateDocumentInSugar()
    {
        $docName = 'test';
        $docPdfBytes = '';
        $completedDateTime = '2020-01-01 01:00:00';
        $completedDate = '2020-01-01';
        $doc = $this->api->createDocumentInSugar($docName, $docPdfBytes, $completedDateTime);

        $this->assertEquals($doc->document_name, $docName);
        $this->assertEquals($doc->active_date, $completedDate);

        $doc->deleted = 1;
        $doc->save();
    }

    /**
     * @covers ::docusignLoadPage
     */
    public function testDocusignLoadPage()
    {
        $serviceBase = SugarTestRestUtilities::getRestServiceMock();
        $res = $this->api->docusignLoadPage($serviceBase, []);

        $hasHtmlTagExists = strpos($res, '<html>') !== false;

        $this->assertEquals(true, $hasHtmlTagExists);
    }

    /**
     * @covers ::resendEnvelope
     */
    public function testResendEnvelope()
    {
        $envelopeBean = BeanFactory::newBean('DocuSignEnvelopes');
        $envelopeBean->id = Uuid::uuid4();
        $envelopeBean->new_with_id = true;
        $envelopeBean->status = 'completed';
        $envelopeBean->save();

        $serviceBase = SugarTestRestUtilities::getRestServiceMock();
        $res = $this->api->resendEnvelope($serviceBase, [
            'id' => $envelopeBean->id,
        ]);


        $this->assertEquals('error', $res['status']);
        $this->assertEquals(
            "Resend could not be made on envelope id {$envelopeBean->id}. It's status must be sent",
            $res['message']
        );

        $envelopeBean->deleted = 1;
        $envelopeBean->save();
    }

    /**
     * @covers ::removeEnvelope
     */
    public function testRemoveEnvelope()
    {
        $envelopeId = Uuid::uuid4();
        $envelopeBean = BeanFactory::newBean('DocuSignEnvelopes');
        $envelopeBean->envelope_id = $envelopeId;
        $envelopeBean->save();

        $serviceBase = SugarTestRestUtilities::getRestServiceMock();
        $res = $this->api->removeEnvelope($serviceBase, [
            'envelopeId' => $envelopeId,
        ]);

        $this->assertEquals(true, $res);

        //ensure to remove garbage
        $envelopeBean->deleted = 1;
        $envelopeBean->save();
    }
}

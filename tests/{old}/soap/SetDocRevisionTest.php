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

class SetDocRevisionTest extends SOAPTestCase
{
    private $docId;
    private $documentRevisionId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->login();
    }

    protected function tearDown(): void
    {
        $db = $GLOBALS['db'];
        $conn = $db->getConnection();
        $conn->delete('documents', ['id' => $this->docId]);
        $conn->delete('document_revisions', ['document_id' => $this->docId]);
        UploadFile::unlink_file($this->documentRevisionId);
        parent::tearDown();
    }

    public function testSetDocRevision()
    {
        //create document
        $set_entry_result = get_object_vars($this->soapClient->set_entry(
            $this->sessionId,
            'Documents',
            [
                ['name' => 'document_name', 'value' => 'Example Document'],
                ['name' => 'revision', 'value' => '1'],
            ]
        ));
        $document_id = $set_entry_result['id'];
        $this->docId = $document_id;
        //create document revision

        $contents = base64_encode(file_get_contents(__FILE__));

        $set_document_revision_resultObj = $this->soapClient->set_document_revision(
            $this->sessionId,
            [
                //The ID of the parent document.
                'id' => $document_id,
                'document_name' => uniqid('example document'),
                //The binary contents of the file.
                'file' => $contents,
                //The name of the file
                'filename' => 'example_document.txt',
                //The revision number
                'revision' => '1',
            ]
        );
        $set_document_revision_result = get_object_vars($set_document_revision_resultObj);
        $this->documentRevisionId = $set_document_revision_result['id'];

        $document = new Document();
        $document->retrieve($document_id);

        $this->assertEquals($set_document_revision_result['id'], $document->document_revision_id);
        $this->assertEquals('example_document.txt', $document->filename);
        $this->assertEquals('Example Document', $document->document_name);
    }
}

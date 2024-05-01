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


class RestFileTest extends RestFileTestBase
{
    /**
     * @group rest
     */
    public function testGetList()
    {
        $restReply = $this->restCall('Contacts/' . $this->contact_id . '/file/');
        $this->assertNotEmpty($restReply['reply'], 'First reply was empty');
        $this->assertArrayHasKey('picture', $restReply['reply'], 'Missing response data for Contacts');
        $restReply = $this->restCall('Notes/' . $this->note_id . '/file/');
        $this->assertNotEmpty($restReply['reply'], 'Second reply was empty');
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Missing response data for Notes');
    }

    /**
     * @group rest
     */
    public function testPostUploadImageTempToContact()
    {
        // Upload a temporary file
        $post = ['picture' => '@include/images/badge_256.png'];
        $reply = $this->restCall('Contacts/temp/file/picture', $post);
        $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
        $this->assertNotEmpty($reply['reply']['picture']['guid'], 'File guid not returned');

        // Grab the temporary file and make sure it is present
        $fetch = $this->restCall('Contacts/temp/file/picture/' . $reply['reply']['picture']['guid']);
        $this->assertNotEmpty($fetch['replyRaw'], 'Temporary file is missing');

        // Grab the temporary file and make sure it's been deleted
        $fetch = $this->restCall('Contacts/temp/file/picture/' . $reply['reply']['picture']['guid']);
        $this->assertArrayHasKey('error', $fetch['reply'], 'Temporary file is still here');
        $this->assertEquals('invalid_parameter', $fetch['reply']['error'], 'Expected error string not returned');
    }

    /**
     * @group rest
     */
    public function testPostUploadImageToContact()
    {
        $post = ['picture' => '@include/images/badge_256.png'];
        $reply = $this->restCall('Contacts/' . $this->contact_id . '/file/picture', $post);
        $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
        $this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');

        // Grab the contact and make sure it saved
        $fetch = $this->restCall('Contacts/' . $this->contact_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Contact ID is missing');
        $this->assertEquals($this->contact_id, $fetch['reply']['id'], 'Known contact id and fetched contact id do not match');
        $this->assertEquals($reply['reply']['picture']['name'], $fetch['reply']['picture'], 'Contact picture field and picture file name do not match');
    }

    /**
     * @group rest
     */
    public function testPostUploadImageToContactWithHTMLJSONResponse()
    {
        $post = ['picture' => '@include/images/badge_256.png'];
        $reply = $this->restCall('Contacts/' . $this->contact_id . '/file/picture?format=sugar-html-json', $post);
        //$this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
        //$this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');
        $this->assertNull($reply['reply'], 'Decoded reply should be null');
        $this->assertNotEmpty($reply['replyRaw'], 'Raw Reply should contain an HTML encoded JSON string');
        $this->assertContains('&quot;picture&quot;', $reply['replyRaw'], 'Raw reply should contain "picture"');

        $decoded = json_decode(html_entity_decode($reply['replyRaw'], ENT_COMPAT), true);
        $this->assertNotEmpty($decoded['picture']['content-type'], 'Sugar HTML JSON result not decodeable');
        $this->assertEquals('image/png', $decoded['picture']['content-type'], 'Content Type value incorrect');
    }

    /**
     * @ticket bug59995
     * @group rest
     */
    public function testPostUploadCrazyEncodingErrorStatusResponse()
    {
        $post = ['picture' => ''];
        $reply = $this->restCall('Contacts/' . $this->contact_id . '/file/picture?format=sugar-html-json', $post);
        $this->assertEquals($reply['info']['http_code'], 200, 'HTTP Code should be 200 (bug59995)');


        $post = ['picture' => ''];
        $reply = $this->restCall('Contacts/' . $this->contact_id . '/file/picture', $post);
        $this->assertEquals($reply['info']['http_code'], 413, 'HTTP Code is not 413 (bug59995)');
    }

    /**
     * @group rest
     */
    public function testPostUploadNonImageToContact()
    {
        $post = ['picture' => '@include/fonts/Courier.afm'];
        $reply = $this->restCall('Contacts/' . $this->contact_id . '/file/picture', $post);
        $this->assertArrayHasKey('error', $reply['reply'], 'Bug58324 - No error message returned');
        $this->assertEquals('fatal_error', $reply['reply']['error'], 'Bug58324 - Expected error string not returned');
    }

    /**
     * @group rest
     */
    public function testPutUploadImageToContact()
    {
        $filename = 'include/images/badge_256.png';
        $opts = [CURLOPT_INFILESIZE => filesize($filename), CURLOPT_INFILE => fopen($filename, 'r')];
        $headers = ['Content-Type: image/png', 'filename: ' . basename($filename)];
        $reply = $this->restCall('Contacts/' . $this->contact_id . '/file/picture', '', 'PUT', $opts, $headers);
        $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
        $this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');

        // Grab the contact and make sure it saved
        $fetch = $this->restCall('Contacts/' . $this->contact_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Contact ID is missing');
        $this->assertEquals($this->contact_id, $fetch['reply']['id'], 'Known contact id and fetched contact id do not match');
        $this->assertEquals($reply['reply']['picture']['name'], $fetch['reply']['picture'], 'Contact picture field and picture file name do not match');
    }

    /**
     * @group rest
     */
    public function testDeleteImageFromContact()
    {
        $reply = $this->restCall('Contacts/' . $this->contact_id . '/file/picture', '', 'DELETE');
        $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing fields');
    }

    /**
     * @group rest
     */
    public function testPostUploadFileToNote()
    {
        $post = ['filename' => '@' . $this->testfile1];
        $restReply = $this->restCall('Notes/' . $this->note_id . '/file/filename', $post);
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Reply is missing file name key');
        $this->assertNotEmpty($restReply['reply']['filename']['name'], 'File name returned empty');

        // Now get the note to make sure it saved
        $fetch = $this->restCall('Notes/' . $this->note_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->note_id, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertEquals($restReply['reply']['filename']['name'], $fetch['reply']['filename']);
    }

    /**
     * @group rest
     */
    public function testPutUploadFileToNote()
    {
        $params = ['filename' => $this->testfile2, 'type' => 'text/plain'];
        $restReply = $this->restCallFilePut('Notes/' . $this->note_id . '/file/filename', $params);
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Reply is missing file name key');
        $this->assertNotEmpty($restReply['reply']['filename']['name'], 'File name returned empty');

        // Now get the note to make sure it saved
        $fetch = $this->restCall('Notes/' . $this->note_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->note_id, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertEquals($restReply['reply']['filename']['name'], $fetch['reply']['filename']);
    }

    /**
     * @group rest
     */
    public function testDeleteFileFromNote()
    {
        $reply = $this->restCall('Notes/' . $this->note_id . '/file/filename', '', 'DELETE');
        $this->assertArrayHasKey('filename', $reply['reply'], 'Reply is missing fields');
    }
}

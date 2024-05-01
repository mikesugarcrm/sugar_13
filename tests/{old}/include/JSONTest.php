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

class JSONTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION['asychronous_key']);
    }

    public function testCanEncodeBasicArray()
    {
        $array = ['foo' => 'bar', 'bar' => 'foo'];
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encode($array)
        );
    }

    public function testCanEncodeBasicObjects()
    {
        $obj = new stdClass();
        $obj->foo = 'bar';
        $obj->bar = 'foo';
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encode($obj)
        );
    }

    public function testCanEncodeMultibyteData()
    {
        $array = ['foo' => '契約', 'bar' => '契約'];
        $this->assertEquals(
            '{"foo":"\u5951\u7d04","bar":"\u5951\u7d04"}',
            JSON::encode($array)
        );
    }

    public function testCanDecodeObjectIntoArray()
    {
        $array = ['foo' => 'bar', 'bar' => 'foo'];
        $this->assertEquals(
            JSON::decode('{"foo":"bar","bar":"foo"}'),
            $array
        );
    }

    public function testCanDecodeMultibyteData()
    {
        $array = ['foo' => '契約', 'bar' => '契約'];
        $this->assertEquals(
            JSON::decode('{"foo":"\u5951\u7d04","bar":"\u5951\u7d04"}'),
            $array
        );
    }

    public function testEncodeRealWorks()
    {
        $array = ['foo' => 'bar', 'bar' => 'foo'];
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encodeReal($array)
        );
    }

    public function testDecodeRealWorks()
    {
        $array = ['foo' => 'bar', 'bar' => 'foo'];
        $this->assertEquals(
            JSON::decodeReal('{"foo":"bar","bar":"foo"}'),
            $array
        );
    }

    public function testCanDecodeHomefinder()
    {
        $response = '{"data":{"meta":{"currentPage":1,"totalMatched":1,"totalPages":1,"executionTime":0.025315999984741},"affiliates":[{"name":"Los Angeles Times","profileName":"latimes","parentCompany":"Tribune Company","isActive":true,"hasEcommerceEnabled":true,"profileNameLong":"latimes","homePageUrl":"http:\/\/www.latimes.com\/classified\/realestate\/","createDateTime":"2008-07-25T00:00:00-05:00","updateDateTime":"2011-02-16T00:00:00-06:00","id":137}]},"status":{"code":200,"errorStack":null}}';
        $json = new JSON();
        $decode = $json->decode($response);
        $this->assertNotEmpty($decode['data']['affiliates'][0]['profileName'], 'Did not decode correctly');
    }

    public function testCanDecodeHomefinderAsObject()
    {
        $response = '{"data":{"meta":{"currentPage":1,"totalMatched":1,"totalPages":1,"executionTime":0.025315999984741},"affiliates":[{"name":"Los Angeles Times","profileName":"latimes","parentCompany":"Tribune Company","isActive":true,"hasEcommerceEnabled":true,"profileNameLong":"latimes","homePageUrl":"http:\/\/www.latimes.com\/classified\/realestate\/","createDateTime":"2008-07-25T00:00:00-05:00","updateDateTime":"2011-02-16T00:00:00-06:00","id":137}]},"status":{"code":200,"errorStack":null}}';
        $json = new JSON();
        $decode = $json->decode($response, false, false);
        $this->assertNotEmpty($decode->data->affiliates[0]->profileName, 'Did not decode correctly');
    }
}

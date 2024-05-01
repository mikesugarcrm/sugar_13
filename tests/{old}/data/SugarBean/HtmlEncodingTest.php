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
 * @covers SugarBean::htmlEncodeRow()
 */
class HtmlEncodingTest extends TestCase
{
    /**
     * @var DataPrivacy
     */
    private static $bean;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        self::$bean = BeanFactory::newBean('DataPrivacy');
        self::$bean->source = '<b>Hello, world!</b>';
        self::$bean->fields_to_erase = '{"foo": "bar"}';
        self::$bean->save();
    }

    public static function tearDownAfterClass(): void
    {
        $conn = DBManagerFactory::getConnection();
        $conn->delete(
            self::$bean->table_name,
            [
                'id' => self::$bean->id,
            ]
        );

        SugarTestCommentUtilities::removeAllCreatedComments();
    }

    /**
     * @test
     */
    public function encodingEnabled()
    {
        $bean = $this->retrieveBean(true);
        $this->assertSame('&lt;b&gt;Hello, world!&lt;/b&gt;', $bean->source);
        $this->assertSame('{"foo": "bar"}', $bean->fields_to_erase);
    }

    /**
     * @test
     */
    public function encodingDisabled()
    {
        $comment = $this->retrieveBean(false);
        $this->assertSame('<b>Hello, world!</b>', $comment->source);
        $this->assertSame('{"foo": "bar"}', $comment->fields_to_erase);
    }

    /**
     * @param bool $encode
     * @return DataPrivacy
     */
    private function retrieveBean($encode)
    {
        return BeanFactory::getBean(self::$bean->module_name, self::$bean->id, [
            'use_cache' => false,
            'encode' => $encode,
        ]);
    }
}

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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass EmailsRelateRecordApi
 * @group api
 * @group email
 */
class EmailsRelateRecordApiTest extends TestCase
{
    protected $service;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
    }

    public function readOnlyLinkProvider()
    {
        return [
            [
                'from',
            ],
            [
                'to',
            ],
            [
                'cc',
            ],
            [
                'bcc',
            ],
            [
                'attachments',
            ],
        ];
    }

    /**
     * The from, to, cc, bcc, and attachments links are readonly.
     *
     * @dataProvider readOnlyLinkProvider
     * @covers ::updateRelatedLink
     */
    public function testUpdateRelatedLink($linkName)
    {
        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();

        $note = BeanFactory::newBean('Notes');
        $note->id = Uuid::uuid1();

        $args = [
            'module' => 'Emails',
            'record' => $email->id,
            'link_name' => $linkName,
            'remote_id' => $note->id,
        ];

        $api = $this->createPartialMock('EmailsRelateRecordApi', [
            'loadBean',
            'checkRelatedSecurity',
        ]);
        $api->expects($this->once())->method('loadBean')->willReturn($email);
        $api->expects($this->once())->method('checkRelatedSecurity')->willReturn([$linkName, $note]);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $api->updateRelatedLink($this->service, $args);
    }
}

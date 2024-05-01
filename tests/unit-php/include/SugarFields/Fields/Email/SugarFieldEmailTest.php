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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarFields\Fields\Email;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \SugarFieldEmail
 */
class SugarFieldEmailTest extends TestCase
{
    /**
     * @covers ::formatEmails
     */
    public function testFormatEmails()
    {
        $emails = [
            [
                'email_address' => 'foo@bar.com',
                'email_address_caps' => 'FOO@BAR.COM',
                'invalid_email' => 0,
                'opt_out' => 0,
                'id' => Uuid::uuid1(),
                'email_address_id' => Uuid::uuid1(),
                'primary_address' => 1,
                'reply_to_address' => 0,
            ],
            [
                'email_address' => 'biz@baz.com',
                'email_address_caps' => 'BIZ@BAZ.COM',
                'invalid_email' => 0,
                'opt_out' => 0,
                'id' => Uuid::uuid1(),
                'email_address_id' => Uuid::uuid1(),
                'primary_address' => 0,
                'reply_to_address' => 1,
            ],
            [
                'email_address' => 'qux@qar.com',
                'email_address_caps' => 'QUX@QAR.COM',
                'invalid_email' => 0,
                'opt_out' => 0,
                'id' => Uuid::uuid1(),
                'email_address_id' => Uuid::uuid1(),
                'primary_address' => 0,
                'reply_to_address' => 0,
            ],
        ];

        // A partial mock is created because we don't want to call the constructor, which has some unnecessary
        // dependencies, but we want all methods to operate as they are implemented.
        $sf = $this->createPartialMock('\\SugarFieldEmail', []);
        array_walk($emails, [$sf, 'formatEmails']);

        $expected = [
            [
                'email_address' => 'foo@bar.com',
                'invalid_email' => false,
                'opt_out' => false,
                'email_address_id' => $emails[0]['email_address_id'],
                'primary_address' => true,
                'reply_to_address' => false,
            ],
            [
                'email_address' => 'biz@baz.com',
                'invalid_email' => false,
                'opt_out' => false,
                'email_address_id' => $emails[1]['email_address_id'],
                'primary_address' => false,
                'reply_to_address' => true,
            ],
            [
                'email_address' => 'qux@qar.com',
                'invalid_email' => false,
                'opt_out' => false,
                'email_address_id' => $emails[2]['email_address_id'],
                'primary_address' => false,
                'reply_to_address' => false,
            ],
        ];
        $this->assertEquals($expected, $emails);
    }
}

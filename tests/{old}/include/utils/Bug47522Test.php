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

require_once 'include/utils.php';

/*
 This unit test simulates cleaning the key values in a POST/REQUEST when mbstring.encoding_translation is on.
 When you turn on  mbstring.encoding_translation in php.ini, it's supposed to translate the characters in the POST array during an http request.
 It's not supposed to touch the key names though.  This test is for the code that cleans those key names
  */

class Bug47522Test extends TestCase
{
    public $orig_ini_encoding_val;

    protected function setUp(): void
    {
        $this->orig_ini_encoding_val = ini_get('mbstring.encoding_translation');

        //set http translation on
        ini_set('mbstring.encoding_translation', 1);
    }

    protected function tearDown(): void
    {
        //set back value of ini setting
        ini_set('mbstring.encoding_translation', $this->orig_ini_encoding_val);

        unset($this->orig_ini_encoding_val);
    }

    public function testEncodedKeyCleaning()
    {
        //continue this test only if encoding_translation is turned on.
        if (ini_get('mbstring.encoding_translation') === '1') {
            //inject bad string into request
            $key = "'you'shall'not'pass!";
            $val = ' must.. not.. die..';
            $_REQUEST[$key] = $val;

            //assert the key is in the request object
            $this->assertsame($_REQUEST[$key], $val, 'request key was not set, rest of test is invalid');

            //clean the string, it should fail but since encoding translation is on, it should only remove the
            //key from request object
            securexsskey($key, false);

            //assert the key is no longer in request
            $this->assertNotContains($key, $_REQUEST, 'Key should not hav passed xss security check, but still exists in request, this is wrong.');
        } else {
            //encoding_translation is turned off, this test will not work, so let's skip it
            $this->markTestSkipped('mbstring.encoding_translation is turned off, mark as skipped for now.');
        }
    }
}

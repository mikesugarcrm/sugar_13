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


class RestCurrentUserPreferenceTest extends RestTestBase
{
    protected static $testPreferences = [
        'hello' => 'world',
        'test' => 'preference',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // add a test preference to the user
        foreach (self::$testPreferences as $key => $pref) {
            $this->user->setPreference($key, $pref);
        }

        // save the preferences to the db.
        $this->user->savePreferencesToDB();
    }

    protected function tearDown(): void
    {
        // clean up the preferences
        $this->user->resetPreferences();

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testGetUserPreferences()
    {
        $reply = $this->restCall('me/preferences');

        // assert that the reply has our two preferences in it
        $this->assertEquals('world', $reply['reply']['hello']);
        $this->assertEquals('preference', $reply['reply']['test']);
    }

    /**
     * @dataProvider dataProviderGetSpecificPreference
     * @group rest
     *
     * @param $key
     * @param $value
     */
    public function testGetSpecificPreference($key, $value)
    {
        $reply = $this->restCall('me/preference/' . $key);

        $this->assertEquals($value, $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testUpdatePreferenceReturnsNewKeyValuePair()
    {
        $reply = $this->restCall('me/preference/hello', json_encode(['value' => 'world1']), 'PUT');

        $this->assertEquals(['hello' => 'world1'], $reply['reply']);
    }

    public function testUpdateMultiplePreferencesReturnsUpdatedKeyValuePair()
    {
        $reply = $this->restCall(
            'me/preferences',
            json_encode(['hello' => 'world1', 'test' => 'preference1']),
            'PUT'
        );

        $this->assertEquals(['hello' => 'world1', 'test' => 'preference1'], $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testUpdatePreferenceWithSpecificMetaName()
    {
        $reply = $this->restCall(
            'me/preferences',
            json_encode(['datepref' => '(y)(m)(d)', 'timepref' => '(h)(i)']),
            'PUT'
        );

        $this->assertEquals('(y)(m)(d)', $reply['reply']['datepref']);
        $this->assertEquals('(h)(i)', $reply['reply']['timepref']);

        // check updated preferences in current user object
        $reply = $this->restCall('me');
        $this->assertEquals('(y)(m)(d)', $reply['reply']['current_user']['preferences']['datepref']);
        $this->assertEquals('(h)(i)', $reply['reply']['current_user']['preferences']['timepref']);
    }

    /**
     * @group rest
     */
    public function testCreatePreferenceReturnsCreatedKeyValuePair()
    {
        $reply = $this->restCall('me/preference/create', json_encode(['value' => 'preference']), 'POST');

        $this->assertEquals(['create' => 'preference'], $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testDeletePreferenceReturnsDeletedKey()
    {
        $reply = $this->restCall('me/preference/hello', json_encode(['value' => 'preference']), 'DELETE');

        $this->assertEquals('hello', $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testGetNonExistantPreferenceReturnsEmptyValue()
    {
        $reply = $this->restCall('me/preference/this_pref_does_not_exist');

        $this->assertSame('', $reply['reply']);
    }


    public static function dataProviderGetSpecificPreference()
    {
        $return = [];
        foreach (self::$testPreferences as $key => $pref) {
            $return[] = [$key, $pref];
        }

        return $return;
    }
}

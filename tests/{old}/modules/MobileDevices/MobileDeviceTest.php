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
use Sugarcrm\Sugarcrm\PushNotification\SugarPush\SugarPush;

class MobileDeviceTest extends TestCase
{
    protected $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->bean = $this->getNewBean('platform', 'device_id');
        $this->bean->save();
    }

    protected function tearDown(): void
    {
        global $db;
        if (!empty($this->bean->id)) {
            $sql = 'DELETE FROM mobile_devices WHERE id = ' . $db->quoted($this->bean->id);
            $db->query($sql);
        }
        SugarTestHelper::tearDown();
    }

    /**
     * @param $platform
     * @param $deviceId
     * @return SugarBean|null
     */
    protected function getNewBean($platform, $deviceId)
    {
        global $current_user;
        $bean = new MobileDeviceWrapper();
        $bean->device_platform = $platform;
        $bean->assigned_user_id = $current_user->id;
        $bean->device_id = $deviceId;
        return $bean;
    }

    /**
     * @return array[]
     */
    public function saveProvider(): array
    {
        return [
            ['device_id', 'platform', true, true],
            ['device_id1', 'platform', false, false],
            ['device_id', 'platform1', false, false],
        ];
    }

    /**
     * @param string $deviceId
     * @param string $platform
     * @param bool $create
     * @param bool $shouldReturnNull
     * @dataProvider saveProvider
     */
    public function testSave(string $deviceId, string $platform, bool $create, bool $shouldReturnNull)
    {
        if ($create) {
            $bean = $this->getNewBean($platform, $deviceId);
        } else {
            $bean = $this->bean;
        }
        $bean->device_platform = $platform;
        $bean->device_id = $deviceId;
        $ret = $bean->save();
        if ($shouldReturnNull) {
            $this->assertNull($ret);
        } else {
            $this->assertNotNull($ret);
        }
    }

    public function testSaveRefused()
    {
        // we have a saved record
        $bean = $this->getNewBean('platform', 'to_remove');
        $bean->save();

        // now we want to save it again and call "relayUpdateRequest" that will return false. It means the record must
        // be deleted
        $ret = $bean->save();

        $this->assertNull($ret);
        $this->assertEquals(1, $bean->deleted);

        // and now we may register a new one with "relayRegisterRequest"
        $ret = $bean->save();
        $this->assertNotNull($ret);
    }

    // In case $service->setActive returned false the MobileDevice must be marked as deleted
    public function testOnLoggedIn()
    {
        $this->bean->service = $this->getMockBuilder(SugarPush::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(0, $this->bean->deleted);

        $this->bean->onLoggedIn();

        $bean = BeanFactory::getBean($this->bean->getModuleName(), $this->bean->id, ['use_cache' => false, ''], false);

        $this->assertEquals(1, $bean->deleted);
    }

    // In case $service->setActive returned false the MobileDevice must be marked as deleted
    public function testOnLoggedOut()
    {
        $this->bean->service = $this->getMockBuilder(SugarPush::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(0, $this->bean->deleted);

        $this->bean->onLoggedOut();

        $bean = BeanFactory::getBean($this->bean->getModuleName(), $this->bean->id, ['use_cache' => false, ''], false);

        $this->assertEquals(1, $bean->deleted);
    }
}

/**
 * Class MobileDeviceWrapper
 */
class MobileDeviceWrapper extends \MobileDevice
{
    public $service;

    /**
     * @return bool
     */
    protected function relayRegisterRequest(): bool
    {
        return true;
    }

    protected function relayUpdateRequest(string $oldId, string $newId = ''): ?bool
    {
        return $oldId === 'to_remove' ? false : null;
    }

    protected function getService()
    {
        return $this->service;
    }
}

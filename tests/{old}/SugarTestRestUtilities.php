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


class SugarTestRestUtilities
{
    private function __construct()
    {
    }

    /**
     * Get the RestServiceMock
     * @param User $user A User to put in the rest service
     * @param string $platform
     * @param string $type
     * @return RestService SugarTestRestService
     */
    public static function getRestServiceMock(User $user = null, string $platform = 'base', string $type = 'user')
    {
        $mock = new SugarTestRestServiceMock();
        $mock->user = ($user == null) ? $GLOBALS['current_user'] : $user;
        // set-up platform, type and visibility
        $mock->platform = $platform;
        $mock->type = $type;
        $mock->setupVisibilityForMock();

        // Api helpers must be reset after a new service was created.
        ApiHelper::$moduleHelpers = [];

        return $mock;
    }

    /**
     * Clean state created by getRestServiceMock
     * @param string $platform
     */
    public static function cleanupRestServiceMock(string $platform = 'base')
    {
        $mock = new SugarTestRestServiceMock();
        $mock->platform = $platform;
        $mock->cleanupVisibilityForMock();
    }
}

class SugarTestRestServiceMock extends RestService
{
    public $type;

    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }

    public function getVersion()
    {
        return 10;
    }

    public function getUrlVersion()
    {
        return 'v10';
    }

    /**
     * Setup visibility
     */
    public function setupVisibilityForMock()
    {
        if ($this->platform === 'portal') {
            // store platform in session
            $_SESSION['platform'] = $this->platform;
            $_SESSION['type'] = $this->type;

            // visibility and acl setup
            $_SESSION['authenticated_user_id'] = !empty($this->user->id) ? $this->user->id : null;
            if (!empty($_SESSION['authenticated_user_id'])) {
                $oauthServer = \SugarOAuth2Server::getOAuth2Server($this->platform);
                $oauthServer->setupVisibility();
            }
        }
    }

    /**
     * Clean state created by setupVisibilityForMock
     */
    public function cleanupVisibilityForMock()
    {
        if ($this->platform === 'portal') {
            unset($_SESSION['platform']);
            unset($_SESSION['type']);
            unset($_SESSION['authenticated_user_id']);
        }
        SugarACL::resetACLs();
    }
}

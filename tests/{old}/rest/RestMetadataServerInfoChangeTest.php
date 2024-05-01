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


class RestMetadataServerInfoChangeTest extends RestTestBase
{
    /**
     * @group rest
     */
    public function testServerInfoChangeTest()
    {
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();

        $mm = MetaDataManager::getManager(['mobile']);
        $original_server_info = $mm->getServerInfo();

        $restReply = $this->restCall('metadata?platform=mobile');
        $server_info = $restReply['reply']['server_info'];

        $this->assertEquals($original_server_info['fts'], $server_info['fts'], 'Server Info not equal');

        $new_server_info = $original_server_info;
        $new_server_info['fts'] = ['enabled' => true, 'type' => 'Elastic'];

        $ac = new AdministrationController();
        $_REQUEST['type'] = 'Elastic';
        $_REQUEST['host'] = 'localhost';
        $_REQUEST['port'] = '9200';

        ob_start();
        $ac->action_saveglobalsearchsettings();
        ob_end_clean();

        $restReply = $this->restCall('metadata?platform=mobile');
        $server_info = $restReply['reply']['server_info'];

        $this->assertEquals($new_server_info['fts'], $server_info['fts'], 'New Server Info not equal');

        $_REQUEST['type'] = '';
        $_REQUEST['host'] = '';
        $_REQUEST['port'] = '';

        ob_start();
        $ac->action_saveglobalsearchsettings();
        ob_end_clean();
    }
}

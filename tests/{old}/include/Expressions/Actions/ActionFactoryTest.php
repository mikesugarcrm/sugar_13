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

class ActionFactoryTest extends TestCase
{
    public $removeCustomDir = false;

    protected function createCustomAction()
    {
        $actionContent = <<<EOQ
<?php

class TestCustomAction extends AbstractAction{
    function __construct(\$params) { }
    static function getJavascriptClass() { return ""; }
    function getJavascriptFire() { return ""; }
    function fire(&\$target){}
    function getDefinition() {
        return array(
            "action" => \$this->getActionName(),
            "target" => "nothing"
        );
    }

    static function getActionName() {
        return "testCustomAction";
    }
}
EOQ;
        if (!is_dir('custom/' . ActionFactory::$action_directory)) {
            SugarAutoLoader::ensureDir('custom/' . ActionFactory::$action_directory);
            $this->removeCustomDir = true;
        }
        file_put_contents('custom/' . ActionFactory::$action_directory . '/testCustomAction.php', $actionContent);
    }

    protected function removeCustomAction()
    {
        unlink('custom/' . ActionFactory::$action_directory . '/testCustomAction.php');
        if ($this->removeCustomDir) {
            rmdir('custom/' . ActionFactory::$action_directory);
        }
    }

    public function testGetNewAction()
    {
        $sva = ActionFactory::getNewAction(
            'SetValue',
            [
                'target' => 'name',
                'value' => 'strlen($name)',
            ]
        );
        $this->assertInstanceOf('SetValueAction', $sva);
    }

    public function testLoadCustomAction()
    {
        $this->createCustomAction();
        ActionFactory::buildActionCache(true);
        $customAction = ActionFactory::getNewAction('testCustomAction', []);
        $this->assertInstanceOf('TestCustomAction', $customAction);
        $this->removeCustomAction();
        ActionFactory::buildActionCache(true);
    }
}

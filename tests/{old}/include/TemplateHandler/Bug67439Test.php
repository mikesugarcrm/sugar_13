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

class Bug67439Test extends TestCase
{
    protected static $oldObjectList;

    protected static $filesToUnlink = [
        'cache/modules/Teams/EditView.tpl',
        'cache/modules/Teams/SearchForm_1.tpl',
        'cache/modules/Teams/DetailView.tpl',
    ];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('mod_strings', ['Teams']);

        $GLOBALS['beanFiles']['CustomTeam'] = 'custom/modules/Teams/Team.php';
        $GLOBALS['beanList']['Teams'] = 'CustomTeam';

        if (isset($GLOBALS['objectList'])) {
            static::$oldObjectList = $GLOBALS['objectList'];
        } else {
            $GLOBALS['objectList'] = static::$oldObjectList = [];
        }
        $GLOBALS['objectList']['Teams'] = 'Team';
    }

    protected function tearDown(): void
    {
        $GLOBALS['objectList'] = static::$oldObjectList;
        foreach (static::$filesToUnlink as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * @dataProvider dataProviderForBuildTemplate
     */
    public function testBuildTemplate($view, $metaDataDefs)
    {
        $templateHandler = $this->getMockBuilder('TemplateHandler')
            ->setMethods([
                'loadSmarty',
                'createQuickSearchCode',
                'createDependencyJavascript',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $templateHandler->expects($this->any())
            ->method('createQuickSearchCode')
            ->with($this->equalTo($GLOBALS['dictionary']['Team']['fields']));
        $templateHandler->expects($this->any())
            ->method('createDependencyJavascript')
            ->with($this->equalTo($GLOBALS['dictionary']['Team']['fields']));

        $sugarSmarty = $this->getMockBuilder('Sugar_Smarty')
            ->setMethods(['assign', 'fetch'])
            ->getMock();
        $sugarSmarty->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue('template content'));

        $templateHandler->ss = $sugarSmarty;

        $templateHandler->buildTemplate('Teams', $view, 'tpl', false, $metaDataDefs);
    }

    public function dataProviderForBuildTemplate()
    {
        $metaDataDefs = [
            'panels' => [
                [
                    [
                        ['name' => 'some_name'],
                        'other_name',
                    ],
                ],
            ],
        ];

        return [
            ['EditView', $metaDataDefs],
            ['SearchForm_1', []],
            ['DetailView', []],
        ];
    }
}

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
 * Name field is no longer a hyperlink after moving the field from Default to Hidden back to Default
 * in the Studio subpanel definition for custom module
 *
 * @ticket 36668
 */
class Bug36668Test extends TestCase
{
    public static function fieldDefProvider()
    {
        return [
            [true, 'relate', '0'],
            [true, 'name', '1'],
            [false, 'name', '0'],
        ];
    }

    /**
     * @dataProvider fieldDefProvider
     * @group 36668
     */
    public function testMakeRelateFieldsAsLink($flag, $type, $link)
    {
        $defs = ['name' => ['type' => $type, 'link' => $link]];

        $parser = $this->createPartialMock(SubpanelMetaDataParser::class, []);

        // Field defs without id_name properties were throwing errors. Adding id_name
        // here to allow tests to run around modification to the core code.
        $parser->_fielddefs = [
            'name' => ['module' => 'test', 'id_name' => 'test'],
        ];

        $newDefs = SugarTestReflection::callProtectedMethod($parser, 'makeRelateFieldsAsLink', [$defs]);

        if ($flag) {
            $this->assertArrayHasKey('widget_class', $newDefs['name']);
        } else {
            $this->assertArrayNotHasKey('widget_class', $newDefs['name']);
        }
    }
}

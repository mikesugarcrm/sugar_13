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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ModuleBuilder\parsers\views;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SidecarGridLayoutMetaDataParser
 */
class SidecarGridLayoutMetaDataParserTest extends TestCase
{
    /**
     * @covers ::isValidFieldPortal
     */
    public function testIsValidFieldPortal()
    {
        $key = 'primary_contact_name';
        $def = [
            'name' => 'primary_contact_name',
            'type' => 'relate',
            'studio' => [
                'portal' => [
                    'portalrecordview' => true,
                ],
            ],
        ];

        $parser = $this->getMockBuilder('SidecarGridLayoutMetaDataParser')
            ->disableOriginalConstructor()->setMethods(null)->getMock();
        $parser->_view = 'portalrecordview';
        $parser->client = 'portal';
        $this->assertEquals(true, $parser->isValidFieldPortal($key, $def));
    }
}

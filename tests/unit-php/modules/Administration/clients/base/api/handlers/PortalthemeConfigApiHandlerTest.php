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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api\handlers;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class PortalthemeConfigApiHandlerTest
 * @coversDefaultClass \PortalthemeConfigApiHandler
 */
class PortalthemeConfigApiHandlerTest extends TestCase
{
    /**
     * @covers ::setStyleguideConfig
     */
    public function testSetStyleguideConfig(): void
    {
        $api = $this->createMock(\ServiceBase::class);
        $args = [
            'category' => 'portaltheme',
            'portaltheme_button_color' => 'white',
            'portaltheme_text_link_color' => 'black',
        ];

        $themeArgs = [
            'platform' => 'portal',
            'themeName' => 'default',
            'PrimaryButton' => 'white',
            'LinkColor' => 'black',
        ];
        $themeApiMock = $this->createPartialMock(
            \ThemeApi::class,
            ['updateCustomTheme']
        );
        $themeApiMock->expects($this->once())->method('updateCustomTheme')
            ->with($api, $themeArgs);

        $handler = $this->createPartialMock(
            \PortalthemeConfigApiHandler::class,
            ['getThemeApi']
        );
        $handler->method('getThemeApi')->willReturn($themeApiMock);

        TestReflection::callProtectedMethod($handler, 'setStyleguideConfig', [$api, $args]);
    }
}

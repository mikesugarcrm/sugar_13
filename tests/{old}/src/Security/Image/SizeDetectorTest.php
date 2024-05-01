<?php

declare(strict_types=1);
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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Image;

use Google\Service\AdExchangeBuyerII\Size;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Dns\QueryFailedException;
use Sugarcrm\Sugarcrm\Security\Image\SizeDetector;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Image\SizeDetector
 */
class SizeDetectorTest extends TestCase
{
    public function testLocalImgeGetSize()
    {
        $size = (new SizeDetector())->getSize('themes/default/images/company_logo_dark.png');
        $this->assertIsArray($size);
        $this->assertEquals(1000, $size[0]);
        $this->assertEquals(204, $size[1]);
    }

    public function testPortInPathIsForbidden()
    {
        $sizeDetector = new SizeDetector();
        $size = $sizeDetector->getSize('http://localhost:8888/themes/default/images/company_logo_dark.png');
        $this->assertFalse($size);
        $this->assertEquals('Ports are not allowed', $sizeDetector->getError());
    }

    public function testInvalidScheme()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new SizeDetector())->getSize('ftp://user:pass@ftp.example.com/logo.png');
    }

    public function testAllowedDomains()
    {
        $allowedDomains = ['example.com'];
        $sizeDetector = $this->getMockBuilder(SizeDetector::class)
            ->setConstructorArgs([$allowedDomains])
            ->onlyMethods(['generateTmpFileName', 'getImageInfo'])
            ->getMock();
        $sizeDetector->expects($this->once())
            ->method('getImageInfo');
        $sizeDetector->expects($this->never())
            ->method('generateTmpFileName');
        $sizeDetector->getSize('https://example.com/logo.png');
    }

    public function testUnreachableExternalImages()
    {
        $allowedDomains = ['example.com'];
        $this->expectException(QueryFailedException::class);
        $sizeDetector = new SizeDetector($allowedDomains);
        $sizeDetector->getSize('https://Just-Unresolvalble-domain.tld/logo.png');
    }

    public function testImageWithInvalidUrl()
    {
        $allowedDomains = ['example.com'];
        $this->expectException(\InvalidArgumentException::class);
        $sizeDetector = new SizeDetector($allowedDomains);
        $sizeDetector->getSize('https://just_invalid_domain.tld/logo.png');
    }

    public function downloadSuccessProvider(): array
    {
        return [
            [true,],
            [false,],
        ];
    }

    /**
     * @dataProvider  downloadSuccessProvider
     * @return void
     */
    public function testTmpImageIsRemoved($success)
    {
        $allowedDomains = ['example.com'];
        $sizeDetector = $this->getMockBuilder(SizeDetector::class)
            ->setConstructorArgs([$allowedDomains])
            ->onlyMethods(['generateTmpFileName', 'getImageInfo', 'download'])
            ->getMock();
        $sizeDetector->expects($this->any())
            ->method('getImageInfo')
            ->willReturn(false);
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        $sizeDetector->expects($this->once())
            ->method('generateTmpFileName')
            ->willReturn($tmpFile);
        $sizeDetector->method('download')
            ->willReturn($success);
        $this->assertFileExists($tmpFile);
        $sizeDetector->getSize($GLOBALS['sugar_config']['site_url'] .'/themes/default/images/company_logo_dark.png');
        $this->assertFileDoesNotExist($tmpFile);
    }
}

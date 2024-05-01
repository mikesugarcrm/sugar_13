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

namespace Sugarcrm\SugarcrmTestsUnit\CSP;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\CSP\ContentSecurityPolicy;
use Sugarcrm\Sugarcrm\CSP\Directive;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\CSP\ContentSecurityPolicy
 */
class ContentSecurityPolicyTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        \BeanFactory::unsetBeanClass(\Administration::class);
    }

    /**
     * @covers ::fromDirectivesList
     */
    public function testFromDirectivesList()
    {
        $csp = ContentSecurityPolicy::fromDirectivesList(Directive::create('default-src', 'sugarcrm.com'));
        $this->assertInstanceOf(ContentSecurityPolicy::class, $csp);
    }

    /**
     * @covers ::getDirective
     * @covers ::setDirective
     */
    public function testGetSetDirective()
    {
        $csp = ContentSecurityPolicy::fromDirectivesList();
        $this->assertNull($csp->getDirective('default-src'));

        $directive = Directive::create('default-src', 'sugarcrm.com');
        $csp->setDirective($directive);
        $this->assertSame($directive, $csp->getDirective('default-src'));

        $directive2 = Directive::create('default-src', 'google.com');
        $csp->setDirective($directive2);
        $this->assertSame($directive2, $csp->getDirective('default-src'));
    }

    /**
     * @covers ::getDirectiveHidden
     * @covers ::setDirective
     */
    public function testGetSetDirectiveHidden()
    {
        $csp = ContentSecurityPolicy::fromDirectivesList();
        $this->assertNull($csp->getDirectiveHidden('default-src'));

        $directive = Directive::createHidden('default-src', 'sugarcrm.com');
        $csp->setDirective($directive);
        $this->assertSame($directive, $csp->getDirectiveHidden('default-src'));

        $directive2 = Directive::createHidden('default-src', 'google.com');
        $csp->setDirective($directive2);
        $this->assertSame($directive2, $csp->getDirectiveHidden('default-src'));
    }

    /**
     * @covers ::getDirective
     * @covers ::getDirectiveHidden
     * @covers ::setDirective
     */
    public function testGetSetDirectiveMixed()
    {
        $csp = ContentSecurityPolicy::fromDirectivesList();
        $this->assertNull($csp->getDirective('default-src'));
        $this->assertNull($csp->getDirectiveHidden('default-src'));

        $directive = Directive::create('default-src', 'sugarcrm.com');
        $csp->setDirective($directive);

        $directive2 = Directive::createHidden('default-src', 'google.com');
        $csp->setDirective($directive2);

        $this->assertSame($directive, $csp->getDirective('default-src'));
        $this->assertSame($directive2, $csp->getDirectiveHidden('default-src'));
    }

    /**
     * @covers ::appendDirective
     */
    public function testAppendDirective()
    {
        $directive = Directive::create('default-src', 'sugarcrm.com');
        $csp = ContentSecurityPolicy::fromDirectivesList($directive);
        $directive2 = Directive::create('default-src', 'google.com');
        $csp->appendDirective($directive2);
        $directive3 = Directive::createHidden('default-src', 'office.com');
        $csp->appendDirective($directive3);
        $directive4 = Directive::createHidden('default-src', 'amazon.com');
        $csp->appendDirective($directive4);

        $extendedDirective = $csp->getDirective('default-src');
        $this->assertEquals('default-src', $extendedDirective->name());
        $this->assertEquals('sugarcrm.com google.com', $extendedDirective->source());

        $extendedDirective2 = $csp->getDirectiveHidden('default-src');
        $this->assertEquals('default-src', $extendedDirective2->name());
        $this->assertEquals('office.com amazon.com', $extendedDirective2->source());
    }

    /**
     * @covers ::removeDirective
     */
    public function testRemoveDirective()
    {
        $directive = Directive::create('default-src', 'sugarcrm.com *.mashable.com');
        $directive2 = Directive::createHidden('default-src', 'google.com office.com');
        $csp = ContentSecurityPolicy::fromDirectivesList($directive, $directive2);

        $remove = Directive::create('default-src', '*.mashable.com');
        $removeHidden = Directive::createHidden('default-src', 'google.com');
        $removeNonexistent = Directive::create('default-src', 'nonexistent.com');
        $csp->removeDirective($remove);
        $csp->removeDirective($removeHidden);
        $csp->removeDirective($removeNonexistent);

        $this->assertEquals('sugarcrm.com', $csp->getDirective('default-src')->source());
        $this->assertEquals('office.com', $csp->getDirectiveHidden('default-src')->source());

        $removeHidden2 = Directive::createHidden('default-src', 'office.com');
        $csp->removeDirective($removeHidden2);
        $this->assertEquals('', $csp->getDirectiveHidden('default-src')->source());
    }

    /**
     * @covers ::asHeader
     */
    public function testAsHeader()
    {
        $directive = Directive::create('default-src', 'sugarcrm.com');
        $directive2 = Directive::create('img-src', 'cdn.example.com');
        $csp = ContentSecurityPolicy::fromDirectivesList($directive, $directive2);
        $this->assertEquals('Content-Security-Policy: default-src sugarcrm.com; img-src cdn.example.com', $csp->asHeader());
    }

    /**
     * @covers ::asString
     */
    public function testAsString()
    {
        $directive = Directive::create('default-src', 'sugarcrm.com');
        $directive2 = Directive::create('img-src', 'cdn.example.com');
        $csp = ContentSecurityPolicy::fromDirectivesList($directive, $directive2);
        $this->assertEquals('default-src sugarcrm.com; img-src cdn.example.com', $csp->asString());
    }

    /**
     * @covers ::fromAdministrationSettings
     */
    public function testFromEmptyAdministrationSettings()
    {
        $this->expectException(\DomainException::class);
        \BeanFactory::setBeanClass(\Administration::class, AdministrationSettingsMock::class);
        ContentSecurityPolicy::fromAdministrationSettings()->asString();
    }

    /**
     * @covers ::withAddedDefaults
     */
    public function testFromEmptyAdministrationSettingsWithDefaults()
    {
        \BeanFactory::setBeanClass(\Administration::class, AdministrationSettingsMock::class);
        $csp = ContentSecurityPolicy::fromAdministrationSettings()->withAddedDefaults();
        $this->assertEquals("default-src 'self' 'unsafe-inline' 'unsafe-eval' *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; connect-src 'self' wss://*.sugarapps.com wss://*.sugarapps.com.au wss://*.sugarapps.eu *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; img-src data: http: https: blob:; object-src 'self'; frame-ancestors 'self'; font-src 'self' data: *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net", $csp->asString());
    }

    public function testFromAdministrationSettingsWithDefaults()
    {
        \BeanFactory::setBeanClass(\Administration::class, AdministrationMock::class);
        $csp = ContentSecurityPolicy::fromAdministrationSettings()->withAddedDefaults();
        $this->assertEquals("default-src *.mashable.com 'self' 'unsafe-inline' 'unsafe-eval' *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; connect-src 'self' wss://*.sugarapps.com wss://*.sugarapps.com.au wss://*.sugarapps.eu *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; img-src data: http: https: blob:; object-src 'self'; frame-ancestors 'self'; font-src 'self' data: *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net", $csp->asString());
    }

    /**
     * @covers ::fromAdministrationSettings
     */
    public function testFromAdministrationSettings()
    {
        \BeanFactory::setBeanClass(\Administration::class, AdministrationMock::class);
        $csp = ContentSecurityPolicy::fromAdministrationSettings();
        $this->assertEquals('default-src *.mashable.com', $csp->asString());

        \BeanFactory::setBeanClass(\Administration::class, AdministrationHiddenMock::class);
        $csp = ContentSecurityPolicy::fromAdministrationSettings();
        $this->assertEquals('default-src *.mashable.com *.hiddenmashable.com', $csp->asString());
    }

    /**
     * @covers ::saveToSettings
     * @doesNotPerformAssertions
     */
    public function testSaveToSettings()
    {
        $csp = ContentSecurityPolicy::fromDirectivesList(Directive::create('default-src', '*.mashable.com'));
        $administrationMock = $this->getMockBuilder(AdministrationMock::class)
            ->onlyMethods(['saveSetting'])
            ->getMock();
        $platform = 'base';
        $administrationMock->method('saveSetting')
            ->with('csp', 'default_src', '*.mashable.com', $platform)
            ->will($this->returnValue(1));
        \BeanFactory::setBeanClass(\Administration::class, get_class($administrationMock));
        $csp->saveToSettings($platform);

        $csp = ContentSecurityPolicy::fromDirectivesList(
            Directive::create('default-src', '*.mashable.com'),
            Directive::createHidden('default-src', '*.hiddenmashable.com'),
        );
        $administrationMock = $this->getMockBuilder(AdministrationMock::class)
            ->onlyMethods(['saveSetting'])
            ->getMock();
        $platform = 'base';
        $administrationMock->method('saveSetting')
            ->with('csp', 'default_src', '*.mashable.com', $platform)
            ->will($this->returnValue(1));
        \BeanFactory::setBeanClass(\Administration::class, get_class($administrationMock));
        $csp->saveToSettings($platform);

        $administrationMock->method('saveSetting')
            ->with('csphidden', 'default_src', '*.hiddenmashable.com', $platform)
            ->will($this->returnValue(1));
        \BeanFactory::setBeanClass(\Administration::class, get_class($administrationMock));
        $csp->saveToSettings($platform);
    }
}

class AdministrationSettingsMock
{
    public $settings = [];

    public function saveSetting($category, $key, $value, $platform = '')
    {
        return 1;
    }

    public function retrieveSettings($category = false, $clean = false)
    {
        return $this;
    }
}

class AdministrationMock
{
    public $settings = [
        'csp_default_src' => '*.mashable.com',
    ];

    public function saveSetting($category, $key, $value, $platform = '')
    {
        return 1;
    }

    public function retrieveSettings($category = false, $clean = false)
    {
        return $this;
    }
}

class AdministrationHiddenMock
{
    public $settings = [
        'csp_default_src' => '*.mashable.com',
        'csphidden_default_src' => '*.hiddenmashable.com',
    ];

    public function saveSetting($category, $key, $value, $platform = '')
    {
        return 1;
    }

    public function retrieveSettings($category = false, $clean = false)
    {
        return $this;
    }
}

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
use Sugarcrm\Sugarcrm\CSP\AdministrationSettingsCSPStorage;
use Sugarcrm\Sugarcrm\CSP\ContentSecurityPolicy;
use Sugarcrm\Sugarcrm\CSP\CSPStorage;
use Sugarcrm\Sugarcrm\CSP\Directive;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\CSP\AdministrationSettingsCSPStorage
 */
class CSPStorageTest extends TestCase
{
    /** @var string|false */
    private static $savedBeanClass = false;

    public static function setUpBeforeClass(): void
    {
        self::$savedBeanClass = \BeanFactory::getBeanClass(\Administration::class);
        \BeanFactory::setBeanClass(\Administration::class, AdministrationSettingsStaticMock::class);
    }

    public static function tearDownAfterClass(): void
    {
        \BeanFactory::setBeanClass(\Administration::class, self::$savedBeanClass ?: null);
    }

    public function tearDown(): void
    {
        AdministrationSettingsStaticMock::$allSettings = [];
    }

    /**
     * @covers ::get
     */
    public function testNoDirectives()
    {
        AdministrationSettingsStaticMock::$allSettings = [];
        $storage = $this->createStorage();
        $csp = $storage->get();
        $this->assertCount(0, $csp->getDirectives());
        $this->assertCount(0, $csp->getDirectivesHidden());
    }

    /**
     * @return CSPStorage
     */
    private function createStorage(): CSPStorage
    {
        return new AdministrationSettingsCSPStorage(function (): void {
        });
    }

    /**
     * @covers ::get
     */
    public function testGetEmptyDirective()
    {
        AdministrationSettingsStaticMock::$allSettings = [
            'csp' => [
                'csp_default_src' => '',
            ],
            'csphidden' => [
                'csphidden_default_src' => '',
            ],
        ];
        $storage = $this->createStorage();
        $csp = $storage->get();
        $this->assertCount(0, $csp->getDirectives());
        $this->assertCount(0, $csp->getDirectivesHidden());
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        AdministrationSettingsStaticMock::$allSettings = [
            'csp' => [
                'csp_default_src' => '*.mashable.com',
            ],
            'csphidden' => [
                'csphidden_default_src' => '*.hiddenmashable.com',
            ],
        ];
        $storage = $this->createStorage();
        $csp = $storage->get();
        $this->assertCount(1, $csp->getDirectives());
        $this->assertEquals(
            Directive::create('default-src', '*.mashable.com'),
            $csp->getDirective('default-src')
        );
        $this->assertCount(1, $csp->getDirectivesHidden());
        $this->assertEquals(
            Directive::createHidden('default-src', '*.hiddenmashable.com'),
            $csp->getDirectiveHidden('default-src')
        );
    }

    /**
     * @covers ::save
     */
    public function testSaveNew()
    {
        $csp = ContentSecurityPolicy::fromDirectivesList(
            Directive::create('default-src', '*.mashable.com'),
            Directive::createHidden('default-src', '*.hiddenmashable.com')
        );
        $storage = $this->createStorage();
        $storage->save($csp);
        $this->assertEquals([
            'csp' => [
                'csp_default_src' => '*.mashable.com',
            ],
            'csphidden' => [
                'csphidden_default_src' => '*.hiddenmashable.com',
            ],
        ], AdministrationSettingsStaticMock::$allSettings);
    }

    /**
     * @covers ::save
     * @covers ::get
     */
    public function testSaveEditExisting()
    {
        AdministrationSettingsStaticMock::$allSettings = [
            'csp' => [
                'csp_default_src' => '*.mashable.com',
            ],
            'csphidden' => [
                'csphidden_default_src' => '*.hiddenmashable.com',
            ],
        ];
        $storage = $this->createStorage();
        $csp = $storage->get();
        $csp->appendDirective(Directive::create('default-src', '*.google.com'));
        $csp->removeDirective(Directive::createHidden('default-src', '*.hiddenmashable.com'));
        $storage->save($csp);
        $this->assertEquals([
            'csp' => [
                'csp_default_src' => '*.mashable.com *.google.com',
            ],
            'csphidden' => [
                'csphidden_default_src' => '',
            ],
        ], AdministrationSettingsStaticMock::$allSettings);
    }
}

class AdministrationSettingsStaticMock
{
    public static $allSettings = [];
    public $settings = [];

    public function saveSetting($category, $key, $value, $platform = '')
    {
        if (empty(self::$allSettings[$category])) {
            self::$allSettings[$category] = [];
        }
        self::$allSettings[$category][$category . '_' . $key] = $value;
        return 1;
    }

    public function retrieveSettings($category = false, $clean = false)
    {
        $this->settings = self::$allSettings[$category] ?? [];
        return $this;
    }
}

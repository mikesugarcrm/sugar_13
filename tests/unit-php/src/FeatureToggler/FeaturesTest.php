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

declare(strict_types=1);

namespace Sugarcrm\SugarcrmTestsUnit\FeaturesToggler;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\FeatureToggle\Features;
use Sugarcrm\Sugarcrm\FeatureToggle\Features\EnhancedModuleChecks;
use Sugarcrm\Sugarcrm\FeatureToggle\Features\StrictIncludes;
use Sugarcrm\Sugarcrm\FeatureToggle\Features\TranslateMLPCode;
use Sugarcrm\Sugarcrm\FeatureToggle\Features\HttpOnlyCookies;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\FeatureToggle\Features
 *
 */
final class FeaturesTest extends TestCase
{
    /**
     * @return array[]
     */
    public function versionDataProvider(): array
    {
        return [
            ['12.1.0', EnhancedModuleChecks::getName(), false,],
            ['13.0.0', EnhancedModuleChecks::getName(), true,],
            ['13.0.0', TranslateMLPCode::getName(), false,],
            ['14.0.0', TranslateMLPCode::getName(), false,],
            ['13.0.0', StrictIncludes::getName(), false,],
            ['14.0.0', StrictIncludes::getName(), true,],
            ['13.0.0', HttpOnlyCookies::getName(), false,],
            ['14.0.0', HttpOnlyCookies::getName(), true,],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::loadFeatures
     * @dataProvider versionDataProvider
     * @param string $version
     * @param string $feature
     * @param bool $state
     * @return void
     */
    public function testDefaults(string $version, string $feature, bool $state): void
    {
        $features = new Features($version);
        self::assertEquals($state, $features->isEnabled($feature));
    }

    /**
     * @return array[]
     */
    public function listOfEnabledFeaturesDataProvider(): array
    {
        return [
            ['12.1.0', [],],
            ['12.2.0', [EnhancedModuleChecks::getName()],],
            ['13.0.0', [EnhancedModuleChecks::getName()],],
            ['13.1.0', [EnhancedModuleChecks::getName(), StrictIncludes::getName(), HttpOnlyCookies::getName()]],
            ['14.0.0', [EnhancedModuleChecks::getName(), StrictIncludes::getName(), HttpOnlyCookies::getName()]],
        ];
    }

    /**
     * @covers ::getAllEnabled
     * @dataProvider listOfEnabledFeaturesDataProvider
     * @param string $version
     * @param array $enabled
     * @return void
     */
    public function testAllEnabled(string $version, array $enabled)
    {
        $features = new Features($version);

        $coveredFeatures = [
            EnhancedModuleChecks::getName(),
            TranslateMLPCode::getName(),
            StrictIncludes::getName(),
            HttpOnlyCookies::getName(),
        ];
        $allFeatures = array_intersect($features->getAllEnabled(), $coveredFeatures);
        sort($allFeatures);
        sort($enabled);

        self::assertEqualsCanonicalizing($enabled, $allFeatures);
    }

    public function disabledFeaturesDataProvider(): array
    {
        return [
            ['12.1.0', EnhancedModuleChecks::getName(),],
            ['13.0.0', TranslateMLPCode::getName(),],
            ['13.0.0', StrictIncludes::getName(),],
            ['13.0.0', HttpOnlyCookies::getName(),],
        ];
    }

    /**
     * @covers ::enable
     * @covers ::isEnabled
     * @covers ::ensureToggleable
     * @dataProvider disabledFeaturesDataProvider
     * @param string $version
     * @param string $feature
     * @return void
     */
    public function testEnable(string $version, string $feature): void
    {
        $features = new Features($version);
        self::assertFalse($features->isEnabled($feature));
        $features->enable($feature);
        self::assertTrue($features->isEnabled($feature));
    }

    public function enabledFeaturesDataProvider(): array
    {
        return [
            ['12.2.0', EnhancedModuleChecks::getName(),],
            ['13.1.0', StrictIncludes::getName(),],
            ['13.1.0', HttpOnlyCookies::getName(),],
        ];
    }

    /**
     * @covers ::disable
     * @covers ::isEnabled
     * @covers ::ensureToggleable
     * @dataProvider enabledFeaturesDataProvider
     * @param string $version
     * @param string $feature
     * @return void
     */
    public function testDisable(string $version, string $feature): void
    {
        $features = new Features($version);
        self::assertTrue($features->isEnabled($feature));
        $features->disable($feature);
        self::assertFalse($features->isEnabled($feature));
    }


    /**
     * @covers ::isEnabled
     * @covers ::checkName
     * @return void
     */
    public function testInvalidFeature()
    {
        self::expectException(\DomainException::class);
        $features = new Features('12.0.0');
        $features->isEnabled('Unknown');
    }

    public function untoggleableFeaturesDataProvider(): array
    {
        return [
            ['13.0.0', EnhancedModuleChecks::getName(),],
            ['14.0.0', StrictIncludes::getName(),],
            ['14.0.0', HttpOnlyCookies::getName(),],
        ];
    }

    /**
     * @dataProvider untoggleableFeaturesDataProvider
     * @param string $version
     * @param string $feature
     * @return void
     */
    public function testUntoggleableFeature(string $version, string $feature)
    {
        self::expectException(\DomainException::class);
        $features = new Features($version);
        $features->disable($feature);
    }
}

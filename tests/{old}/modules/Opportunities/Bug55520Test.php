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

require_once 'include/export_utils.php';

/**
 * Bug #55520
 * Export opportunities on windows corrupts unicode characters
 *
 * @author vromanenko@sugarcrm.com
 * @ticked 55520
 */
class Bug55520Test extends TestCase
{
    public const BOM = "\xEF\xBB\xBF";
    public const DEFAULT_EXPORT_CHARSET_PREF_NAME = 'default_export_charset';
    public const UTF8_CHARSET = 'UTF-8';
    public const NON_UTF8_CHARSET = 'ISO-8859-1';

    /**
     * @var Opportunity
     */
    protected $opportunity;

    /**
     * @var string
     */
    protected $defaultExportCharset;

    /**
     * @var User
     */
    protected $currentUser;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->currentUser = $GLOBALS['current_user'];
        $this->defaultExportCharset = $this->currentUser->getPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME);

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
    }

    /**
     * Ensure that exported data starts with BOM
     *
     * @group 55520
     */
    public function testExportStringIncludesBOM()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, self::UTF8_CHARSET);
        $export = export('Opportunities', [$this->opportunity->id]);
        $this->assertStringStartsWith(self::BOM, $export);
    }

    /**
     * Ensure that exported data does not start with BOM if the export character set is other than utf-8
     *
     * @group 55520
     */
    public function testExportStringNotIncludesBOM()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, self::NON_UTF8_CHARSET);
        $export = export('Opportunities', [$this->opportunity->id]);
        $this->assertStringStartsNotWith(self::BOM, $export);
    }

    protected function tearDown(): void
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, $this->defaultExportCharset);
        SugarTestHelper::tearDown();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
    }
}

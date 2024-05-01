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

require_once 'modules/Calls/metadata/additionalDetails.php';

/**
 * @ticket 22882
 */
class Bug22882Test extends AppListStringsTest
{
    public function testMultiLanguagesDeletedValue()
    {
        $this->loadFilesDeletedValue();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertTrue($this->isEqual($resultfr, $resulten));
    }

    public function testMultiLanguagesDeletedValueFrOnly()
    {
        $this->loadFilesDeletedValueFrOnly();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr), count($resulten), 'The 2 drop down list have the same size.');
    }

    public function testMultiLanguagesDeletedValueEnOnly()
    {
        $this->loadFilesDeletedValueEnOnly();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr), count($resulten));
        $this->assertFalse(in_array('Customer', $resulten));
        $this->assertTrue(in_array('Customer', $resultfr));
    }

    public function testMultiLanguagesAddedValue()
    {
        $this->loadFilesAddedValueEn();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = array_keys($resultfr['account_type_dom']);
        $resulten = array_keys($resulten['account_type_dom']);
        $this->assertNotEquals(count($resultfr), count($resulten), 'The 2 drop down list have the same size.');
    }


    /**
     * Bug 57431 : the custom default language overrides the current language
     */
    public function testMultiLanguagesCustomValueEnOnly()
    {
        $this->loadFilesAddedCustomValueEnOnly();
        $resultfr = return_app_list_strings_language('fr_test');
        $resulten = return_app_list_strings_language('en_us');
        $resultfr = $resultfr['account_type_dom']['Analyst'];
        $resulten = $resulten['account_type_dom']['Analyst'];
        $this->assertNotEquals($resultfr, $resulten, 'The custom default language overrides french lang.');
    }


    public function loadFilesDeletedValue()
    {
        $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyste', Line deleted
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyst', Line deleted
  'Competitor' => 'Competitor',
  'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }

    public function loadFilesDeletedValueFrOnly()
    {
        $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyste', Line deleted
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyst',
  'Competitor' => 'Competitor',
  'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }

    public function loadFilesDeletedValueEnOnly()
    {
        $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyste',
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyst',
  'Competitor' => 'Competitor',
  //'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }

    public function loadFilesAddedValueEn()
    {
        $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']=array (
  'Analyst' => 'Analyste',
  'Competitor' => 'Concurrent',
  'Customer' => 'Client',
  'Integrator' => 'Intégrateur',
  'Investor' => 'Investisseur',
  'Partner' => 'Partenaire',
  'Press' => 'Presse',
  'Prospect' => 'Prospect',
  'Other' => 'Autre',
  '' => '',
);
FRFR;
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  'Extra' => 'Extra',
  'Analyst' => 'Analyst',
  'Competitor' => 'Competitor',
  'Customer' => 'Customer',
  'Integrator' => 'Integrator',
  'Investor' => 'Investor',
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'Other' => 'Other',
  '' => '',
);
ENEN;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }


    public function loadFilesAddedCustomValueEnOnly()
    {
        $file_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']['Analyst'] = 'Test';
ENEN;

        $file_fr = <<<FRFR
<?php
\$app_list_strings['account_type_dom']['Analyst'] = 'Test (French)';
FRFR;
        $this->safe_create('include/language/fr_test.lang.php', file_get_contents('include/language/en_us.lang.php'));
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_fr);
        $this->safe_create('custom/include/language/en_us.lang.php', $file_en);
    }
}

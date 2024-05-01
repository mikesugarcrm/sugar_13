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
 * See Bug22882Test.php for other tests on app_list_strings_language
 */
class AppListStringsTest extends TestCase
{
    private $temp_files = [];

    protected $created_files = [];
    private $configBackup = [];

    protected function setUp(): void
    {
        \SugarConfig::getInstance()->clearCache();
        // Backup current language settings so manipulation can be tested
        $this->configBackup['languages'] = $GLOBALS['sugar_config']['languages'];
        $GLOBALS['sugar_config']['languages'] = [
            'en_us' => 'English (US)',
            'fr_test' => 'Test Lang',
        ];
        if (!is_dir('custom/include/language')) {
            @mkdir('custom/include/language', 0777, true);
        }

        sugar_cache_clear('app_list_strings.en_us');
        sugar_cache_clear('app_list_strings.fr_test');
    }

    protected function tearDown(): void
    {
        if (!empty($this->created_files)) {
            $this->created_files = [];
            $this->restore_or_delete('include/language/fr_test.lang.php');
            $this->restore_or_delete('custom/include/language/en_us.lang.php');
            $this->restore_or_delete('custom/include/language/fr_test.lang.php');
        }
        $GLOBALS['sugar_config']['languages'] = $this->configBackup['languages'];
        \SugarConfig::getInstance()->clearCache();
    }

    public function testAppListStringsLanguage()
    {
        //Here we load french language
        $this->loadFrench();
        //Here we delete some items in account_type_dom
        $this->loadCustomEnglish();
        //Here we delete some items in case_type_dom
        $this->loadCustomFrench();

        $result = return_app_list_strings_language('fr_test');
        $expected = [
            'account_type_dom' => [
                'Partner' => 'Partenaire',
                'Press' => 'Presse',
                'Prospect' => 'Prospect',
                'School' => 'School',
                'Other' => 'Autre',
            ],
            'case_type_dom' => [
                'Product' => 'Produit',
                'User' => 'Utilisateur',
                '' => '',
            ],
        ];
        $this->assertTrue(
            $this->isEqual($expected['account_type_dom'], $result['account_type_dom']),
            'The english custom list string is not correctly loaded.'
        );
        $this->assertTrue(
            $this->isEqual($expected['case_type_dom'], $result['case_type_dom']),
            'The french custom list string is not correctly loaded.'
        );
    }

    public function testIsEqual()
    {
        $arr1 = [
            'a' => [
                'aa' => [
                    'aaa',
                    'aab',
                ],
                'ab' => [
                    'aba',
                    'abb',
                ],
            ],
            'b' => [
                'ba' => [
                    'baa',
                    'bab',
                ],
                'bb' => [
                    'bba',
                    'bbb',
                ],
            ],
        ];
        $arr2 = [
            'a' => [
                'aa' => [
                    'aaa',
                    'aab',
                ],
                'ab' => [
                    'aba',
                    'abb',
                ],
            ],
            'b' => [
                'ba' => [
                    'baa',
                    'bab',
                ],
                'bb' => [
                    'bbb', // CHANGE ORDER
                    'bba',
                ],
            ],
        ];

        $this->assertFalse(
            $this->isEqual($arr1, $arr2),
            'isEqual does not make the job.'
        );
        $this->assertFalse(
            $this->isEqual($arr2, $arr1),
            'isEqual does not make the job.'
        );
    }

    /**
     * Creates a file saving the previous version if exists
     * @param string $filename
     * @param string $contents
     */
    protected function safe_create($filename, $contents)
    {
        if (file_exists($filename)) {
            $this->temp_files[$filename] = file_get_contents($filename);
        }
        $this->created_files[] = $filename;
        file_put_contents($filename, $contents);
    }

    /**
     * Deletes a file or restore the previous version if exists
     * @param string $filename
     * @param string $contents
     */
    protected function restore_or_delete($filename)
    {
        if (isset($this->temp_files[$filename]) && !empty($this->temp_files[$filename])) {
            file_put_contents($filename, $this->temp_files[$filename]);
            $this->temp_files[$filename] = '';
        } elseif (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * TRUE if $gimp and $dom have the same key/value pairs in the same order and of the same types.
     * @param $gimp
     * @param $dom
     * @return bool
     */
    protected function isEqual($gimp, $dom)
    {
        return $gimp === $dom;
    }

    private function loadFrench()
    {
        $file_fr = <<<FRFR
<?php
\$app_list_strings=array(
    'account_type_dom'=> array (
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
    ),
);
FRFR;
        $this->safe_create('include/language/fr_test.lang.php', $file_fr);
    }

    private function loadCustomEnglish()
    {
        $file_custom_en = <<<ENEN
<?php
\$app_list_strings['account_type_dom']=array (
  //'Analyst' => 'Analyst', Line deleted
  //'Competitor' => 'Competitor', Line deleted
  //'Customer' => 'Customer', Line deleted
  //'Integrator' => 'Integrator', Line deleted
  //'Investor' => 'Investor', Line deleted
  'Partner' => 'Partner',
  'Press' => 'Press',
  'Prospect' => 'Prospect',
  'School' => 'School', // Line added
  'Other' => 'Other',
  //'' => '', Line deleted
);
ENEN;
        $this->safe_create('custom/include/language/en_us.lang.php', $file_custom_en);
    }

    private function loadCustomFrench()
    {
        $file_custom_fr = <<<FRFR
<?php
\$app_list_strings['case_type_dom']=array (
//'Administration' => 'Administration', Line deleted
'Product' => 'Produit',
'User' => 'Utilisateur',
'' => '',
);
FRFR;
        $this->safe_create('custom/include/language/fr_test.lang.php', $file_custom_fr);
    }
}

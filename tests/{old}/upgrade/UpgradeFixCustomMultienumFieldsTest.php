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

require_once 'upgrade/scripts/post/7_FixCustomMultienumFields.php';

class UpgradeFixCustomMultienumFieldsTest extends UpgradeTestCase
{
    /**
     * @var SugarUpgradeFixCustomMultienumFields
     */
    protected $script;

    /**
     * @var string
     */
    public $metaFolder = 'custom/Extension/modules/Accounts/Ext/Vardefs/';

    /**
     * @var string
     */
    public $metaFileName = 'sugarfield_test_multienum_field.php';

    protected function setUp(): void
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $customMultienumMeta = <<<EOQ
<?php
\$dictionary['Accounts']['fields']['test_multienum_field']['default'] = '^^';
\$dictionary['Accounts']['fields']['test_multienum_field']['type'] = 'multienum';
\$dictionary['Accounts']['fields']['test_multienum_field']['dependency'] = '';
EOQ;
        mkdir_recursive($this->metaFolder);
        file_put_contents($this->metaFolder . $this->metaFileName, $customMultienumMeta);

        $this->upgrader->setVersions(6.7, 'ent', 7.5, 'ent');

        $this->script = $this->getMockBuilder('SugarUpgradeFixCustomMultienumFields')
            ->setConstructorArgs([$this->upgrader])
            ->setMethods(['getCustomFieldFiles'])
            ->getMock();

        $this->script->expects($this->any())->method('getCustomFieldFiles')
            ->will(
                $this->returnValue(
                    [
                        $this->metaFolder . $this->metaFileName,
                    ]
                )
            );
    }

    protected function tearDown(): void
    {
        rmdir_recursive($this->metaFolder);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testMultienumFieldContainsIsMultiSelect()
    {
        $this->script->run();

        $dictionary = [];
        require $this->metaFolder . $this->metaFileName;

        $this->assertTrue($dictionary['Accounts']['fields']['test_multienum_field']['isMultiSelect']);
    }
}

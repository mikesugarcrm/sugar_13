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

use Regression\Helpers\MLPBuilder;
use Regression\Severity;
use Regression\SugarCRMAssessment;

class BypassSaveCustomAppListStringsContentsAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return 'RCE in manifest.php';
    }

    public function run(): void
    {
        $this
            ->login('admin', 'asdf')
            ->bwcLogin();

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'manifest_shell'))
            ->addFile(
                'custom.php',
                <<<'PHP'
                <?php
                require 'modules/Administration/Common.php';
                save_custom_app_list_strings_contents(base64_decode("YWJyYWhhY2suZnQuc3VnYXJjcm0udmlhLmgxIDw/cGhwIHBocGluZm8oKTsgPz4="), 'pocday');
                PHP,
                'custom.php'
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->assumeSubstring('Code attempted to call denylisted function "save_custom_app_list_strings_contents"', $escaped)
            ->checkAssumptions('Manifest shell scripting was found.', !$escaped);
    }
}

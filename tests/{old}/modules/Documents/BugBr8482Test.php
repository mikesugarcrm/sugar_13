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

class BugBr8482Test extends TestCase
{
    /**
     * @covers DocumentsApiHelper::getDocumentRevisionId
     */
    public function testTableIsCorrectlyQuoted()
    {
        $helper = new DocumentsApiHelper(new DocumentsServiceBr8482Mock());
        $result = SugarTestReflection::callProtectedMethod($helper, 'getDocumentRevisionId', ['documents', 'some_random_id']);
        $this->assertEquals(false, $result);
    }
}

class DocumentsServiceBr8482Mock extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}

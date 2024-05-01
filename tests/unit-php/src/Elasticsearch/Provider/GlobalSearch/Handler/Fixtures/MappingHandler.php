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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures;

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MappingHandlerInterface;

/**
 * MappingHandler fixture
 */
class MappingHandler extends BaseHandler implements MappingHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'MappingHandler';
    }

    /**
     * {@inheritDoc}
     */
    public function buildMapping(Mapping $mapping, $field, array $defs)
    {
    }
}

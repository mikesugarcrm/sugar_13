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

use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\AnalysisHandlerInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MappingHandlerInterface;

/**
 * AnalysisMappingHandler fixture
 */
class AnalysisMappingHandler extends BaseHandler implements AnalysisHandlerInterface, MappingHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'AnalysisMappingHandler';
    }

    /**
     * {@inheritDoc}
     */
    public function buildAnalysis(AnalysisBuilder $builder)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function buildMapping(Mapping $mapping, $field, array $defs)
    {
    }
}

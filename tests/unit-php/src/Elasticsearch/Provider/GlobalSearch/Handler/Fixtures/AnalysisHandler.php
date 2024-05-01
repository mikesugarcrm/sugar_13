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
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\AnalysisHandlerInterface;

/**
 * AnalysisHandler fixture
 */
class AnalysisHandler extends BaseHandler implements AnalysisHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'AnalysisHandler';
    }

    /**
     * {@inheritDoc}
     */
    public function buildAnalysis(AnalysisBuilder $builder)
    {
    }
}

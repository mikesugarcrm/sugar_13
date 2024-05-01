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

namespace Sugarcrm\SugarcrmTestsUnit\src\DocumentMerge\Client\Adapter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\AdapterFactory;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\ConvertDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\MergeDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\MultiConvertDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\MultiMergeDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\LabelsMergeDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\ExcelMergeDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\ExcelMergeConvertDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\PresentationConvertDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\PresentationDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Adapter\Adapters\LabelsMergeConvertDataAdapter;
use Sugarcrm\Sugarcrm\DocumentMerge\Client\Constants\MergeType;

class AdapterFactoryTest extends TestCase
{
    /**
     * The type of the merge
     *
     * @var string
     */
    private $mergeType;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function providerGetInstance(): array
    {
        return [
            [
                'args' => [
                    ConvertDataAdapter::class => MergeType::Convert,
                    MergeDataAdapter::class => MergeType::Merge,
                    MultiConvertDataAdapter::class => MergeType::MultiConvert,
                    MultiMergeDataAdapter::class => MergeType::MultiMerge,
                    ExcelMergeDataAdapter::class => MergeType::Spreadsheet,
                    LabelsMergeDataAdapter::class => MergeType::LabelsGenerate,
                    ExcelMergeConvertDataAdapter::class => MergeType::SpreadsheetConvert,
                    PresentationConvertDataAdapter::class => MergeType::PresentationConvert,
                    PresentationDataAdapter::class => MergeType::Presentation,
                    LabelsMergeConvertDataAdapter::class => MergeType::LabelsGenerateConvert,
                ],
            ],
        ];
    }

    /**
     * it should test the factory getDataAdapterInstance function
     *
     * @dataProvider providerGetInstance
     *
     * @return void
     */
    public function testGetInstance(array $args): void
    {
        foreach ($args as $class => $type) {
            $instance = AdapterFactory::getDataAdapterInstance(['mergeType' => $type]);
            $this->assertInstanceOf($class, $instance);
        }
    }
}

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

namespace Sugarcrm\SugarcrmTests\Denormalization\Relate\FunctionalityCases\One2Many;

use BeanFactory;
use DBManagerFactory;
use LogicHook;
use PHPUnit\Framework\TestCase;
use SugarBean;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Process;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Process\Entity;
use SugarTestHelper;

abstract class AbstractFunctionalityTest extends TestCase
{
    protected static $options = [
        'primary_module' => '-module_name-',
        'field_id' => '-field_id-',
        'field_name' => '-field_name-',
        'relate_field_name' => '-relate_field_name-',
        'primary_link_name' => '-link_name-',
    ];

    /** @var SugarBean */
    private static $primaryBean;

    /** @var SugarBean */
    private static $linkedBean;

    private const TMP_TABLE = 'denorm_tmp';

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        self::$primaryBean = BeanFactory::getBean(static::$options['primary_module']);
        $processEntity = new Entity(self::$primaryBean, static::$options['field_name']);
        $process = new Process();
        $process->turnOnDenormalizationWithoutCopying($processEntity);

        SugarTestHelper::setUp('current_user');

        // reload to access to denormalized field defs
        SugarTestHelper::setUp('dictionary');

        LogicHook::refreshHooks();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        static::removeCreatedBeans();
        SugarTestHelper::tearDown();

        $processEntity = new Entity(self::$primaryBean, static::$options['field_name']);
        $process = new Process();
        $process->normalize($processEntity);
    }

    protected function setUp(): void
    {
        self::$linkedBean = $this->createLinkedBean();
        $primaryBean = $this->createPrimaryBean(self::$linkedBean);
        self::$primaryBean = $this->reloadBean($primaryBean);
    }

    public function testBeanUpdateHandler()
    {
        $testName = 'test1';
        self::$primaryBean->{static::$options['field_name']} = $testName;
        self::$primaryBean->save();

        $this->assertEquals($testName, self::$primaryBean->{$this->getDenormFieldName()});
        $primaryBean = $this->reloadBean(self::$primaryBean);
        $this->assertEquals($testName, $primaryBean->{$this->getDenormFieldName()});
    }

    public function testLinkChangeHandler()
    {
        $linkedBean = $this->createLinkedBean();
        self::$primaryBean->{static::$options['field_id']} = $linkedBean->id;
        self::$primaryBean->save();
        $primaryBean = $this->reloadBean(self::$primaryBean);

        $this->assertEquals($linkedBean->{static::$options['relate_field_name']}, $primaryBean->{$this->getDenormFieldName()});
    }

    public function testBeanLinkUpdateHandler()
    {
        $testName = 'test2';

        self::$primaryBean->load_relationship(static::$options['primary_link_name']);
        $linkedBeans = self::$primaryBean->{static::$options['primary_link_name']}->getBeans();
        $this->assertNotEmpty($linkedBeans);
        $linkedBean = end($linkedBeans);
        $this->assertNotEmpty($linkedBean);
        $this->assertEquals(
            $linkedBean->{static::$options['relate_field_name']},
            self::$primaryBean->{$this->getDenormFieldName()}
        );

        $linkedBean->{static::$options['relate_field_name']} = $testName;

        $linkedBean->save();

        // reload to ensure that changes were saved
        /** @var SugarBean $primaryBean */
        $primaryBean = $this->reloadBean(self::$primaryBean);

        $this->assertEquals($testName, $linkedBean->{static::$options['relate_field_name']});
        $this->assertEquals($testName, $primaryBean->{$this->getDenormFieldName()});
    }

    public function testTmpTableUpdateDuringSynchronization()
    {
        // case: we still migrating from TMP table
        $processEntity = new Entity(self::$primaryBean, static::$options['field_name']);
        $process = new Process();
        $process->denormalize($processEntity);
        $process->alterTable($processEntity);
        $process->prepareForCopy($processEntity);
        // Created case: we still migrating from TMP table

        $testName = 'test3';
        self::$primaryBean->{static::$options['field_name']} = $testName;
        self::$primaryBean->save();
        $primaryBean = $this->reloadBean(self::$primaryBean);

        $this->assertEquals($testName, $primaryBean->{$this->getDenormFieldName()});

        // in TMP table we should have an updated value
        $builder = DBManagerFactory::getConnection()->createQueryBuilder();
        $value = $builder->select('value')
            ->from(self::TMP_TABLE)
            ->where('target_id = :target_id')
            ->setParameter('target_id', $primaryBean->id)
            ->execute()
            ->fetchOne();

        $this->assertEquals($testName, $value);
    }

    private function getDenormFieldName()
    {
        return 'denorm_' . static::$options['field_name'];
    }

    private function reloadBean(SugarBean $bean): SugarBean
    {
        return BeanFactory::getBean($bean->getModuleName(), $bean->id, ['use_cache' => false]);
    }

    abstract protected function createPrimaryBean(?SugarBean $linkedBean): SugarBean;

    abstract protected function createLinkedBean(): SugarBean;

    abstract protected static function removeCreatedBeans(): void;
}

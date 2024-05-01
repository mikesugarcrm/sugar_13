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

namespace Sugarcrm\SugarcrmTests\Denormalization\Relate\FunctionalityCases\Many2Many;

use BeanFactory;
use DBManagerFactory;
use Link2;
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
        'primary_link_name' => '-primary_link_name-',
        'relate_link_name' => '-relate_link_name-',
        'field_name' => '-field_name-',
        'relate_field_name' => '-field_in_linked_bean-',
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
        // reload to access to denormalized field defs
        SugarTestHelper::setUp('dictionary');

        self::$primaryBean = BeanFactory::getBean(static::$options['primary_module']);
        $processEntity = new Entity(self::$primaryBean, static::$options['field_name']);
        $process = new Process();
        $process->turnOnDenormalizationWithoutCopying($processEntity);

        SugarTestHelper::setUp('current_user');


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

        $db = DBManagerFactory::getInstance();
        $indexName = $db->getValidDBName(
            'idx_' . static::$options['primary_link_name'] . '_denorm_account_name'
        );
        $sql = $db->dropIndexes(
            self::$primaryBean->getTableName(),
            [
                [
                    'name' => $indexName,
                    'type' => 'index',
                    'fields' => [],
                ],
            ],
            false
        );
        $db->query($sql);
        $sql = $db->dropColumnSQL(
            self::$primaryBean->getTableName(),
            [
                'name' => 'denorm_account_name',
                'type' => 'varchar',
            ]
        );
        $db->query($sql);
    }

    protected function setUp(): void
    {
        self::$linkedBean = $this->createLinkedBean();
        self::$primaryBean = $this->createPrimaryBean(self::$linkedBean);
        self::$primaryBean = $this->reloadBean(self::$primaryBean);
    }

    public function testIndexCreated()
    {
        $db = DBManagerFactory::getInstance();
        $indices = $db->get_indices(self::$primaryBean->getTableName());
        $indexName = $db->getValidDBName(
            'idx_' . static::$options['primary_link_name'] . '_denorm_account_name'
        );
        $this->assertArrayHasKey($indexName, $indices);

        $this->assertArrayHasKey('denorm_account_name', array_flip($indices[$indexName]['fields']));
    }

    public function testBeanUpdateHandler()
    {
        $testName = 'test1';
        self::$primaryBean->{static::$options['field_name']} = $testName;
        self::$primaryBean->save();

        $this->assertEquals($testName, self::$primaryBean->{$this->getDenormFieldName()});
        self::$primaryBean = $this->reloadBean(self::$primaryBean);
        $this->assertEquals($testName, self::$primaryBean->{$this->getDenormFieldName()});

        return self::$primaryBean;
    }

    /**
     * @return SugarBean
     */
    public function testBeanLinkUpdateHandler()
    {
        $testName = 'test2';

        self::$primaryBean->load_relationship(static::$options['relate_link_name']);

        $linkedBeans = self::$primaryBean->{static::$options['relate_link_name']}->getBeans();
        $this->assertNotEmpty($linkedBeans);
        $linkedBean = end($linkedBeans);
        $this->assertNotEmpty($linkedBean);
        $this->assertEquals(
            $linkedBean->{static::$options['relate_field_name']},
            self::$primaryBean->{$this->getDenormFieldName()}
        );

        $linkedBean->{static::$options['relate_field_name']} = $testName;
        $linkedBean->save();

        $this->assertEquals($testName, $linkedBean->{static::$options['relate_field_name']});

        // reload to ensure that changes were saved
        $primaryBean = $this->reloadBean(self::$primaryBean);

        $this->assertEquals($testName, $primaryBean->{$this->getDenormFieldName()});

        return $primaryBean;
    }

    /**
     * @depends testBeanLinkUpdateHandler
     *
     * @param SugarBean $primaryBean
     * @return SugarBean
     */
    public function testRelationshipRemoveHandler(SugarBean $primaryBean)
    {
        $primaryBean->{static::$options['relate_link_name']}->resetLoaded();

        $linkedBeans = $primaryBean->{static::$options['relate_link_name']}->getBeans();
        /** @var Link2 $link */
        $link = $primaryBean->{static::$options['relate_link_name']};
        $this->assertNotEmpty($linkedBeans);
        $linkedBean = end($linkedBeans);
        $this->assertNotEmpty($linkedBean);

        $this->assertNotEmpty($primaryBean->{$this->getDenormFieldName()});

        $link->delete(null, $linkedBean);

        $this->assertEmpty($primaryBean->{$this->getDenormFieldName()});

        // reload to ensure that changes saved
        $primaryBean = $this->reloadBean($primaryBean);

        $this->assertEmpty($primaryBean->{$this->getDenormFieldName()});

        return $primaryBean;
    }

    /**
     * @depends testRelationshipRemoveHandler
     */
    public function testRelationshipAddHandler(SugarBean $primaryBean)
    {
        $primaryBean->{static::$options['relate_link_name']}->resetLoaded();

        $linkedBeans = $primaryBean->{static::$options['relate_link_name']}->getBeans();

        /** @var Link2 $link */
        $link = $primaryBean->{static::$options['relate_link_name']};

        $this->assertEmpty($linkedBeans);

        $linkedBean = $this->createLinkedBean();
        $link->add($linkedBean);

        // reload to ensure that changes saved
        $primaryBean = $this->reloadBean($primaryBean);

        $this->assertEquals($linkedBean->{static::$options['relate_field_name']}, $primaryBean->{$this->getDenormFieldName()});

        return $primaryBean;
    }

    /**
     * @depends testRelationshipAddHandler
     */
    public function testRelationshipModification(SugarBean $primaryBean)
    {
        $primaryBean->{static::$options['relate_link_name']}->resetLoaded();

        $linkedBeans = $primaryBean->{static::$options['relate_link_name']}->getBeans();
        $this->assertNotEmpty($linkedBeans);
        $linkedBean = end($linkedBeans);
        $this->assertNotEmpty($linkedBean);

        $newLinkedBean = $this->createLinkedBean();
        $idName = $primaryBean->getFieldDefinition(static::$options['field_name'])['id_name'];

        // direct link ID modification should correctly update denormalized field
        $primaryBean->$idName = $newLinkedBean->id;
        $primaryBean->save();

        // reload to ensure that changes saved
        $primaryBean = $this->reloadBean($primaryBean);

        $this->assertEquals($newLinkedBean->{static::$options['relate_field_name']}, $primaryBean->{$this->getDenormFieldName()});

        return $primaryBean;
    }

    /**
     * @depends testRelationshipModification
     */
    public function testHookHandlerAfterTargetDelete(SugarBean $primaryBean)
    {
        $primaryBean->{static::$options['relate_link_name']}->resetLoaded();

        $linkedBeans = $primaryBean->{static::$options['relate_link_name']}->getBeans();
        $this->assertNotEmpty($linkedBeans);
        $linkedBean = end($linkedBeans);
        $this->assertNotEmpty($linkedBean);

        $linkedBean->load_relationship(static::$options['primary_link_name']);
        /** @var Link2 $link */
        $link = $linkedBean->{static::$options['primary_link_name']};

        $this->assertNotEmpty($primaryBean->{$this->getDenormFieldName()});

        $link->delete(null, $primaryBean);

        $this->assertEmpty($primaryBean->{$this->getDenormFieldName()});
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

    protected function getDenormFieldName()
    {
        return 'denorm_' . static::$options['field_name'];
    }

    protected function reloadBean(SugarBean $bean): SugarBean
    {
        return BeanFactory::getBean($bean->getModuleName(), $bean->id, ['use_cache' => false]);
    }

    abstract protected function createPrimaryBean(?SugarBean $linkedBean): SugarBean;

    abstract protected function createLinkedBean(): SugarBean;

    abstract protected static function removeCreatedBeans(): void;
}

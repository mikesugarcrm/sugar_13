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

/**
 * Bug #53944
 *
 * Product Catalog | One-to-One Relationship with Accounts to Product Catalog does not work properly
 * @ticket 53944
 * @author imatsiushyna@sugarcrm.com
 */
class Bug53944Test extends TestCase
{
    /**
     * @var string
     */
    public $lhs_module = null;

    /**
     * @var string
     */
    public $rhs_module = null;

    /**
     * @var DeployedRelationships
     */
    protected $relationships = null;

    /**
     * @var OneToManyRelationship
     */
    protected $relationship = null;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var ProductTemplate
     */
    private $pt;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ProductTemplates']);

        //Adding relationship between module Accounts and new module
        $this->lhs_module = 'Accounts';
        $this->rhs_module = 'ProductTemplates';

        $this->relationships = new DeployedRelationships($this->lhs_module);
        $definition = [
            'lhs_module' => $this->lhs_module,
            'lhs_label' => $this->lhs_module,
            'relationship_type' => 'one-to-one',
            'rhs_module' => $this->rhs_module,
            'rhs_label' => $this->rhs_module,
            'rhs_subpanel' => 'default',
        ];
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', [
            $this->lhs_module,
            $this->rhs_module,
        ]);
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        if ($this->pt) {
            $this->pt->mark_deleted($this->pt->id);
        }

        //Removing created relationship
        $this->relationships = new DeployedRelationships($this->lhs_module);
        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();

        SugarTestHelper::tearDown();
    }

    /**
     * @large
     */
    public function testRelationOneToOne()
    {
        //Creating new Account
        $this->account = SugarTestAccountUtilities::createAccount();

        $_REQUEST['relate_to'] = $this->rhs_module;

        //Creating new ProductTemplate
        $this->pt = new ProductTemplate();
        $this->pt->name = 'Bug53944ProductTemplates' . time();
        $rel_name = $this->relationship->getName();
        $ida = $this->pt->field_defs[$rel_name]['id_name'];
        $this->pt->$ida = $this->account->id;
        $this->pt->save();

        $this->pt->load_relationship($rel_name);
        $actual = $this->pt->$rel_name->getBeans();

        $this->assertArrayHasKey($this->account->id, $actual, 'Relationship was not created');
    }
}

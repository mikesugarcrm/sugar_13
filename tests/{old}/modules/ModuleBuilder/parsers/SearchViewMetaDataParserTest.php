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

class SearchViewMetaDataParserTest extends TestCase
{
    protected function setUp(): void
    {
        //echo "Setup";
    }

    protected function tearDown(): void
    {
        //echo "TearDown";
    }

    /**
     * Bug 40530 returned faulty search results when a assigned_to relate field was used on the basic search
     * The fix is making the widgets consistent between Basic and Advanced search when there is no definition
     * for basic search. This was implemented in getOriginalViewDefs and that is what is being tested here.
     */
    public function test_Bug40530_getOriginalViewDefs()
    {
        //echo "begin test";
        // NOTE This is sample data from the live application used for test verification purposes
        $orgViewDefs = [
            'templateMeta' => [
                'maxColumns' => '3',
                'widths' => [
                    'label' => '10',
                    'field' => '30',
                ],
            ],
            'layout' => [
                'basic_search' => [
                    'name' => [
                        'name' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'current_user_only' => [
                        'name' => 'current_user_only',
                        'label' => 'LBL_CURRENT_USER_FILTER',
                        'type' => 'bool',
                        'default' => true,
                        'width' => '10%',
                    ],
                    0 => [
                        'name' => 'favorites_only',
                        'label' => 'LBL_FAVORITES_FILTER',
                        'type' => 'bool',
                    ],
                ],
                'advanced_search' => [
                    'name' => [
                        'name' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'website' => [
                        'name' => 'website',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'phone' => [
                        'name' => 'phone',
                        'label' => 'LBL_ANY_PHONE',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'email' => [
                        'name' => 'email',
                        'label' => 'LBL_ANY_EMAIL',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'address_street' => [
                        'name' => 'address_street',
                        'label' => 'LBL_ANY_ADDRESS',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'address_city' => [
                        'name' => 'address_city',
                        'label' => 'LBL_CITY',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'address_state' => [
                        'name' => 'address_state',
                        'label' => 'LBL_STATE',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'address_postalcode' => [
                        'name' => 'address_postalcode',
                        'label' => 'LBL_POSTAL_CODE',
                        'type' => 'name',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'billing_address_country' => [
                        'name' => 'billing_address_country',
                        'label' => 'LBL_COUNTRY',
                        'type' => 'name',
                        'options' => 'countries_dom',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'account_type' => [
                        'name' => 'account_type',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'industry' => [
                        'name' => 'industry',
                        'default' => true,
                        'width' => '10%',
                    ],
                    'assigned_user_id' => [
                        'name' => 'assigned_user_id',
                        'type' => 'enum',
                        'label' => 'LBL_ASSIGNED_TO',
                        'function' => [
                            'name' => 'get_user_array',
                            'params' => [
                                0 => false,
                            ],
                        ],
                        'default' => true,
                        'width' => '10%',
                    ],
                    0 => [
                        'name' => 'favorites_only',
                        'label' => 'LBL_FAVORITES_FILTER',
                        'type' => 'bool',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'name' => [
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ],
            'website' => [
                'name' => 'website',
                'default' => true,
                'width' => '10%',
            ],
            'phone' => [
                'name' => 'phone',
                'label' => 'LBL_ANY_PHONE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ],
            'email' => [
                'name' => 'email',
                'label' => 'LBL_ANY_EMAIL',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ],
            'address_street' => [
                'name' => 'address_street',
                'label' => 'LBL_ANY_ADDRESS',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ],
            'address_city' => [
                'name' => 'address_city',
                'label' => 'LBL_CITY',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ],
            'address_state' => [
                'name' => 'address_state',
                'label' => 'LBL_STATE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ],
            'address_postalcode' => [
                'name' => 'address_postalcode',
                'label' => 'LBL_POSTAL_CODE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ],
            'billing_address_country' => [
                'name' => 'billing_address_country',
                'label' => 'LBL_COUNTRY',
                'type' => 'name',
                'options' => 'countries_dom',
                'default' => true,
                'width' => '10%',
            ],
            'account_type' => [
                'name' => 'account_type',
                'default' => true,
                'width' => '10%',
            ],
            'industry' => [
                'name' => 'industry',
                'default' => true,
                'width' => '10%',
            ],
            'assigned_user_id' => [
                'name' => 'assigned_user_id',
                'type' => 'enum',
                'label' => 'LBL_ASSIGNED_TO',
                'function' => [
                    'name' => 'get_user_array',
                    'params' => [
                        0 => false,
                    ],
                ],
                'default' => true,
                'width' => '10%',
            ],
            'favorites_only' => [
                'name' => 'favorites_only',
                'label' => 'LBL_FAVORITES_FILTER',
                'type' => 'bool',
            ],
            'current_user_only' => [
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'default' => true,
                'width' => '10%',
            ],
        ];

        // We use a derived class to aid in stubbing out test properties and functions
        $parser = new SearchViewMetaDataParserTestDerivative('basic_search');

        // Creating a mock object for the DeployedMetaDataImplementation

        $impl = $this->getMockBuilder('DeployedMetaDataImplementation')
            ->setMethods(['getOriginalViewdefs'])
            ->setMockClassName('DeployedMetaDataImplementation_Mock')
            ->disableOriginalConstructor()
            ->getMock();

        // Making the getOriginalViewdefs function return the test viewdefs and verify that it is being called once
        $impl->expects($this->once())
            ->method('getOriginalViewdefs')
            ->will($this->returnValue($orgViewDefs));

        // Replace the protected implementation with our Mock object
        $parser->setImpl($impl);

        // Execute the method under test
        $result = $parser->getOriginalViewDefs();

        // Verify that the returned result matches our expectations
        $this->assertEquals($result, $expectedResult);

        //echo "Done";
    }
}

/**
 * Using derived helper class from SearchViewMetaDataParser to avoid having to fully
 * initialize the whole class and to give us the flexibility to replace the
 * Deploy/Undeploy MetaDataImplementation
 */
class SearchViewMetaDataParserTestDerivative extends SearchViewMetaDataParser
{
    public function __construct($layout)
    {
        $this->_searchLayout = $layout;
    }

    public function setImpl($impl)
    {
        $this->implementation = $impl;
    }
}

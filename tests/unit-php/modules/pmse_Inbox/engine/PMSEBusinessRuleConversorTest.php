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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

use PHPUnit\Framework\TestCase;
use PMSEBusinessRuleConversor;
use stdClass;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \PMSEBusinessRuleConversor
 */
class PMSEBusinessRuleConversorTest extends TestCase
{
    /**
     * @covers ::addValueToTransformedToken
     * @dataProvider addValueToTransformedTokenDataProvider
     * @test
     * @param stdClass $criteriaTokenInput Input param for the function which is mutated into the result.
     * @param stdClass $businessRuleTokenInput Input param for the function
     * @param string $expectedFieldType What field_type to find in the field_defs array.
     * @param stdClass $returnedExpressionValue Return value for the call to processValueExpression
     * @param stdClass $expected Expected value to match
     */
    public function testAddValueToTransformedToken(stdClass $criteriaTokenInput, stdClass $businessRuleTokenInput, $expectedFieldType, stdClass $returnedExpressionValue, stdClass $expected)
    {
        // The constructor gets two static objects from external classes -> BLOCK IT
        $mock = $this->getMockBuilder(PMSEBusinessRuleConversor::class)
            ->disableOriginalConstructor()
            // Don't mock what we want to test.
            ->setMethodsExcept(['addValueToTransformedToken'])
            ->getMock();

        $mock->expects($this->once())
            ->method('processValueExpression')
            ->with($businessRuleTokenInput->value)
            ->willReturn($returnedExpressionValue);

        // Mock the evaluated bean so the field type can be retrieved.
        $beanMock = new stdClass();
        $beanMock->field_defs = [
            $criteriaTokenInput->expField => [
                'type' => $expectedFieldType,
            ],
        ];
        TestReflection::setProtectedValue($mock, 'evaluatedBean', $beanMock);

        // The result will be the mutated input.
        $mock->addValueToTransformedToken($criteriaTokenInput, $businessRuleTokenInput);
        $this->assertEquals($expected, $criteriaTokenInput);
    }

    public function addValueToTransformedTokenDataProvider()
    {
        $returnArray = [];

        // A regular field type
        $returnArray[] = $this->makeAddValueToTransformedTokenTuple('string');

        // Check that currency tokens are processed correctly and return the extra "expCurrency" field.
        // Or if that has been fixed, check that legacy customer data will still work.
        $currencyValueExpression = $this->makeValueExpressionMock('currency', 'expField');
        $currencyValueToken = $this->makeProcessValueExpressionReturnMock(json_encode($currencyValueExpression), 'string');
        $returnArray[] = $this->makeAddValueToTransformedTokenTuple('currency', 'expCurrency', $currencyValueToken);

        // Check that currency tokens would still work if they were fixed to use expCurrency instead of expField.
        $fixedCurrencyValueExpression = $this->makeValueExpressionMock('currency', 'expField');
        $fixedCurrencyValueToken = $this->makeProcessValueExpressionReturnMock(json_encode($fixedCurrencyValueExpression), 'string');
        $returnArray[] = $this->makeAddValueToTransformedTokenTuple('currency', 'expCurrency', $fixedCurrencyValueToken);

        return $returnArray;
    }

    /**
     * @covers ::transformToken
     * @dataProvider transformTokenDataProvider
     * @test
     * @param mixed $businessRulesToken
     * @param stdClass $expectedCriteriaToken
     * @param array $propertiesToReplace
     */
    public function testTransformToken($businessRulesToken, stdClass $expectedCriteriaToken, array $propertiesToReplace)
    {
        // The constructor gets two static objects from external classes -> BLOCK IT
        $mock = $this->getMockBuilder(PMSEBusinessRuleConversor::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['transformToken'])
            /*
             * addValueToTransformedToken mutates the input criteria token. Since the mutations are used
             * by transformToken, they must be mocked using a callback. For the callback to actually mutate
             * the input, we have to make sure it does not clone the input we give it.
             */
            ->disableArgumentCloning()
            ->getMock();

        // Does not process non-objects
        if (is_object($businessRulesToken)) {
            // Ensure the condition operator is transformed.
            $mock->expects($this->once())
                ->method('transformConditionOperator')
                ->with($businessRulesToken->condition)
                ->willReturn('blah');

            $mock->expects($this->once())
                ->method('addValueToTransformedToken')
                // Use an anonymous function to pass the right params to mutateCriteriaToken.
                ->willReturnCallback(
                    function ($criteriaToken, $businessToken) use ($propertiesToReplace) {
                        $this->mutateCriteriaToken($criteriaToken, $propertiesToReplace);
                    }
                );
        }

        // Get the result and compare.
        $result = $mock->transformToken($businessRulesToken);
        $this->assertEquals($expectedCriteriaToken, $result);
    }

    public function transformTokenDataProvider()
    {
        $returnArray = [];
        // I don't know if this is worth testing for but it's logic in the code
        $returnArray[] =
            [
                'businessRulesToken' => 'goat',
                'expectedToken' => new stdClass(),
                'valueTokenReturn' => $this->getPropertiesToAdd(),
            ];

        // A regular field on whatever module
        $returnArray[] = $this->makeTransformTokenTuple('test_field_a', 'Accounts');

        //Check that an extra field is added properly if returned in the value token.
        $returnArray[] = $this->makeTransformTokenTuple('test_field_b', 'Contacts', 'expCurrency');

        return $returnArray;
    }

    /**
     * Used to mock the return from processValueExpression with less mess
     * @param mixed $value The $value property of the returned class
     * @param string $type The $type property of the returned class
     * @return stdClass
     */
    private function makeProcessValueExpressionReturnMock($value, $type)
    {
        $valueTokenMock = new stdClass();
        $valueTokenMock->value = $value;
        $valueTokenMock->type = $type;
        return $valueTokenMock;
    }

    /**
     * Make an input array for testAddValueToTransformedTokenTuple.
     * Helps to keep the big objects out of the data provider to make it more clean.
     * @param string $expectedFieldType What type should be set.
     * @param null|string $expectedExtraProperty If not null, the name of another property expected to be added with value 'extra'.
     * @param null $returnedExpressionValue What value should be returned by the call to Process Value Expression if a generic one isn't good enough.
     * @return array
     */
    private function makeAddValueToTransformedTokenTuple($expectedFieldType = 'string', $expectedExtraProperty = null, $returnedExpressionValue = null)
    {
        $businessRulesToken = new stdClass();
        $businessRulesToken->value = 'blah';

        $criteriaToken = new stdClass();
        $criteriaToken->expField = 'blah';

        $expected = clone($criteriaToken);
        $expected->expValue = 'goat';
        $expected->expSubtype = $expectedFieldType;
        if ($expectedExtraProperty != null) {
            $expected->$expectedExtraProperty = 'extra';
        }


        if ($returnedExpressionValue == null) {
            $returnedExpressionValue = $this->makeProcessValueExpressionReturnMock('goat', $expectedFieldType);
        }

        return [
            'criteriaTokenInput' => $criteriaToken,
            'businessRulesTokenInput' => $businessRulesToken,
            'expectedFieldType' => $expectedFieldType,
            'returnedExpressionValue' => $returnedExpressionValue,
            'expected' => $expected,
        ];
    }

    /**
     * Mutate the given criteria token by adding all properties found in
     * the passed array.
     * @param stdClass $criteriaToken The token to mutate
     * @param array $propertiesToReplace An object containing the replacement properties
     */
    private function mutateCriteriaToken(stdClass $criteriaToken, array $propertiesToReplace)
    {
        foreach ($propertiesToReplace as $key => $value) {
            $criteriaToken->$key = $value;
        }
    }

    /**
     * Used to easily mock the expression that would be found in
     * businessRuleToken->value. This is currently only needed for
     * when that value is currency, as in that case the return value
     * is an expression that is json_encoded.
     * @param string $subType What subtype to use. Defaults to 'blah'
     * @param null|string $extraProperty If not null, adds an extra field with name give and value 'extra'
     * @return array
     */
    private function makeValueExpressionMock($subType = 'blah', $extraProperty = null)
    {
        $mock = [
            'expType' => 'CONSTANT',
            'expSubtype' => $subType,
            'expLabel' => 'llama',
            'expValue' => 'goat',
        ];

        if ($extraProperty != null) {
            $mock[$extraProperty] = 'extra';
        }

        return $mock;
    }

    /**
     * Used specify what properties should be added by the call to addValueToTransformedToken
     * @param null|string $extraProperty If not null, use for the name of an extra property field with value 'extra'
     * @return array
     */
    private function getPropertiesToAdd($extraProperty = null)
    {
        $valuesToAdd = [];
        $valuesToAdd['expValue'] = 'goat';
        $valuesToAdd['expSubtype'] = 'string';
        if ($extraProperty != null) {
            $valuesToAdd[$extraProperty] = 'extra';
        }
        return $valuesToAdd;
    }

    /**
     * Used to make the data provider function more clean.
     * @param string $inputFieldName
     * @param string $module
     * @param null|string $expectedExtraPropertyName
     * @return array
     */
    private function makeTransformTokenTuple($inputFieldName, $module, $expectedExtraPropertyName = null)
    {
        $businessRulesToken = new stdClass();
        $businessRulesToken->condition = '>';
        $businessRulesToken->variable_name = $inputFieldName;
        $businessRulesToken->variable_module = $module;

        $expectedToken = new stdClass();
        $expectedToken->expDirection = 'after';
        $expectedToken->expType = 'MODULE';
        $expectedToken->expOperator = 'blah';
        $expectedToken->expField = $inputFieldName;
        $expectedToken->expValue = 'goat';
        $expectedToken->expSubtype = 'string';
        $expectedToken->expModule = $module;
        $expectedToken->expLabel = $inputFieldName . ' > goat';
        if ($expectedExtraPropertyName != null) {
            $expectedToken->$expectedExtraPropertyName = 'extra';
        }

        $valuesToAdd = $this->getPropertiesToAdd($expectedExtraPropertyName);

        return [
            'businessRulesToken' => $businessRulesToken,
            'expectedToken' => $expectedToken,
            'valuesToAdd' => $valuesToAdd,
        ];
    }
}

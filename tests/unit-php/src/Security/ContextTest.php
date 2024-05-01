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

namespace Sugarcrm\SugarcrmTestsUnit\Security;

use DomainException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Context
 * @covers \Sugarcrm\Sugarcrm\Security\Context\SubjectAttributes
 */
class ContextTest extends TestCase
{
    /**
     * @var Context
     */
    private $context;

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->context = new Context($logger);
    }

    /**
     * @test
     * @covers ::jsonSerialize
     */
    public function emptyContext()
    {
        $this->assertContext(null, $this->context);
    }

    /**
     * @test
     * @covers ::activateSubject
     * @covers ::deactivateSubject
     * @covers ::jsonSerialize
     */
    public function activateDeactivateSubject()
    {
        $subject1 = $this->createSubject([
            'foo' => 'bar',
        ]);
        $this->context->activateSubject($subject1);

        $this->assertContext([
            'subject' => [
                'foo' => 'bar',
            ],
        ], $this->context);

        $subject2 = $this->createSubject([
            'baz' => 'qux',
        ]);
        $this->context->activateSubject($subject2);

        $this->assertContext([
            'subject' => [
                'baz' => 'qux',
            ],
        ], $this->context);

        $this->context->deactivateSubject($subject2);

        $this->assertContext([
            'subject' => [
                'foo' => 'bar',
            ],
        ], $this->context);

        $this->context->deactivateSubject($subject1);

        $this->assertContext(null, $this->context);
    }

    /**
     * @test
     * @covers ::deactivateSubject
     * @covers ::hasActiveSubject
     * @covers ::top
     */
    public function deactivateWhenEmpty()
    {
        $subject = $this->createSubject([]);

        $this->expectException(DomainException::class);
        $this->context->deactivateSubject($subject);
    }

    /**
     * @test
     * @covers ::deactivateSubject
     * @covers ::hasActiveSubject
     * @covers ::top
     */
    public function deactivateInactiveSubject()
    {
        $subject1 = $this->createSubject([]);
        $subject2 = $this->createSubject([]);

        $this->context->activateSubject($subject1);

        $this->expectException(DomainException::class);
        $this->context->deactivateSubject($subject2);
    }

    /**
     * @test
     * @covers ::setAttribute
     * @covers ::unsetAttribute
     */
    public function subjectAttributes()
    {
        $subject1 = $this->createSubject([
            'foo' => 'bar',
        ]);

        $this->context->activateSubject($subject1);
        $this->context->setAttribute('attr1', 'value1');

        $this->assertContext([
            'subject' => [
                'foo' => 'bar',
            ],
            'attributes' => [
                'attr1' => 'value1',
            ],
        ], $this->context);

        $this->context->setAttribute('attr2', 'value2');

        $this->assertContext([
            'subject' => [
                'foo' => 'bar',
            ],
            'attributes' => [
                'attr1' => 'value1',
                'attr2' => 'value2',
            ],
        ], $this->context);

        $this->context->unsetAttribute('attr1');

        $this->assertContext([
            'subject' => [
                'foo' => 'bar',
            ],
            'attributes' => [
                'attr2' => 'value2',
            ],
        ], $this->context);
    }

    /**
     * @test
     * @covers ::setAttribute
     * @covers ::unsetAttribute
     */
    public function attributesAreAttachedToSubject()
    {
        $subject1 = $this->createSubject([
            'foo' => 'bar',
        ]);

        $this->context->activateSubject($subject1);
        $this->context->setAttribute('attr1', 'value1');

        $subject2 = $this->createSubject([
            'baz' => 'qux',
        ]);
        $this->context->activateSubject($subject2);
        $this->context->setAttribute('attr2', 'value2');

        $this->assertContext([
            'subject' => [
                'baz' => 'qux',
            ],
            'attributes' => [
                'attr2' => 'value2',
            ],
        ], $this->context);

        $this->context->deactivateSubject($subject2);

        $this->assertContext([
            'subject' => [
                'foo' => 'bar',
            ],
            'attributes' => [
                'attr1' => 'value1',
            ],
        ], $this->context);
    }

    private function createSubject(array $data)
    {
        $subject = $this->createMock(Subject::class);
        $subject->method('jsonSerialize')
            ->willReturn($data);

        return $subject;
    }

    private function assertContext($data, Context $context)
    {
        $this->assertSame($data, $context->jsonSerialize());
    }
}

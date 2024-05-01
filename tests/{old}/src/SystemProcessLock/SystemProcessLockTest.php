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

namespace Sugarcrm\SugarcrmTests\SystemProcessLock;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\SystemProcessLock\SystemProcessLock;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SystemProcessLock\SystemProcessLock
 */
class SystemProcessLockTest extends TestCase
{
    /**
     * @covers ::lock
     * @covers ::unlock
     */
    public function testLockUnlock()
    {
        $options = ['iterations_before_fault' => 1, 'iteration_wait_microseconds' => 0];
        $lockId = 'test_lock';
        $lockKey = 'test_lock_key';
        $systemProcessLock = new SystemProcessLock($lockId, $lockKey, $options);
        // unlock if locked
        $systemProcessLock->unlock();

        // lock must be successful here
        $isLocked = $systemProcessLock->lock();
        $this->assertTrue($isLocked);

        // attempt to lock second time must return false as it's locked already
        $isLocked = $systemProcessLock->lock();
        $this->assertFalse($isLocked);

        // unlock and lock again to ensure that unlock allows locking after its execution
        $systemProcessLock->unlock();
        $isLocked = $systemProcessLock->lock();
        $this->assertTrue($isLocked);

        // unlock
        $systemProcessLock->unlock();
    }

    /**
     * @covers ::isAttemptLimitReached
     * @covers ::resetAttemptCounter
     * @covers ::wait
     */
    public function testAttempts()
    {
        $options = ['iterations_before_fault' => 2, 'iteration_wait_microseconds' => 0];
        $lockId = 'test_lock';
        $lockKey = 'test_lock_key';
        $systemProcessLock = new SystemProcessLock($lockId, $lockKey, $options);
        // unlock if locked
        $systemProcessLock->unlock();

        // successful lock
        $systemProcessLock->lock();
        $this->assertFalse($systemProcessLock->isAttemptLimitReached());
        $systemProcessLock->resetAttemptCounter();

        // attempt 1 - lock refused
        $systemProcessLock->lock();
        $this->assertFalse($systemProcessLock->isAttemptLimitReached());

        // attempt 2 - lock refused and attempt limit reached
        $systemProcessLock->lock();
        $this->assertTrue($systemProcessLock->isAttemptLimitReached());

        // following should reset attempt counter
        $systemProcessLock->resetAttemptCounter();
        $this->assertFalse($systemProcessLock->isAttemptLimitReached());
    }

    public function providerIsolatedCall()
    {
        return [
            // see phpdoc for ::testIsolatedCall
            [true, 1, 1, 1, 0, 1],
            [true, 10, 3, 4, 0, 0],
            [true, 3, 2, 2, 0, 1],
            [false, 1, 0, 1, 0, 0],
            [true, 10, 999, 2, 1, 0, 1000000, 1],
        ];
    }

    /**
     * @dataProvider providerIsolatedCall
     * @param bool $isCurrentlyLocked Is process locked before we started
     * @param int $iterationLimit Lock attempts limit
     * @param int $checkReturnsFalseAfterCall A number of calls of "checkCondition" callback. After that number it
     * starts returning "false" (that means - rebuild is not necessary)
     * @param int $expectedCheckCalls Expected number of calls of "checkCondition" callback
     * @param int $expectedRebuildCalls Expected number of calls of "Rebuild" callback
     * @param int $expectedRefuseCalls Expected number of calls of "Refuse" callback
     * @param int $iterationWaitMicroseconds How long to wait between the lock attempts
     * @param int $lockTimeoutSeconds The lock record lifetime until it's removed by timeout
     */
    public function testIsolatedCall(
        bool $isCurrentlyLocked,
        int  $iterationLimit,
        int  $checkReturnsFalseAfterCall,
        int  $expectedCheckCalls,
        int  $expectedRebuildCalls,
        int  $expectedRefuseCalls,
        int  $iterationWaitMicroseconds = 10,
        int  $lockTimeoutSeconds = 600
    ) {

        $options = [
            'iterations_before_fault' => $iterationLimit,
            'iteration_wait_microseconds' => $iterationWaitMicroseconds,
            'lock_timeout_seconds' => $lockTimeoutSeconds,
        ];
        $lockId = 'test_lock';
        $lockKey = 'test_lock_key';
        $systemProcessLock = new SystemProcessLock($lockId, $lockKey, $options);
        // unlock if locked
        $systemProcessLock->unlock();

        if ($isCurrentlyLocked) {
            $systemProcessLock->lock();
        }

        // group of mocks to test callbacks
        /** @var callable $mockCheck */
        $mockCheck = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $mockCheck->expects($this->exactly($expectedCheckCalls))->method('__invoke');

        /** @var callable $mockLongRunningFunction */
        $mockLongRunningFunction = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $mockLongRunningFunction->expects($this->exactly($expectedRebuildCalls))->method('__invoke');

        /** @var callable $mockRefuse */
        $mockRefuse = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $mockRefuse->expects($this->exactly($expectedRefuseCalls))->method('__invoke');

        $checkCallCounter = 0;
        $systemProcessLock->isolatedCall(
            function () use ($mockCheck, &$checkCallCounter, $checkReturnsFalseAfterCall) {
                $mockCheck();
                $checkCallCounter++;
                return $checkCallCounter > $checkReturnsFalseAfterCall ? false : true;
            },
            function () use ($mockLongRunningFunction) {
                $mockLongRunningFunction();
            },
            function () use ($mockRefuse) {
                $mockRefuse();
            }
        );
    }

    public function testIsolatedCallNestedCallHandling()
    {
        $options = [
            'iterations_before_fault' => 10,
            'iteration_wait_microseconds' => 1000,
            'lock_timeout_seconds' => 1,
        ];
        $lockId = 'test_lock';
        $lockKey = 'test_lock_key';
        $systemProcessLock = new SystemProcessLock($lockId, $lockKey, $options);
        // unlock if locked
        $systemProcessLock->unlock();

        // group of mocks to test callbacks
        /** @var callable $mockCheck */
        $mockCheck = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $mockCheck->expects($this->exactly(1))->method('__invoke');

        /** @var callable $mockLongRunningFunction */
        $mockLongRunningFunction = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $mockLongRunningFunction->expects($this->exactly(5))->method('__invoke');

        /** @var callable $mockRefuse */
        $mockRefuse = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $mockRefuse->expects($this->exactly(0))->method('__invoke');

        $rebuildFunc = null;
        $isolatedCall = function () use ($systemProcessLock, &$rebuildFunc, $mockCheck, $mockRefuse) {
            $systemProcessLock->isolatedCall(
                function () use ($mockCheck) {
                    $mockCheck();
                    return true;
                },
                $rebuildFunc,
                function () use ($mockRefuse) {
                    $mockRefuse();
                }
            );
        };

        $nestedCalls = 0;

        $rebuildFunc = function () use ($mockLongRunningFunction, $isolatedCall, &$nestedCalls) {
            $nestedCalls++;
            $mockLongRunningFunction();
            if ($nestedCalls < 5) {
                $isolatedCall();
            }
        };

        $isolatedCall();
    }
}

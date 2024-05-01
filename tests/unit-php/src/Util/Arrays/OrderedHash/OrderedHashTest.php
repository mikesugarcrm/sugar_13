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

namespace Sugarcrm\SugarcrmTestsUnit\Util\Arrays\OrderedHash;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash
 */
class OrderedHashTest extends TestCase
{
    /**
     * @return array
     */
    public function hashProvider()
    {
        return [
            [
                [],
            ],
            [
                [
                    'susan' => 'Susan',
                    'suzy' => 'Suzy',
                ],
            ],
        ];
    }

    /**
     * Invalid hash keys provider.
     * @return array
     */
    public function invalidHashKeys()
    {
        return [
            [false],
            [6.53],
            [new \stdClass()],
            [['invalid']],
        ];
    }

    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::__construct
     * @covers ::toArray
     * @dataProvider hashProvider
     */
    public function testHashGivenIsTheSameReturned($data)
    {
        $hash = new OrderedHash($data);
        $this->assertSame($data, $hash->toArray());
    }

    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::__construct
     * @covers ::toArray
     */
    public function testToArrayWhenEmpty()
    {
        $hash = new OrderedHash();
        $this->assertSame([], $hash->toArray());
    }

    /**
     * @covers ::add
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element
     */
    public function testAdd()
    {
        $hash = new OrderedHash();
        $hash->add(null, 'susan', 'Susan');

        $this->assertSame('susan', $hash->bottom()->getKey());
        $this->assertSame('susan', $hash->top()->getKey());

        $susan = $hash->top();
        $this->assertSame('Susan', $susan->getValue());
        $this->assertNull($susan->getBefore());
        $this->assertNull($susan->getAfter());

        $hash->add(null, 'sally', 'Sally');
        $this->assertSame('sally', $hash->bottom()->getKey());
        $this->assertSame('susan', $hash->top()->getKey());

        $before = $hash->bottom();
        $after = $before->getAfter();
        $hash->add($before, 'suzy', 'Suzy');

        $this->assertSame('suzy', $before->getAfter()->getKey());
        $this->assertSame('suzy', $after->getBefore()->getKey());

        $expected = [
            'sally' => 'Sally',
            'suzy' => 'Suzy',
            'susan' => 'Susan',
        ];
        $this->assertSame($expected, $hash->toArray());
    }

    /**
     * @covers ::add
     * @dataProvider invalidHashKeys
     */
    public function testAddInvalidKey($key)
    {
        $this->expectException(OutOfRangeException::class);
        $hash = new OrderedHash();
        $hash->add(null, $key, 'Foo');
    }

    /**
     * @covers ::add
     */
    public function testAddDuplicateKey()
    {
        $hash = new OrderedHash([
            'sally' => 'Sally',
        ]);

        $this->expectException(RuntimeException::class);
        $hash->add($hash->top(), 'sally', 'Foo');
    }

    /**
     * @covers ::bottom
     */
    public function testBottom()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
            'stephanie' => 'Stephanie',
            'sara' => 'Sara',
            'sue' => 'Sue',
        ]);
        $head = $hash->bottom();

        $this->assertSame('susan', $head->getKey());
        $this->assertSame('Susan', $head->getValue());
    }

    /**
     * @covers ::bottom
     */
    public function testBottomOnEmpty()
    {
        $hash = new OrderedHash();

        $this->expectException(RuntimeException::class);
        $hash->bottom();
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
            'stephanie' => 'Stephanie',
            'sara' => 'Sara',
            'sue' => 'Sue',
        ]);
        $this->assertCount(6, $hash);
    }

    /**
     * @covers ::current
     * @covers ::next
     * @covers ::prev
     * @covers ::rewind
     * @covers ::valid
     * @covers ::fastForward
     */
    public function testIterator()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
            'stephanie' => 'Stephanie',
            'sara' => 'Sara',
            'sue' => 'Sue',
        ]);
        $hash->rewind();
        $this->assertSame('susan', $hash->current()->getKey());

        $hash->next();
        $this->assertSame('suzy', $hash->current()->getKey());

        $hash->rewind();
        $this->assertSame('susan', $hash->current()->getKey());

        while ($hash->valid()) {
            $hash->next();
        }
        $this->assertNull($hash->current());

        $hash->fastForward();
        $this->assertSame('sue', $hash->current()->getKey());

        while ($hash->valid()) {
            $hash->prev();
        }
        $this->assertNull($hash->current());
    }

    /**
     * @covers ::isEmpty
     */
    public function testIsEmpty()
    {
        $hash = new OrderedHash();

        $this->assertTrue($hash->isEmpty());
        $this->assertEmpty($hash);

        $hash = new OrderedHash([]);

        $this->assertTrue($hash->isEmpty());
        $this->assertEmpty($hash);

        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
        ]);

        $this->assertFalse($hash->isEmpty());
        $this->assertNotEmpty($hash);
    }

    /**
     * @covers ::key
     */
    public function testKey_CurrentIsTheHead_ReturnsTheValueOfHead()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
        ]);

        $hash->rewind();
        $this->assertSame('susan', $hash->key());

        $hash->next();
        $this->assertSame('suzy', $hash->key());

        $hash->next();
        $this->assertNull($hash->key());
    }

    /**
     * @covers ::move
     */
    public function testMove()
    {
        $expected = [
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
            'stephanie' => 'Stephanie',
            'sara' => 'Sara',
            'sue' => 'Sue',
        ];
        $hash = new OrderedHash($expected);
        $hash->move('invalid', $hash->bottom());
        $this->assertSame($expected, $hash->toArray());

        $middle = $hash['stephanie'];
        $hash->move('susan', $middle);
        $this->assertSame('susan', $middle->getAfter()->getKey());

        $hash->move('susan', $hash->top());
        $this->assertSame('susan', $hash->top()->getKey());
        $this->assertSame('suzy', $hash->bottom()->getKey());

        $hash->move('susan', $middle);
        $this->assertSame('susan', $middle->getAfter()->getKey());

        $hash->move('susan', null);
        $this->assertSame($expected, $hash->toArray());
    }

    /**
     * @covers ::move
     * @dataProvider invalidHashKeys
     */
    public function testMoveInvalidKey($key)
    {
        $hash = new OrderedHash();

        $this->expectException(OutOfRangeException::class);
        $hash->move($key, null);
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     */
    public function testOffsetExistsAndGet()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ]);

        $this->assertArrayNotHasKey('missing', $hash);
        $this->assertArrayHasKey('sally', $hash);
        $this->assertArrayHasKey('suzy', $hash);
        $this->assertArrayHasKey('susan', $hash);

        $this->assertNull($hash['missing']);
        $this->assertNotNull($hash['sally']);
        $this->assertNotNull($hash['suzy']);
        $this->assertNotNull($hash['susan']);

        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element::class, $hash['sally']);
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element::class, $hash['suzy']);
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element::class, $hash['susan']);
    }

    /**
     * @covers ::offsetGet
     * @dataProvider invalidHashKeys
     */
    public function testOffsetGetInvalid($key)
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ]);

        $this->expectException(OutOfRangeException::class);
        $hash[$key];
    }

    /**
     * @covers ::offsetSet
     * @dataProvider invalidHashKeys
     */
    public function testOffsetSetInvalid($key)
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ]);

        $this->expectException(OutOfRangeException::class);
        $hash[$key] = 'Foo';
    }

    /**
     * @covers ::offsetSet
     */
    public function testOffsetSet()
    {
        $hash = new OrderedHash();

        $hash['suzy'] = 'Suzy';
        $this->assertSame('suzy', $hash->top()->getKey());

        $hash['sally'] = 'Sally';
        $this->assertSame('sally', $hash->top()->getKey());

        $this->assertSame('Suzy', $hash['suzy']->getValue());
        $hash['suzy'] = 'Suzanne';
        $this->assertSame('Suzanne', $hash['suzy']->getValue());
        $this->assertSame('sally', $hash->top()->getKey());
    }

    /**
     * @covers ::offsetUnset
     * @dataProvider invalidHashKeys
     */
    public function testOffsetUnsetInvalid($key)
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ]);

        $this->expectException(OutOfRangeException::class);
        unset($hash[$key]);
    }

    /**
     * @covers ::offsetUnset
     */
    public function testOffsetUnset()
    {
        $expected = [
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ];
        $hash = new OrderedHash($expected);

        unset($hash['missing']);
        $this->assertSame($expected, $hash->toArray());

        $element = $hash['suzy'];
        $before = $element->getBefore();
        $after = $element->getAfter();
        unset($hash['suzy']);
        $this->assertNull($hash['suzy']);
        $this->assertArrayNotHasKey('suzy', $hash);

        $this->assertSame('susan', $hash->bottom()->getKey());
        $this->assertSame('sally', $hash->top()->getKey());
        $this->assertSame('susan', $after->getBefore()->getKey());
        $this->assertSame('sally', $before->getAfter()->getKey());

        unset($hash['sally']);
        unset($hash['susan']);
        $this->assertTrue($hash->isEmpty());
        $this->assertEmpty($hash);
    }

    /**
     * @covers ::offsetUnset
     */
    public function testOffsetUnsetCurrent()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ]);
        $hash->rewind();

        while ($hash->valid()) {
            if ($hash->current()->getKey() === 'suzy') {
                break;
            }
            $hash->next();
        }

        unset($hash['suzy']);
        $this->assertSame('sally', $hash->current()->getKey());
        unset($hash['sally']);
        $this->assertNull($hash->current());
        unset($hash['susan']);
        $this->assertNull($hash->current());
    }

    /**
     * @covers ::pop
     */
    public function testPopOnEmpty()
    {
        $hash = new OrderedHash();

        $this->expectException(RuntimeException::class);
        $hash->pop();
    }

    /**
     * @covers ::pop
     */
    public function testPop()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ]);

        $element = $hash->pop();
        $this->assertNotNull($element);
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element::class, $element);
        $this->assertNotNull($element->getBefore());
        $this->assertNull($element->getAfter());
        $this->assertSame('sally', $element->getKey());

        $this->assertNull($hash['sally']);
        $this->assertSame('suzy', $hash->top()->getKey());
        $this->assertSame('susan', $hash->bottom()->getKey());
    }

    /**
     * @covers ::push
     */
    public function testPush()
    {
        $hash = new OrderedHash();
        $hash->push('stacy', 'Stacy');

        $this->assertSame('stacy', $hash->top()->getKey());
        $this->assertSame('stacy', $hash->bottom()->getKey());

        $hash->push('sally', 'Sally');
        $this->assertSame('sally', $hash->top()->getKey());
        $this->assertSame('stacy', $hash->bottom()->getKey());
    }

    /**
     * @covers ::shift
     */
    public function testShiftOnEmpty()
    {
        $hash = new OrderedHash();

        $this->expectException(RuntimeException::class);
        $hash->shift();
    }

    /**
     * @covers ::shift
     */
    public function testShift()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
        ]);

        $element = $hash->shift();
        $this->assertNotNull($element);
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element::class, $element);
        $this->assertNull($element->getBefore());
        $this->assertNotNull($element->getAfter());
        $this->assertSame('susan', $element->getKey());

        $this->assertNull($hash['susan']);
        $this->assertSame('suzy', $hash->bottom()->getKey());
        $this->assertSame('sally', $hash->top()->getKey());
    }

    /**
     * @covers ::top
     */
    public function testTop()
    {
        $hash = new OrderedHash([
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
            'stephanie' => 'Stephanie',
            'sara' => 'Sara',
            'sue' => 'Sue',
        ]);
        $head = $hash->top();

        $this->assertSame('sue', $head->getKey());
        $this->assertSame('Sue', $head->getValue());
    }

    /**
     * @covers ::top
     */
    public function testTopOnEmpty()
    {
        $hash = new OrderedHash();

        $this->expectException(RuntimeException::class);
        $hash->top();
    }

    /**
     * @covers ::unshift
     */
    public function testUnshift()
    {
        $hash = new OrderedHash();
        $hash->unshift('stacy', 'Stacy');

        $this->assertSame('stacy', $hash->top()->getKey());
        $this->assertSame('stacy', $hash->bottom()->getKey());

        $hash->unshift('sally', 'Sally');
        $this->assertSame('sally', $hash->bottom()->getKey());
        $this->assertSame('stacy', $hash->top()->getKey());
    }
}

<?php

namespace YetAnother\DI\Tests;

use YetAnother\DI\Container;

/**
 * test configuration: A->B->C
 * classes located in TestClasses/
 */

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Container */
    protected $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testGet_Default()
    {
        $a = $this->container->get('A');
        $this->assertTrue(get_class($a->b->c) == 'C');
    }

    public function testGet_Array()
    {
        list ($a, $b, $c) = $this->container->get(array('A', 'B', 'C'));
        $this->assertTrue(get_class($a) == 'A');
        $this->assertTrue(get_class($b) == 'B');
        $this->assertTrue(get_class($c) == 'C');
    }

    public function testGet_Closure()
    {
        $this->container->set('A', function (\B $b) {
            return new \A($b);
        });
        $this->container->set('B', function (\C $c) {
            return new \B($c);
        });
        $c = new \C();
        $this->container->set('C', function () use ($c) {
            return $c;
        });
        $a = $this->container->get('A');
        $this->assertTrue($a->b->c === $c);
    }

    public function testGet_Existing()
    {
        $a1 = $this->container->get('A');
        $a2 = $this->container->get('A');
        $this->assertTrue($a1 === $a2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGet_NotExistingClass()
    {
        $this->container->get('Z');
    }

    public function testSet_Default()
    {
        $c1 = new \C();
        $this->container->set('C', $c1);
        $c2 = $this->container->get('C');
        $this->assertTrue($c1 === $c2);
    }

    public function testSet_Closure()
    {
        $c1 = new \C();
        $this->container->set('C', function () use ($c1) {
            return $c1;
        });
        $c2 = $this->container->get('C');
        $this->assertTrue($c1 === $c2);
    }

    public function testHas_Existing()
    {
        $this->container->get('A');
        $this->assertTrue($this->container->has('A'));
    }

    public function testHas_NotExisting()
    {
        $this->assertFalse($this->container->has('A'));
    }

    public function testPush_Default()
    {
        $c1 = new \C();
        $this->container->push($c1);
        $c2 = $this->container->get('C');
        $this->assertTrue($c1 === $c2);
    }

    public function testPush_Array()
    {
        $a1 = new \A($b1 = new \B($c1 = new \C()));
        $this->container->push(array($a1, $b1, $c1));
        list ($a2, $b2, $c2) = $this->container->get(array('A', 'B', 'C'));
        $this->assertTrue($a1 === $a2);
        $this->assertTrue($b1 === $b2);
        $this->assertTrue($c1 === $c2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPush_Scalar()
    {
        $this->container->push('A');
    }

    public function testCreate_Default()
    {
        $a1 = $this->container->create('A');
        $a2 = $this->container->create('A');
        $this->assertFalse($a1 === $a2);
    }

    public function testCreate_Closure()
    {
        $this->container->set('C', function() {
            return new \C();
        });
        $c1 = $this->container->create('C');
        $c2 = $this->container->create('C');
        $this->assertFalse($c1 === $c2);
    }

    public function testCreate_Storing()
    {
        $this->container->create('A');
        $this->assertFalse($this->container->has('A'));
    }

    public function testRemove_Exist()
    {
        $this->container->get('A');
        $this->container->remove('A');
        $this->assertFalse($this->container->has('A'));
    }

    public function testRemove_NotExist()
    {
        $this->container->remove('A');
        $this->assertFalse($this->container->has('A'));
    }

    /********** ArrayAccess functions **********/

    public function testOffsetGet_Default()
    {
        $a = $this->container['A'];
        $this->assertTrue(get_class($a->b->c) == 'C');
    }

    public function testOffsetGet_Closure()
    {
        $this->container['A'] = function (\B $b) {
            return new \A($b);
        };
        $this->container['B'] = function (\C $c) {
            return new \B($c);
        };
        $c = new \C();
        $this->container['C'] = function () use ($c) {
            return $c;
        };
        $a = $this->container['A'];
        $this->assertTrue($a->b->c === $c);
    }

    public function testOffsetSet_NullOffset()
    {
        $c1 = new \C();
        $this->container[] = $c1;
        $c2 = $this->container['C'];
        $this->assertTrue($c1 === $c2);
    }

    public function testOffsetSet_NotNullOffset()
    {
        $c1 = new \C();
        $this->container['C'] = $c1;
        $c2 = $this->container['C'];
        $this->assertTrue($c1 === $c2);
    }

    public function testOffsetSet_Closure()
    {
        $c1 = new \C();
        $this->container['C'] = function () use ($c1) {
            return $c1;
        };
        $c2 = $this->container['C'];
        $this->assertTrue($c1 === $c2);
    }

    public function testOffsetExist_IfExist()
    {
        $this->container['A'];
        $this->assertTrue(isset($this->container['A']));
    }

    public function testOffsetExist_IfNotExist()
    {
        $this->assertFalse(isset($this->container['A']));
    }

    public function testOffsetUnset_Exist()
    {
        $this->container['A'];
        unset($this->container['A']);
        $this->assertFalse(isset($this->container['A']));
    }

    public function testOffsetUnset_NotExist()
    {
        unset($this->container['A']);
        $this->assertFalse(isset($this->container['A']));
    }
}
<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo\Tests\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ArrayShapeType;

class ArrayShapeTypeTest extends TestCase
{
    public function testGetCollectionKeyType()
    {
        $type = new ArrayShapeType([
            1 => ['type' => Type::bool(), 'optional' => false],
        ]);
        $this->assertEquals(Type::int(), $type->getCollectionKeyType());

        $type = new ArrayShapeType([
            'foo' => ['type' => Type::bool(), 'optional' => false],
        ]);
        $this->assertEquals(Type::string(), $type->getCollectionKeyType());

        $type = new ArrayShapeType([
            1 => ['type' => Type::bool(), 'optional' => false],
            'foo' => ['type' => Type::bool(), 'optional' => false],
        ]);
        $this->assertEquals(Type::union(Type::int(), Type::string()), $type->getCollectionKeyType());
    }

    public function testGetCollectionValueType()
    {
        $type = new ArrayShapeType([
            1 => ['type' => Type::bool(), 'optional' => false],
        ]);
        $this->assertEquals(Type::bool(), $type->getCollectionValueType());

        $type = new ArrayShapeType([
            'foo' => ['type' => Type::bool(), 'optional' => false],
            'bar' => ['type' => Type::int(), 'optional' => false],
        ]);
        $this->assertEquals(Type::union(Type::int(), Type::bool()), $type->getCollectionValueType());

        $type = new ArrayShapeType([
            'foo' => ['type' => Type::bool(), 'optional' => false],
            'bar' => ['type' => Type::nullable(Type::string()), 'optional' => false],
        ]);
        $this->assertEquals(Type::nullable(Type::union(Type::bool(), Type::string())), $type->getCollectionValueType());

        $type = new ArrayShapeType([
            'foo' => ['type' => Type::true(), 'optional' => false],
            'bar' => ['type' => Type::false(), 'optional' => false],
        ]);
        $this->assertEquals(Type::bool(), $type->getCollectionValueType());
    }

    public function testAccepts()
    {
        $type = new ArrayShapeType([
            'foo' => ['type' => Type::bool(), 'optional' => false],
            'bar' => ['type' => Type::string(), 'optional' => true],
        ]);

        $this->assertFalse($type->accepts('string'));
        $this->assertFalse($type->accepts([]));
        $this->assertFalse($type->accepts(['foo' => 'string']));
        $this->assertFalse($type->accepts(['foo' => true, 'other' => 'string']));

        $this->assertTrue($type->accepts(['foo' => true]));
        $this->assertTrue($type->accepts(['foo' => true, 'bar' => 'string']));
    }

    public function testToString()
    {
        $type = new ArrayShapeType([1 => ['type' => Type::bool(), 'optional' => false]]);
        $this->assertSame('array{1: bool}', (string) $type);

        $type = new ArrayShapeType([
            2 => ['type' => Type::int(), 'optional' => true],
            1 => ['type' => Type::bool(), 'optional' => false],
        ]);
        $this->assertSame('array{1: bool, 2?: int}', (string) $type);

        $type = new ArrayShapeType([
            'foo' => ['type' => Type::bool(), 'optional' => false],
            'bar' => ['type' => Type::string(), 'optional' => true],
        ]);
        $this->assertSame("array{'bar'?: string, 'foo': bool}", (string) $type);
    }
}

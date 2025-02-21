<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo\Type;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * Represents the exact shape of an array.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 *
 * @extends CollectionType<GenericType<BuiltinType<TypeIdentifier::ARRAY>>>
 */
final class ArrayShapeType extends CollectionType
{
    /**
     * @var array<array{type: Type, optional: bool}>
     */
    private readonly array $shape;

    /**
     * @param array<array{type: Type, optional: bool}> $shape
     */
    public function __construct(array $shape)
    {
        $keyTypes = [];
        $valueTypes = [];

        foreach ($shape as $k => $v) {
            $keyTypes[] = self::fromValue($k);
            $valueTypes[] = $v['type'];
        }

        if ($keyTypes) {
            $keyTypes = array_values(array_unique($keyTypes));
            $keyType = \count($keyTypes) > 1 ? self::union(...$keyTypes) : $keyTypes[0];
        } else {
            $keyType = Type::union(Type::int(), Type::string());
        }

        $valueType = $valueTypes ? CollectionType::mergeCollectionValueTypes($valueTypes) : Type::mixed();

        parent::__construct(self::generic(self::builtin(TypeIdentifier::ARRAY), $keyType, $valueType));

        $sortedShape = $shape;
        ksort($sortedShape);

        $this->shape = $sortedShape;
    }

    /**
     * @return array<array{type: Type, optional: bool}>
     */
    public function getShape(): array
    {
        return $this->shape;
    }

    public function accepts(mixed $value): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        foreach ($this->shape as $key => $shapeValue) {
            if (!($shapeValue['optional'] ?? false) && !\array_key_exists($key, $value)) {
                return false;
            }
        }

        foreach ($value as $key => $itemValue) {
            $valueType = $this->shape[$key]['type'] ?? false;
            if (!$valueType) {
                return false;
            }

            if (!$valueType->accepts($itemValue)) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        $items = [];

        foreach ($this->shape as $key => $value) {
            $itemKey = \is_int($key) ? (string) $key : \sprintf("'%s'", $key);
            if ($value['optional'] ?? false) {
                $itemKey = \sprintf('%s?', $itemKey);
            }

            $items[] = \sprintf('%s: %s', $itemKey, $value['type']);
        }

        return \sprintf('array{%s}', implode(', ', $items));
    }
}

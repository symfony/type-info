<?php

namespace Symfony\Component\TypeInfo\Tests\Fixtures;

final class DummyWithPhpDoc
{
    /**
     * @var array<Dummy>
     */
    public mixed $arrayOfDummies = [];

    /**
     * @param bool $promoted
     * @param bool $promotedVarAndParam
     */
    public function __construct(
        public mixed $promoted,
        /**
         * @var string
         */
        public mixed $promotedVar,
        /**
         * @var string
         */
        public mixed $promotedVarAndParam,
    ) {
    }

    /**
     * @param Dummy $dummy
     *
     * @return Dummy
     */
    public function getNextDummy(mixed $dummy): mixed
    {
        throw new \BadMethodCallException(sprintf('"%s" is not implemented.', __METHOD__));
    }
}

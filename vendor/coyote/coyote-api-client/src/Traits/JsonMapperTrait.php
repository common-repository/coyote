<?php

namespace Coyote\Traits;

use Coyote\ApiHelper\JsonMapperShapeValidatorMiddleware;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;

trait JsonMapperTrait
{
    public static function mapper(): JsonMapperInterface
    {
        return (new JsonMapperFactory())->bestFit();
    }

    public static function validatingMapper(): JsonMapperInterface
    {
        $mapper = self::mapper();
        $mapper->push(new JsonMapperShapeValidatorMiddleware());
        return $mapper;
    }
}
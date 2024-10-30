<?php

namespace Coyote\ApiHelper;

use JsonMapper\JsonMapperInterface;
use JsonMapper\Middleware\AbstractMiddleware;
use JsonMapper\ValueObjects\PropertyMap;
use JsonMapper\Wrapper\ObjectWrapper;
use RuntimeException;
use stdClass;

class JsonMapperShapeValidatorMiddleware extends AbstractMiddleware
{
    public function handle(
        stdClass $json,
        ObjectWrapper $object,
        PropertyMap $propertyMap,
        JsonMapperInterface $mapper
    ): void {

        foreach ((array) $json as $key => $value) {
            if (!$propertyMap->hasProperty($key)) {
                throw new RuntimeException("Missing JSON key '$key' in object interface");
            }
        }

        foreach ($propertyMap->getIterator() as $property => $value) {
            if (!array_key_exists($property, (array) $json)) {
                throw new RuntimeException("Missing object property '$property' in JSON structure");
            }
        }
    }
}

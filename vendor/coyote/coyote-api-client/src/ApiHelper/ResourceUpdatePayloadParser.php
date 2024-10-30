<?php

namespace Coyote\ApiHelper;

use Coyote\ApiPayload\WebhookUpdatePayloadApiModel;
use JsonMapper\JsonMapperFactory;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

class ResourceUpdatePayloadParser
{
    public static function parse(\stdClass $payload): ?WebhookUpdatePayloadApiModel
    {
        $logger = new Logger("Coyote/ResourceUpdatePayloadParser");
        $logger->pushHandler(new ErrorLogHandler());

        $mapper = (new JsonMapperFactory())->bestFit();
        $mapper->push(new JsonMapperShapeValidatorMiddleware());

        /** @return WebhookUpdatePayloadApiModel|null */
        try {
            $result = $mapper->mapObject($payload, (new WebhookUpdatePayloadApiModel()));
        } catch (\RuntimeException $e) {
            $logger->error($e->getMessage(), ['payload' => $payload]);
            return null;
        }

        return $result;
    }
}
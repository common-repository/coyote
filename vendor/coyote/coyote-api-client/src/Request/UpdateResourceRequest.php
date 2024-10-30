<?php

namespace Coyote\Request;

use Coyote\ApiResponse\UpdateResourceApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\ResourceUpdateModel;
use Coyote\ModelHelper\ResourceModelHelper;
use Coyote\Payload\UpdateResourcePayload;

class UpdateResourceRequest extends AbstractApiRequest
{
    // TODO what about host_uris, resource_groups?
    private const UPDATE_KEYS = ['name', 'source_uri'];

    private const PATH = '/resources/%s';

    private UpdateResourcePayload $payload;
    private InternalApiClient $apiClient;

    public function __construct(InternalApiClient $apiClient, UpdateResourcePayload $payload)
    {
        $this->apiClient = $apiClient;
        $this->payload = $payload;
    }

    public function perform(): ?ResourceUpdateModel
    {
        try {
            $json = $this->apiClient->patch(
                sprintf(self::PATH, $this->payload->id),
                $this->marshallPayload()
            );
        } catch (\Exception $error) {
            self::logError("Error updating resource {$this->payload->id}: " . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning("Unexpected null response when updating resource {$this->payload->id}");
            return null;
        }

        /** @var UpdateResourceApiResponse $response */
        $response = self::mapper()->mapObject($json, new UpdateResourceApiResponse());

        return ResourceModelHelper::mapUpdateResourceResponseToResourceUpdateModel($response->data);
    }

    private function marshallPayload(): array
    {
        return array_reduce(self::UPDATE_KEYS, function (array $data, string $key): array {
            if (!property_exists($this->payload, $key)) {
                return $data;
            }

            if (!is_null($this->payload->$key[0])) {
                $item[$key] = $this->payload->$key[0];
            }

            return $data;
        }, []);
    }
}

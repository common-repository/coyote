<?php

namespace Coyote\Request;

use Coyote\ApiResponse\CreateResourceApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\ResourceModel;
use Coyote\ModelHelper\ResourceModelHelper;
use Coyote\Payload\CreateResourcePayload;

class CreateResourceRequest extends AbstractApiRequest
{
    private const PATH = '/resources/';

    private CreateResourcePayload $payload;
    private InternalApiClient $apiClient;

    public function __construct(InternalApiClient $apiClient, CreateResourcePayload $payload) {
        $this->apiClient = $apiClient;
        $this->payload = $payload;
    }

    public function perform(): ?ResourceModel
    {
        try {
            $json = $this->apiClient->post(
                self::PATH,
                $this->marshallPayload(),
                [InternalApiClient::INCLUDE_ORG_ID => true]
            );
        } catch (\Exception $error) {
            self::logError("Error creating resource {$this->payload->source_uri}: " . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning("Unexpected null response when creating resource {$this->payload->source_uri}");
            return null;
        }

        /** @var CreateResourceApiResponse $response */
        $response = self::mapper()->mapObject($json, new CreateResourceApiResponse());

        return ResourceModelHelper::mapCreateResourceResponseToResourceModel($response);
    }

    private function marshallPayload(): array
    {
        return [
            'resource' => [
                'name' => $this->payload->name,
                'source_uri' => $this->payload->source_uri,
                'resource_type' => $this->payload->resource_type,
                'resource_group_id' => $this->payload->resource_group_id,
                'host_uris' => $this->payload->host_uris,
                'representations' => is_null($this->payload->representations)
                    ? null
                    : array_map('get_object_vars', $this->payload->representations)
            ],
        ];
    }
}

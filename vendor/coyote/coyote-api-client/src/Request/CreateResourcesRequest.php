<?php

namespace Coyote\Request;

use Coyote\ApiModel\Partial\Relationship;
use Coyote\ApiModel\ResourceApiModel;
use Coyote\ApiModel\ResourceRepresentationApiModel;
use Coyote\ApiResponse\CreateResourcesApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\ResourceModel;
use Coyote\Payload\CreateResourcePayload;
use Coyote\Payload\CreateResourcesPayload;

class CreateResourcesRequest extends AbstractApiRequest
{
    private const PATH = '/resources/create';

    private CreateResourcesPayload $payload;
    private InternalApiClient $apiClient;

    public function __construct(InternalApiClient $apiClient, CreateResourcesPayload $payload) {
        $this->apiClient = $apiClient;
        $this->payload = $payload;
    }

    /** @return ResourceModel[]|null */
    public function perform(): ?array
    {
        try {
            $json = $this->apiClient->post(
                self::PATH,
                $this->marshallPayload(),
                [InternalApiClient::INCLUDE_ORG_ID => true]
            );
        } catch (\Exception $error) {
            self::logError("Error creating resources: " . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning("Unexpected null response when creating resources");
            return null;
        }

        // Resource batch creation doesn't include its member organization
        $organization = null;

        $response = self::mapper()->mapObject($json, (new CreateResourcesApiResponse()));

        return array_map(function (ResourceApiModel $model) use ($organization, $response): ResourceModel {
            $representations = $this->getRepresentationApiModelsByResourceId(
                $response->included ?? [],
                $model->relationships->representations->data
            );
            return new ResourceModel($model, $organization, $representations);
        }, $response->data);
    }

    /**
     * @param ResourceRepresentationApiModel[] $representations
     * @param Relationship[] $relationships
     * @return ResourceRepresentationApiModel[]
     */
    private function getRepresentationApiModelsByResourceId(array $representations, array $relationships): array
    {
        $representationIds = array_map(function (Relationship $relationship): string {
            return $relationship->id;
        }, $relationships);

        return array_filter(
            $representations,
            function (ResourceRepresentationApiModel $model) use ($representationIds): bool {
                return in_array($model->attributes->id, $representationIds);
            },
        );
    }

    private function marshallPayload(): array
    {
        return [
            'resources' => array_map(function (CreateResourcePayload $resource): array {
                return [
                    'name' => $resource->name,
                    'source_uri' => $resource->source_uri,
                    'resource_type' => $resource->resource_type,
                    'resource_group_id' => $resource->resource_group_id,
                    'host_uris' => $resource->host_uris,
                    'representations' => is_null($resource->representations)
                        ? null
                        : array_map('get_object_vars', $resource->representations)
                ];
            }, $this->payload->resources)
        ];
    }
}

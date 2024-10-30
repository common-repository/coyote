<?php

namespace Coyote;

use Coyote\ApiHelper\ResourceUpdatePayloadParser;
use Coyote\Model\ProfileModel;
use Coyote\Model\RepresentationModel;
use Coyote\Model\ResourceGroupModel;
use Coyote\Model\ResourceModel;
use Coyote\Model\WebhookUpdateModel;
use Coyote\ModelHelper\ResourceModelHelper;
use Coyote\Payload\CreateResourceGroupPayload;
use Coyote\Payload\CreateResourcePayload;
use Coyote\Payload\CreateResourcesPayload;
use Coyote\Request\CreateResourceGroupRequest;
use Coyote\Request\CreateResourceRequest;
use Coyote\Request\CreateResourcesRequest;
use Coyote\Request\GetProfileRequest;
use Coyote\Request\GetResourceGroupsRequest;
use Coyote\Request\GetResourceRepresentationRequest;
use Coyote\Request\GetResourceRepresentationsRequest;
use Coyote\Request\GetResourceRequest;
use Coyote\Request\GetResourcesRequest;
use Exception;

class CoyoteApiClient
{
    private InternalApiClient $apiClient;

    public function __construct(string $endpoint, string $apiToken, ?int $organizationId = null)
    {
        $this->apiClient = new InternalApiClient($endpoint, $apiToken, $organizationId);
    }

    public function getProfile(): ?ProfileModel
    {
        return (new GetProfileRequest($this->apiClient))->data();
    }

    /** @return ResourceModel[]|null */
    public function getResources(): ?array
    {
        return (new GetResourcesRequest($this->apiClient))->data();
    }

    public function getResource(string $id): ?ResourceModel
    {
        return (new GetResourceRequest($this->apiClient, $id))->data();
    }

    public function createResource(CreateResourcePayload $payload): ?ResourceModel
    {
        return (new CreateResourceRequest($this->apiClient, $payload))->perform();
    }

    /** @return ResourceModel[]|null */
    public function createResources(CreateResourcesPayload $payload): ?array
    {
        return (new CreateResourcesRequest($this->apiClient, $payload))->perform();
    }

    public function updateResource(string $id): void
    {
        throw new Exception("updateResource is not yet implemented.");
    }

    /** @return RepresentationModel[]|null */
    public function getResourceRepresentations(string $id): ?array
    {
        return (new GetResourceRepresentationsRequest($this->apiClient, $id))->data();
    }

    public function getResourceRepresentation(string $id): ?RepresentationModel
    {
        return (new GetResourceRepresentationRequest($this->apiClient, $id))->data();
    }

    /**
     * @return ResourceGroupModel[]|null
     */
    public function getResourceGroups(): ?array
    {
        return (new GetResourceGroupsRequest($this->apiClient))->data();
    }

    public function getResourceGroup(string $id): ?ResourceGroupModel
    {
        $group = array_filter($this->getResourceGroups(), function (ResourceGroupModel $group) use ($id): bool {
            return $group->getId() === $id;
        });

        return array_shift($group);
    }

    public function createResourceGroup(CreateResourceGroupPayload $payload): ?ResourceGroupModel
    {
        return (new CreateResourceGroupRequest($this->apiClient, $payload))->perform();
    }

    public static function parseWebHookResourceUpdate(\stdClass $json): ?WebhookUpdateModel
    {
        $payload = ResourceUpdatePayloadParser::parse($json);

        if (is_null($payload)) {
            return null;
        }

        return ResourceModelHelper::mapWebhookUpdatePayloadApiModelToWebhookUpdateModel($payload);
    }
}

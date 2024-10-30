<?php

namespace Coyote\Request;

use Coyote\ApiResponse\CreateResourceGroupApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\ResourceGroupModel;
use Coyote\Payload\CreateResourceGroupPayload;

class CreateResourceGroupRequest extends AbstractApiRequest
{
    private const PATH = '/resource_groups';

    private CreateResourceGroupPayload $payload;
    private InternalApiClient $apiClient;

    public function __construct(InternalApiClient $apiClient, CreateResourceGroupPayload $payload)
    {
        $this->apiClient = $apiClient;
        $this->payload = $payload;
    }

    public function perform(): ?ResourceGroupModel
    {
        try {
            $json = $this->apiClient->post(
                self::PATH,
                $this->marshallPayload(),
                [InternalApiClient::INCLUDE_ORG_ID => true]
            );
        } catch (\Exception $error) {
            self::logError(
                "Error creating resource group ({$this->payload->name}/{$this->payload->webhook_uri}): "
                . $error->getMessage()
            );
            return null;
        }

        if (is_null($json)) {
            self::logWarning(
                "Unexpected null response when creating resource group "
                . "({$this->payload->name}/{$this->payload->webhook_uri})"
            );
            return null;
        }

        $response = self::mapper()->mapObject($json, (new CreateResourceGroupApiResponse()));

        return $this->responseToResourceGroup($response);
    }

    private function responseToResourceGroup(CreateResourceGroupApiResponse $response): ResourceGroupModel
    {
        return new ResourceGroupModel($response->data);
    }

    /** @return mixed[] */
    private function marshallPayload(): array
    {
        return get_object_vars($this->payload);
    }
}

<?php

namespace Coyote\Request;

use Coyote\ApiResponse\GetResourceGroupsApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\ResourceGroupModel;
use stdClass;

class GetResourceGroupsRequest extends AbstractApiRequest
{
    private const PATH = '/resource_groups/';

    private InternalApiClient $client;

    public function __construct(InternalApiClient $client)
    {
        $this->client = $client;
    }

    /** @return ResourceGroupModel[]|null */
    public function data(): ?array
    {
        self::logDebug('Fetching resource groups');

        try {
            $json = $this->client->get(self::PATH, [InternalApiClient::INCLUDE_ORG_ID => true]);
        } catch (\Exception $error) {
            self::logError('Error fetching resource groups: ' . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning('Unexpected null response when fetching resource groups');
            return null;
        }

        return $this->mapResponseToResourceGroupModels($json);
    }

    /** @return ResourceGroupModel[] */
    private function mapResponseToResourceGroupModels(stdClass $json): array
    {
        $response = self::mapper()->mapObject($json, (new GetResourceGroupsApiResponse()));
        return $this->mapResourceGroupApiModelsToResourceGroupModels($response);
    }

    /** @return ResourceGroupModel[] */
    private function mapResourceGroupApiModelsToResourceGroupModels(GetResourceGroupsApiResponse $response): array
    {
        return array_map(function ($apiModel) {
            return new ResourceGroupModel($apiModel);
        }, $response->data);
    }
}

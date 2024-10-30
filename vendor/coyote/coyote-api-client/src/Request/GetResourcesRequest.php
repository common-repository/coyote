<?php

namespace Coyote\Request;

use Coyote\ApiModel\OrganizationApiModel;
use Coyote\ApiModel\ResourceApiModel;
use Coyote\ApiModel\ResourceRepresentationApiModel;
use Coyote\ApiResponse\GetResourcesApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\ResourceModel;
use stdClass;

class GetResourcesRequest extends AbstractApiRequest
{
    private const PATH = '/resources/';

    private InternalApiClient $client;

    public function __construct(InternalApiClient $client)
    {
        $this->client = $client;
    }

    /** @return ResourceModel[]|null */
    public function data(
        ?int $pageNumber = null,
        ?int $pageSize = null,
        ?string $filterString = null,
        ?string $filterScope = null
    ): ?array {
        try {
            $json = $this->client->get(self::PATH, [InternalApiClient::INCLUDE_ORG_ID => true]);
        } catch (\Exception $error) {
            self::logError("Error fetching resources: " . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning("Unexpected null response when fetching resources");
            return null;
        }

        return $this->mapResponseToResourceModels($json);
    }

    /** @return ResourceModel[] */
    private function mapResponseToResourceModels(stdClass $json): array
    {
        $response = self::mapper()->mapObject($json, (new GetResourcesApiResponse()));

        $organizationApiModel = $this->getOrganizationApiModel($response);
        $representationApiModels = $this->getRepresentationApiModels($response);

        return array_map(function (ResourceApiModel $model) use ($organizationApiModel, $representationApiModels) {
            return new ResourceModel($model, $organizationApiModel, $representationApiModels);
        }, $response->data);
    }

    private function getOrganizationApiModel(GetResourcesApiResponse $response): OrganizationApiModel
    {
        /** @var OrganizationApiModel[] $organizationApiData */
        $organizationApiData = array_filter($response->included, function ($data) {
            return get_class($data) === OrganizationApiModel::class;
        });

        return array_shift($organizationApiData);
    }

    /** @return ResourceRepresentationApiModel[] */
    private function getRepresentationApiModels(GetResourcesApiResponse $response): array
    {
        return self::mapper()->mapArray(array_filter($response->included, function ($data) {
            return $data->type === ResourceRepresentationApiModel::TYPE;
        }), new ResourceRepresentationApiModel());
    }
}

<?php

namespace Coyote\Request;

use Coyote\ApiModel\OrganizationApiModel;
use Coyote\ApiModel\ResourceRepresentationApiModel;
use Coyote\ApiResponse\GetResourceApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\ResourceModel;
use stdClass;

class GetResourceRequest extends AbstractApiRequest
{
    private const PATH = '/resources/%s';

    private InternalApiClient $client;

    private string $resource_id;

    public function __construct(InternalApiClient $client, string $resource_id)
    {
        $this->client = $client;
        $this->resource_id = $resource_id;
    }

    public function data(): ?ResourceModel
    {
        self::logDebug("Fetching resource {$this->resource_id}");

        try {
            $json = $this->client->get(sprintf(self::PATH, $this->resource_id));
        } catch (\Exception $error) {
            self::logError("Error fetching resource {$this->resource_id}: " . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning("Unexpected null response when fetching resource {$this->resource_id}");
            return null;
        }

        return $this->mapResponseToResourceModel($json);
    }

    private function mapResponseToResourceModel(stdClass $json): ResourceModel
    {
        $response = self::mapper()->mapObject($json, (new GetResourceApiResponse()));

        $organizationApiModel = $this->getOrganizationApiModel($response);
        $representationApiModels = $this->getRepresentationApiModels($response);

        return new ResourceModel($response->data, $organizationApiModel, $representationApiModels);
    }

    private function getOrganizationApiModel(GetResourceApiResponse $response): OrganizationApiModel
    {
        /** @var stdClass[] $organizationApiData */
        $organizationApiData = array_filter($response->included, function ($data) {
            return $data->type === OrganizationApiModel::TYPE;
        });

        $data = array_shift($organizationApiData) ?? new stdClass();

        return self::mapper()->mapObject($data, (new OrganizationApiModel()));
    }

    /** @return ResourceRepresentationApiModel[]
     */
    private function getRepresentationApiModels(GetResourceApiResponse $response): array
    {
        return self::mapper()->mapArray(array_filter($response->included, function ($data) {
            return $data->type === ResourceRepresentationApiModel::TYPE;
        }), new ResourceRepresentationApiModel());
    }
}

<?php

namespace Coyote\Request;

use Coyote\ApiResponse\GetResourceRepresentationsApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\RepresentationModel;
use stdClass;

class GetResourceRepresentationsRequest extends AbstractApiRequest
{
    private const PATH = '/resources/%s/representations';

    private InternalApiClient $client;
    private string $resource_id;

    public function __construct(InternalApiClient $client, string $resource_id)
    {
        $this->client = $client;
        $this->resource_id = $resource_id;
    }

    /** @return RepresentationModel[]|null */
    public function data(): ?array
    {
        self::logDebug("Fetching representations for {$this->resource_id}");

        try {
            $json = $this->client->get(sprintf(self::PATH, $this->resource_id));
        } catch (\Exception $error) {
            self::logError(
                "Error fetching resource {$this->resource_id} representations: " . $error->getMessage()
            );
            return null;
        }

        if (is_null($json)) {
            self::logWarning(
                "Unexpected null response when fetching representations for resource {$this->resource_id}"
            );
            return null;
        }

        return $this->mapResponseToRepresentationModels($json);
    }

    /** @return RepresentationModel[] */
    private function mapResponseToRepresentationModels(stdClass $json): array
    {
        $response = self::mapper()->mapObject($json, (new GetResourceRepresentationsApiResponse()));
        return $this->mapRepresentationApiModelsToRepresentationModels($response);
    }

    /**
     * @param GetResourceRepresentationsApiResponse $response
     * @return RepresentationModel[]
     */
    private function mapRepresentationApiModelsToRepresentationModels(
        GetResourceRepresentationsApiResponse $response
    ): array {
        return array_map(function ($apiModel) {
            return new RepresentationModel($apiModel);
        }, $response->data);
    }
}

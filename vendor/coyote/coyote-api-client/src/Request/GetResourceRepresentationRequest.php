<?php

namespace Coyote\Request;

use Coyote\ApiResponse\GetResourceRepresentationApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\RepresentationModel;
use stdClass;

class GetResourceRepresentationRequest extends AbstractApiRequest
{
    private const PATH = '/representations/%s';

    private InternalApiClient $client;
    private string $representation_id;

    public function __construct(InternalApiClient $client, string $representation_id)
    {
        $this->client = $client;
        $this->representation_id = $representation_id;
    }

    /** @return RepresentationModel|null */
    public function data(): ?RepresentationModel
    {
        self::logDebug("Fetching representation {$this->representation_id}");

        try {
            $json = $this->client->get(sprintf(self::PATH, $this->representation_id));
        } catch (\Exception $error) {
            self::logError(
                "Error fetching representation {$this->representation_id}: " . $error->getMessage()
            );
            return null;
        }

        if (is_null($json)) {
            self::logWarning(
                "Unexpected null response when fetching representation {$this->representation_id}"
            );
            return null;
        }

        return $this->mapResponseToRepresentationModel($json);
    }

    private function mapResponseToRepresentationModel(stdClass $json): ?RepresentationModel
    {
        try {
            $response = self::validatingMapper()->mapObject($json, (new GetResourceRepresentationApiResponse()));
            return $this->mapRepresentationApiModelToRepresentationModel($response);
        } catch (\RuntimeException $e) {
            $this::logError("Unable to map API response to RepresentationModel");
            return null;
        }
    }

    private function mapRepresentationApiModelToRepresentationModel(
        GetResourceRepresentationApiResponse $response
    ): ?RepresentationModel
    {
        return new RepresentationModel($response->data);
    }
}

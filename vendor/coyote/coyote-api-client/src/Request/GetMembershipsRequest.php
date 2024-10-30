<?php

namespace Coyote\Request;

use Coyote\ApiModel\MembershipApiModel;
use Coyote\ApiResponse\GetMembershipsApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\MembershipModel;
use stdClass;

class GetMembershipsRequest extends AbstractApiRequest
{
    private const PATH = '/memberships/';

    private InternalApiClient $client;

    public function __construct(InternalApiClient $client)
    {
        $this->client = $client;
    }

    /** @return MembershipModel[]|null */
    public function data(): ?array
    {
        self::logDebug('Fetching memberships');

        try {
            $json = $this->client->get(self::PATH, [InternalApiClient::INCLUDE_ORG_ID => true]);
        } catch (\Exception $error) {
            self::logError('Error fetching memberships: ' . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning('Unexpected null response when fetching memberships');
            return null;
        }

        return $this->mapResponseToMembershipModels($json);
    }

    /** @return MembershipModel[] */
    private function mapResponseToMembershipModels(stdClass $json): array
    {
        $response = self::mapper()->mapObject($json, (new GetMembershipsApiResponse()));

        $memberships = $response->data;

        return array_map(function (MembershipApiModel $model): MembershipModel {
            $model->attributes->organization_id = strval($this->client->getOrganizationId() ?? 0);
            return new MembershipModel($model);
        }, $memberships);
    }
}

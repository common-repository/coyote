<?php

namespace Coyote\Request;

use Coyote\ApiModel\MembershipApiModel;
use Coyote\ApiModel\OrganizationApiModel;
use Coyote\ApiModel\ProfileApiModel;
use Coyote\ApiResponse\GetProfileApiResponse;
use Coyote\InternalApiClient;
use Coyote\Model\MembershipModel;
use Coyote\Model\OrganizationModel;
use Coyote\Model\ProfileModel;

use stdClass;

class GetProfileRequest extends AbstractApiRequest
{
    private const PATH = '/profile/';

    private InternalApiClient $client;

    public function __construct(InternalApiClient $client)
    {
        $this->client = $client;
    }

    /** @return ProfileModel|null */
    public function data(): ?ProfileModel
    {
        self::logDebug('Fetching profile');

        try {
            $json = $this->client->get(self::PATH);
        } catch (\Exception $error) {
            self::logError('Error fetching profile: ' . $error->getMessage());
            return null;
        }

        if (is_null($json)) {
            self::logWarning('Unexpected null response when fetching profile');
            return null;
        }

        return $this->mapResponseToProfileModel($json);
    }

    private function mapResponseToProfileModel(stdClass $json): ProfileModel
    {
        $response = self::mapper()->mapObject($json, (new GetProfileApiResponse()));

        $profileApiModel = $this->getProfileApiModel($response);
        $organizationApiModels = $this->getOrganizationApiModels($response);
        $membershipApiModels = $this->getMembershipApiModels($response);

        if (count($membershipApiModels) === 0) {
            $organizations = array_map(function (OrganizationApiModel $apiModel): OrganizationModel {
                return new OrganizationModel($apiModel);
            }, $organizationApiModels);

            $profileModel = new ProfileModel($profileApiModel, $organizationApiModels, []);
            $membershipModels = $this->getMembershipModelsBySeparateRequest($profileApiModel, $organizations);
            $profileModel->setMemberships($membershipModels);
            return $profileModel;
        }

        return new ProfileModel($profileApiModel, $organizationApiModels, $membershipApiModels);
    }

    /**
     * @param ProfileApiModel $profile
     * @param OrganizationModel[] $organizations
     * @return MembershipModel[]
     */
    private function getMembershipModelsBySeparateRequest(ProfileApiModel $profile, array $organizations): array
    {
        $membershipsRequest = new GetMembershipsRequest($this->client);
        $valid = array_filter(
            $membershipsRequest->data(),
            function (MembershipModel $membership) use ($profile): bool {
                return
                    $membership->isActive() &&
                    $membership->getFirstName() === $profile->attributes->first_name &&
                    $membership->getLastName() === $profile->attributes->last_name
                ;
            }
        );

        return array_map(function (MembershipModel $membership) use ($organizations): MembershipModel {
            $membership->setOrganisation($organizations);
            return $membership;
        }, $valid);
    }

    private function getProfileApiModel(GetProfileApiResponse $response): ProfileApiModel
    {
        return $response->data;
    }

    /** @return MembershipApiModel[] */
    private function getMembershipApiModels(GetProfileApiResponse $response): array
    {
        $memberships = array_filter($response->included, function (stdClass $item): bool {
            return $item->type === MembershipApiModel::TYPE;
        });

        return array_map(function (stdClass $item): MembershipApiModel {
            return self::mapper()->mapObject($item, new MembershipApiModel());
        }, $memberships);
    }

    /** @return OrganizationApiModel[] */
    private function getOrganizationApiModels(GetProfileApiResponse $response): array
    {
        $organizations = array_filter($response->included, function (stdClass $item): bool {
            return $item->type === OrganizationApiModel::TYPE;
        });

        return array_map(function (stdClass $item): OrganizationApiModel {
            return self::mapper()->mapObject($item, new OrganizationApiModel());
        }, $organizations);
    }
}

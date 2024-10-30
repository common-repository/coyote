<?php

namespace Coyote;

use Coyote\Model\MembershipModel;
use Coyote\Model\OrganizationModel;
use Coyote\Model\ProfileModel;
use Coyote\Model\ResourceGroupModel;
use Coyote\Model\ResourceModel;
use Coyote\Payload\CreateResourceGroupPayload;
use Coyote\Payload\CreateResourcePayload;
use Coyote\Payload\CreateResourcesPayload;
use Coyote\Request\CreateResourceGroupRequest;
use Coyote\Request\CreateResourceRequest;
use Coyote\Request\CreateResourcesRequest;
use Coyote\Request\GetMembershipsRequest;
use Coyote\Request\GetProfileRequest;
use Coyote\Request\GetResourceGroupsRequest;
use Coyote\Request\GetResourceRequest;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class CoyoteApiClientHelperFunctions
{
    public static function getResourceGroupByUri(
        string $endpoint,
        string $token,
        int $organizationId,
        string $uri
    ): ?ResourceGroupModel {
        $client = new InternalApiClient($endpoint, $token, $organizationId);
        $groups = (new GetResourceGroupsRequest($client))->data() ?? [];

        foreach ($groups as $group) {
            if ($group->getUri() === $uri) {
                return $group;
            }
        }

        return null;
    }

    public static function getResourceById(
        string $endpoint,
        string $token,
        int $organizationId,
        string $resourceId
    ): ?ResourceModel {
        $client = new InternalApiClient($endpoint, $token, $organizationId);
        return (new GetResourceRequest($client, $resourceId))->data();
    }

    public static function getProfile(string $endpoint, string $token): ?ProfileModel
    {
        $client = new InternalApiClient($endpoint, $token, null);
        return (new GetProfileRequest($client))->data();
    }

    /**
     * @param string $endpoint
     * @param string $token
     * @return MembershipModel[]|null
     */
    public static function getOrganizationMemberships(string $endpoint, string $token, int $organizationId): ?array
    {
        $client = new InternalApiClient($endpoint, $token, $organizationId);
        return (new GetMembershipsRequest($client))->data();
    }

    /** @return OrganizationModel[]|null */
    public static function getOrganizations(string $endpoint, string $token): ?array
    {
        $profile = static::getProfile($endpoint, $token);

        if (is_null($profile)) {
            return null;
        }

        return $profile->getOrganizations();
    }

    public static function getProfileName(string $endpoint, string $token): ?string
    {
        $profile = static::getProfile($endpoint, $token);

        if (is_null($profile)) {
            return null;
        }

        return $profile->getName();
    }

    public static function createResourceGroup(
        string $endpoint,
        string $token,
        int $organizationId,
        string $groupName,
        string $groupUri = null
    ): ?ResourceGroupModel {
        $client = new InternalApiClient($endpoint, $token, $organizationId);
        return (new CreateResourceGroupRequest(
            $client,
            new CreateResourceGroupPayload($groupName, $groupUri)
        ))->perform();
    }

    /**
     * @param string $endpoint
     * @param string $token
     * @param int $organizationId
     * @param CreateResourcePayload[] $resources
     * @return ResourceModel[]|null
     */
    public static function createResources(
        string $endpoint,
        string $token,
        int $organizationId,
        array $resources
    ): ?array {
        $client = new InternalApiClient($endpoint, $token, $organizationId);
        $payload = new CreateResourcesPayload();

        foreach ($resources as $resource) {
            $payload->addResource($resource);
        }

        return (new CreateResourcesRequest($client, $payload))->perform();
    }

    /**
     * @param string $endpoint
     * @param string $token
     * @param int $organizationId
     * @param CreateResourcePayload $payload
     * @return ResourceModel|null
     */
    public static function createResource(
        string $endpoint,
        string $token,
        int $organizationId,
        CreateResourcePayload $payload
    ): ?ResourceModel {
        $client = new InternalApiClient($endpoint, $token, $organizationId);
        return (new CreateResourceRequest($client, $payload))->perform();
    }

    /**
     * @return OrganizationModel[]
     */
    public static function getOrganizationsFilteredForMembershipRoles(
        string $endpoint,
        string $token,
        array $roles
    ): array {
        $profile = self::getProfile($endpoint, $token);

        if (is_null($profile)) {
            return [];
        }

        return array_reduce($profile->getMemberships(), function (array $set, MembershipModel $membership) use ($roles): array {
            if (in_array($membership->getRole(), $roles)) {
                $organization = $membership->getOrganization();
                if (!is_null($organization)) {
                    $set[] = $membership->getOrganization();
                }
            }

            return $set;
        }, []);
    }

    public static function getOrganisationMembershipWithName(
        string $endpoint,
        string $token,
        int $organizationId,
        string $name
    ): ?MembershipModel {
        $client = new InternalApiClient($endpoint, $token, $organizationId);
        $memberships = (new GetMembershipsRequest($client))->data();

        if (is_null($memberships)) {
            return null;
        }

        $matches = array_filter($memberships, function (MembershipModel $m) use ($name): bool {
            return $m->isActive() &&
                $m->getName() === $name;
        });

        if (count($matches) !== 1) {
            return null;
        }

        return array_pop($matches);
    }
}

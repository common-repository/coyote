<?php

namespace Coyote\ModelHelper;

use Coyote\ApiModel\OrganizationApiModel;
use Coyote\ApiModel\ResourceGroupApiModel;
use Coyote\ApiModel\ResourceRepresentationApiModel;
use Coyote\ApiModel\ResourceUpdateApiModel;
use Coyote\ApiPayload\WebhookUpdatePayloadApiModel;
use Coyote\ApiResponse\CreateResourceApiResponse;
use Coyote\Model\RepresentationModel;
use Coyote\Model\ResourceModel;
use Coyote\Model\ResourceUpdateModel;
use Coyote\Model\WebhookUpdateModel;
use JsonMapper\JsonMapperFactory;
use stdClass;

class ResourceModelHelper
{
    // TODO: These functions can probably be simplified by using an Interface

    public static function mapCreateResourceResponseToResourceModel(
        CreateResourceApiResponse $response
    ): ResourceModel {
        $organizationApiModel = self::getOrganizationApiModel($response->included);
        $representationApiModels = self::getRepresentationApiModels($response->included);

        return new ResourceModel($response->data, $organizationApiModel, $representationApiModels);
    }

    public static function mapWebhookUpdatePayloadApiModelToWebhookUpdateModel(
        WebhookUpdatePayloadApiModel $update
    ): WebhookUpdateModel
    {
        $organizationApiModel = self::getOrganizationApiModel($update->included);
        $representationApiModels = self::getRepresentationApiModels($update->included);
        $resourceGroupModels = self::getResourceGroupApiModels($update->included);

        return new WebhookUpdateModel(
            $update->data,
            $organizationApiModel,
            $representationApiModels,
            $resourceGroupModels
        );
    }

    public static function mapUpdateResourceResponseToResourceUpdateModel(
        ResourceUpdateApiModel $update
    ): ResourceUpdateModel
    {
        return new ResourceUpdateModel($update);
    }

    private static function getOrganizationApiModel(array $included): OrganizationApiModel
    {
        $mapper = (new JsonMapperFactory())->bestFit();

        $organizationApiModel = new OrganizationApiModel();

        /** @var stdClass[] $organizationApiData */
        $organizationApiData = array_filter($included, function ($data) {
            return $data->type === OrganizationApiModel::TYPE;
        });

        $data = array_shift($organizationApiData) ?? new stdClass();

        $mapper->mapObject($data, $organizationApiModel);

        return $organizationApiModel;
    }


    /** @return ResourceRepresentationApiModel[]
     */
    private static function getRepresentationApiModels(array $included): array
    {
        $mapper = (new JsonMapperFactory())->bestFit();

        return $mapper->mapArray(array_filter($included, function ($data) {
            return $data->type === ResourceRepresentationApiModel::TYPE;
        }), new ResourceRepresentationApiModel());
    }

    /** @return ResourceGroupApiModel[]
     */
    private static function getResourceGroupApiModels(array $included): array
    {
        $mapper = (new JsonMapperFactory())->bestFit();

        return $mapper->mapArray(array_filter($included, function ($data) {
            return $data->type === ResourceGroupApiModel::TYPE;
        }), new ResourceGroupApiModel());
    }

    /**
     * @param string $metum
     * @param RepresentationModel[] $representations
     * @return RepresentationModel|null
     */
    public static function getTopRepresentationByMetum(string $metum, array $representations): ?RepresentationModel
    {
        $byMetum = array_filter($representations, function (RepresentationModel $r) use ($metum): bool {
            return $r->getMetum() === $metum;
        });

        uasort($byMetum, function (RepresentationModel $a, RepresentationModel $b): int {
            $aO = $a->getOrdinality();
            $bO = $b->getOrdinality();

            if ($aO < $bO) {
                return -1;
            }

            if ($aO > $bO) {
                return 1;
            }

            return 0;
        });

        return array_pop($byMetum);
    }
}

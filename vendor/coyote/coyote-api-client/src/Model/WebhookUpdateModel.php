<?php

namespace Coyote\Model;

use Coyote\ApiModel\OrganizationApiModel;
use Coyote\ApiModel\ResourceApiModel;
use Coyote\ApiModel\ResourceGroupApiModel;
use Coyote\ApiModel\ResourceRepresentationApiModel;
use Coyote\ApiModel\WebhookUpdateApiModel;
use Coyote\ModelHelper\ResourceModelHelper;

class WebhookUpdateModel
{
    private string $id;
    private ?string $canonical_id;
    private string $name;
    private string $type;
    private string $source_uri;
    private array $host_uris;

    /** @var ResourceGroupModel[] */
    private array $resourceGroups;

    private ?OrganizationModel $organization;

    /** @var RepresentationModel[] */
    private array $representations;

    /**
     * @param WebhookUpdateApiModel $model
     * @param OrganizationApiModel|null $organizationApiModel
     * @param array<ResourceRepresentationApiModel> $representations
     * @param array<ResourceGroupApiModel> $resourceGroups
     */
    public function __construct(
        WebhookUpdateApiModel $model,
        ?OrganizationApiModel $organizationApiModel,
        array                 $representations,
        array                 $resourceGroups
    ) {
        $this->id = $model->id;
        $this->canonical_id = $model->attributes->canonical_id;
        $this->name = $model->attributes->name;
        $this->type = $model->attributes->resource_type;
        $this->source_uri = $model->attributes->source_uri;
        $this->host_uris = $model->attributes->host_uris;
        $this->organization = null;

        if (!is_null($organizationApiModel)) {
            $this->organization = new OrganizationModel($organizationApiModel);
        }

        $this->representations = array_map(function ($resourceRepresentationApiModel) {
            return new RepresentationModel($resourceRepresentationApiModel);
        }, $representations);

        $this->resourceGroups = array_map(function ($resourceGroupApiModel) {
            return new ResourceGroupModel($resourceGroupApiModel);
        }, $resourceGroups);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCanonicalId(): ?string
    {
        return $this->canonical_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSourceUri(): string
    {
        return $this->source_uri;
    }

    /**
     * @return string[]
     */
    public function getHostURIs(): array
    {
        return $this->host_uris;
    }

    public function getOrganization(): ?OrganizationModel
    {
        return $this->organization;
    }

    /**
     * @return ResourceGroupModel[]
     */
    public function getResourceGroups(): array
    {
        return $this->resourceGroups;
    }

    /**
     * @return RepresentationModel[]
     */
    public function getRepresentations(): array
    {
        return $this->representations;
    }

    public function getTopRepresentationByMetum(string $metum): ?RepresentationModel
    {
        return ResourceModelHelper::getTopRepresentationByMetum($metum, $this->representations);
    }
}

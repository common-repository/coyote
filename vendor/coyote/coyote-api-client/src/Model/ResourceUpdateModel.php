<?php

namespace Coyote\Model;

use Coyote\ApiModel\ResourceUpdateApiModel;

class ResourceUpdateModel
{
    private string $id;
    private ?string $canonical_id;
    private string $name;
    private string $type;
    private string $source_uri;

    /**
     * @param ResourceUpdateApiModel $model
     */
    public function __construct(
        ResourceUpdateApiModel $model
    ) {
        $this->id = $model->id;
        $this->canonical_id = $model->attributes->canonical_id;
        $this->name = $model->attributes->name;
        $this->type = $model->attributes->resource_type;
        $this->source_uri = $model->attributes->source_uri;
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSourceUri(): string
    {
        return $this->source_uri;
    }
}

<?php

namespace Coyote\ApiModel;

use Coyote\ApiModel\Partial\ResourceGroupAttributes;
use Coyote\ApiModel\Partial\ResourceGroupRelationships;

class ResourceGroupApiModel
{
    public const TYPE = 'resource_group';

    public string $id;
    public string $type;
    public ResourceGroupAttributes $attributes;
    public ResourceGroupRelationships $relationships;
}

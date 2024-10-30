<?php

namespace Coyote\ApiModel;

use Coyote\ApiModel\Partial\ResourceAttributes;
use Coyote\ApiModel\Partial\ResourceUpdateLinks;
use Coyote\ApiModel\Partial\ResourceUpdateRelationships;

class ResourceUpdateApiModel
{
    public string $id;
    public string $type;
    public ResourceAttributes $attributes;
    public ResourceUpdateRelationships $relationships;
    public ResourceUpdateLinks $links;
}

<?php

namespace Coyote\ApiModel;

use Coyote\ApiModel\Partial\ResourceAttributes;
use Coyote\ApiModel\Partial\ResourceUpdateLinks;
use Coyote\ApiModel\Partial\WebhookUpdateRelationships;

class WebhookUpdateApiModel
{
    public string $id;
    public string $type;
    public ResourceAttributes $attributes;
    public WebhookUpdateRelationships $relationships;
    public ResourceUpdateLinks $links;
}

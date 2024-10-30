<?php

namespace Coyote\ApiPayload;

use Coyote\ApiModel\OrganizationApiModel;
use Coyote\ApiModel\ResourceGroupApiModel;
use Coyote\ApiModel\ResourceRepresentationApiModel;
use Coyote\ApiModel\WebhookUpdateApiModel;

class WebhookUpdatePayloadApiModel
{
    public WebhookUpdateApiModel $data;

    /** @var array<OrganizationApiModel|ResourceRepresentationApiModel|ResourceGroupApiModel> */
    public array $included;
}
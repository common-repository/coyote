<?php

namespace Coyote\ApiResponse;

use Coyote\ApiModel\Partial\ApiMetaData;
use Coyote\ApiModel\Partial\ResourceUpdateLinks;
use Coyote\ApiModel\ResourceUpdateApiModel;

class UpdateResourceApiResponse
{
    public ResourceUpdateApiModel $data;

    public ResourceUpdateLinks $links;
    public ApiMetaData $jsonapi;
}

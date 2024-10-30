<?php

namespace Coyote\ApiResponse;

use Coyote\ApiModel\Partial\ApiMetaData;
use Coyote\ApiModel\Partial\ResourceLinks;
use Coyote\ApiModel\ResourceRepresentationApiModel;

class GetResourceRepresentationApiResponse
{
    public ResourceRepresentationApiModel $data;

    public ResourceLinks $links;
    public ApiMetaData $jsonapi;
}

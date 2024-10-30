<?php

namespace Coyote\Request;

use Coyote\Traits\JsonMapperTrait;
use Coyote\Traits\LoggerTrait;

abstract class AbstractApiRequest
{
    use LoggerTrait;
    use JsonMapperTrait;
}
<?php

namespace Tests;

use Coyote\ApiHelper\ResourceUpdatePayloadParser;
use Coyote\ApiPayload\WebhookUpdatePayloadApiModel;

class ResourceUpdatePayloadParserTest extends AbstractTestCase
{
    public function testParsingInvalidJsonReturnsNull(): void
    {
        $json = (object) ['foo' => 'bar', 'baz' => 'xuux'];
        $result = ResourceUpdatePayloadParser::parse($json);
        $this->assertNull($result);
    }

    public function testParsingValidPayloadReturnsModel(): void
    {
        $json = $this->getApiContract('resourceUpdatePayload');
        $result = ResourceUpdatePayloadParser::parse($json);
        $this->assertInstanceOf(WebhookUpdatePayloadApiModel::class, $result);
    }
}

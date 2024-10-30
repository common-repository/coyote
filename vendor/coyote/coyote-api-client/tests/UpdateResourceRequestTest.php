<?php

namespace Tests;

use Coyote\InternalApiClient;
use Coyote\Model\OrganizationModel;
use Coyote\Model\ResourceModel;
use Coyote\Model\ResourceUpdateModel;
use Coyote\Payload\UpdateResourcePayload;
use Coyote\Request\UpdateResourceRequest;
use PAC_Vendor\GuzzleHttp\Psr7\Response;
use stdClass;

/**
 * @covers \Coyote\Request\UpdateResourceRequest
 */
class UpdateResourceRequestTest extends AbstractTestCase
{
    private stdClass $contract;
    private UpdateResourcePayload $defaultPayload;

    public function setUp(): void
    {
        $this->defaultPayload = new UpdateResourcePayload(12345, 'Name', 'https://some-uri.com');

        $this->responses = [
            new Response(200, ['Content-Type' => 'application/json'], $this->getApiContractJson('updateResource'))
        ];

        $this->contract = $this->getApiContract('updateResource');

        parent::setUp();
    }

    private function doRequest(?array $responses = null): ?ResourceUpdateModel
    {
        if (!is_null($responses)) {
            $this->setResponses($responses);
        }

        $client = new InternalApiClient('', '', null, $this->client);
        return (new UpdateResourceRequest($client, $this->defaultPayload))->perform();
    }

    public function testInvalidResponseMapsToNull(): void
    {
        $response = $this->doRequest([new Response(404)]);
        $this->assertNull($response);
    }

    public function testValidResponseMapsToResourceModel(): void
    {
        $response = $this->doRequest();
        $this->assertNotNull($response);
        $this->assertInstanceOf(ResourceUpdateModel::class, $response);
    }

    public function testResourceIdIsAvailable(): void
    {
        $response = $this->doRequest();
        $this->assertEquals(
            $response->getId(),
            $this->contract->data->id
        );
    }
}

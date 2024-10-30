<?php

namespace Tests;

class CoyoteApiClientTest extends AbstractTestCase
{
    public function testClientConstructsWithoutOrganizationId(): void
    {
        $subject = new \Coyote\CoyoteApiClient('endpoint', 'token');
        $this->assertInstanceOf(\Coyote\CoyoteApiClient::class, $subject);
    }

    public function testClientConstructsWithOrganizationId(): void
    {
        $subject = new \Coyote\CoyoteApiClient('endpoint', 'token', 1);
        $this->assertInstanceOf(\Coyote\CoyoteApiClient::class, $subject);
    }
}
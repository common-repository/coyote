<?php

namespace Coyote\Payload;

class UpdateResourcePayload
{
    public string $id;
    public string $name;
    public string $source_uri;

    public ?string $resource_group_id = null;

    /** @var string[]|null */
    public ?array $host_uris = null;

    public string $resource_type = 'image';
    public string $language = 'en';

    public function __construct(
        string $id,
        string $name,
        string $uri,
        string $resource_group_id = null,
        string $host_uri = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->source_uri = $uri;

        if (!is_null($resource_group_id)) {
            $this->resource_group_id = $resource_group_id;
        }

        if (!is_null($host_uri)) {
            $this->host_uris = [$host_uri];
        }
    }
}

<?php

namespace Coyote\ApiModel\Partial;

class WebhookUpdateRelationships
{
    public SingleOrganizationRelationship $organization;
    public RepresentationRelationship $representations;
    public ResourceGroupRelationship $resource_groups;
}

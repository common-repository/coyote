<?php

namespace Coyote;

use Coyote\DB\ResourceRecord;
use Coyote\Model\ResourceModel;
use Coyote\Payload\CreateResourcePayload;
use Coyote\Payload\CreateResourcesPayload;
use WP_Post;

if (!defined('WPINC')) {
    exit;
}

class WordPressHelper
{
    public static function getSrcAndImageData(WP_Post $post, bool $fetch = false): array
    {
        $helper = new ContentHelper($post->post_content);
        $images = $helper->getImages();

        $imageMap = [];
        $missing = [];

        $hostUri = get_permalink($post);

        foreach ($images as $image) {
            $image = new WordPressImage($image);
            $image->setHostUri($hostUri);
            $key = $image->getAttachmentId() ?? $image->getSrc();
            $hash = sha1($image->getUrl());
            $resource = DB::getRecordByHash($hash);

            if (is_null($resource)) {
                if ($fetch) {
                    $missing[$key] = $image;
                }
                continue;
            }

            $imageMap[$key] = [
                'coyoteId' => $resource->getResourceId(),
                'alt' => esc_html($resource->getCoyoteDescription())
            ];
        }

        if (!$fetch) {
            return $imageMap;
        }

        foreach ($missing as $key => $image) {
            $payload = WordPressHelper::mapWordPressImageToCreateResourcePayload($image);
            $resource = WordPressCoyoteApiClient::createResource($payload);

            if (is_null($resource)) {
                continue;
            }

            $representation = $resource->getTopRepresentationByMetum(PluginConfiguration::METUM);
            $representation = is_null($representation) ? '' : $representation->getText();

            $record = DB::insertRecord(
                sha1($resource->getSourceUri()),
                $resource->getSourceUri(),
                $image->getAlt(),
                $resource->getId(),
                $representation,
            );

            $imageMap[$key] = [
                'coyoteId' => $record->getResourceId(),
                'alt' => esc_html($record->getCoyoteDescription())
            ];
        }

        return $imageMap;
    }

    private static function createPayload(WordPressImage $image): CreateResourcePayload
    {
        $payload = new CreateResourcePayload(
            $image->getCaption() ?? $image->getUrl(),
            $image->getUrl(),
            PluginConfiguration::getApiResourceGroupId(),
            $image->getHostUri()
        );

        $alt = $image->getAlt();

        if ($alt !== '') {
            $payload->addRepresentation($alt, PluginConfiguration::METUM);
        }

        return $payload;
    }

    public static function getResourceForWordPressImage(
        WordPressImage $image,
        bool           $fetchFromApiIfMissing = true
    ): ?ResourceRecord {
        $record = DB::getRecordByHash(sha1($image->getUrl()));

        if (!is_null($record)) {
            return $record;
        }

        if (!$fetchFromApiIfMissing) {
            return null;
        }

        $resource = WordPressCoyoteApiClient::createResource(self::mapWordPressImageToCreateResourcePayload($image));

        if (is_null($resource)) {
            return null;
        }

        $representation = $resource->getTopRepresentationByMetum(PluginConfiguration::METUM);
        $representation = is_null($representation) ? '' : $representation->getText();

        return DB::insertRecord(
            sha1($resource->getSourceUri()),
            $resource->getSourceUri(),
            $image->getAlt(),
            $resource->getId(),
            $representation,
        );
    }

    public static function mapWordPressImageToCreateResourcePayload(WordPressImage $image): CreateResourcePayload
    {
        $payload = new CreateResourcePayload(
            $image->getCaption() ?? $image->getUrl(),
            $image->getUrl(),
            PluginConfiguration::getApiResourceGroupId(),
            $image->getHostUri()
        );

        $alt = $image->getAlt();

        if (!empty($alt)) {
            $payload->addRepresentation($image->getAlt(), PluginConfiguration::getMetum());
        }

        return $payload;
    }

    public static function setImageAlts(string $postID, string $postContent, bool $fetchFromApiIfMissing = true): string
    {
        $helper = new ContentHelper($postContent);
        $images = $helper->getImages();
        $permalink = get_permalink($postID);

        $imageMap = [];
        $missingImages = [];
        $payload = new CreateResourcesPayload();

        foreach ($images as $image) {
            $image = new WordPressImage($image);
            $src = $image->getSrc();
            $url = $image->getUrl();
            $hash = sha1($url);
            $resource = DB::getRecordByHash($hash);

            if (!is_null($resource)) {
                $imageMap[$src] = $resource->getCoyoteDescription();
                continue;
            }

            if ($fetchFromApiIfMissing) {
                $missingImages[$url] = ['alt' => $image->getAlt(), 'src' => $src];

                /*  Resources require a hostUri where available  */
                $image->setHostUri($permalink);
                $payload->addResource(self::createPayload($image));
            }
        }

        // if $missingImages contains items, it implies fetching from the API
        if (count($missingImages) > 0) {
            $imageMap = self::fetchImagesFromApi($imageMap, $missingImages, $payload);
        }

        return $helper->setImageAlts($imageMap);
    }

    private static function fetchImagesFromApi(
        array                  $imageMap,
        array                  $missingImages,
        CreateResourcesPayload $payload
    ): array {
        $response = WordPressCoyoteApiClient::createResources($payload);

        if (is_null($response)) {
            return $imageMap;
        }

        foreach ($response as $resourceModel) {
            $uri = $resourceModel->getSourceUri();
            $hash = sha1($uri);
            $originalSrc = $missingImages[$uri]['src'];
            $originalAlt = $missingImages[$uri]['alt'];

            $coyoteId = $resourceModel->getId();
            $representation = $resourceModel->getTopRepresentationByMetum(PluginConfiguration::METUM);

            if (is_null($representation)) {
                DB::InsertRecord($hash, $uri, $originalAlt, $coyoteId, '');
                continue;
            }

            $coyoteAlt = $representation->getText();

            DB::InsertRecord($hash, $uri, $originalAlt, $coyoteId, $coyoteAlt);
            $imageMap[$originalSrc] = $coyoteAlt;
        }

        return $imageMap;
    }

    public static function getMediaTemplateData(): string
    {
        global $post;

        if (empty($post)) {
            return '';
        }

        if (empty($post->post_type)) {
            return '';
        }

        $prefix = implode('/', [
            PluginConfiguration::getApiEndPoint(),
            'organizations',
            PluginConfiguration::getApiOrganizationId()
        ]);

        $mapping = WordPressHelper::getSrcAndImageData($post, PluginConfiguration::isEnabled());
        $jsonMapping = json_encode($mapping, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<js
<script>
    window.coyote = {};
    window.coyote.classic_editor = {
        postId: "$post->ID",
        prefix: "$prefix",
        mapping: $jsonMapping
    };
</script>
js;
    }

    /**
     * @param int $attachmentID
     * @return string
     */
    public static function getAttachmentURL(int $attachmentID): ?string
    {
        $url = wp_get_attachment_url($attachmentID);

        $parts = wp_parse_url($url);

        if ($parts === false) {
            return null;
        }

        return '//' . $parts['host'] . esc_url($parts['path']);
    }

    /**
     * Check if the current user has administrator privileges
     * @return bool
     */
    public static function userIsAdmin(): bool
    {
        return current_user_can('administrator');
    }

    /**
     * @param WordPressImage[] $images
     * @param ResourceModel[] $resources
     * @return array<string, ResourceModel>
     */
    public static function getNewlyCreatedResources(array $images, array $resources): array
    {
        $altText = [];
        $records = [];

        foreach ($images as $image) {
            $altText[$image->getUrl()] = $image->getAlt();
        }

        foreach ($resources as $resource) {
            $alt = $altText[$resource->getSourceUri()];
            $records[$alt] = $resource;
        }

        return $records;
    }
}

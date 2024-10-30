<?php

declare(strict_types=1);

namespace Coyote;

use Coyote\ContentHelper\Image;
use IvoPetkov\HTML5DOMDocument;

class ContentHelper
{
    private \IvoPetkov\HTML5DOMDocument $dom;
    private const LEFT = 0;
    private const RIGHT = 1;

    /**
     * @throws \Exception
     */
    public function __construct(string $html)
    {
        $this->dom = new \IvoPetkov\HTML5DOMDocument();
        $this->dom->loadHTML($html, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
    }

    /**
     * @param callable|null $srcFilter optional (string) => bool filter for the src attribute
     *
     * @return Image[]
        * Given a html string, either an entire or partial document, return all image elements
        * (these can be DOMElement instances)
    */
    public function getImages(?callable $srcFilter = null): array
    {
        $images = $this->dom->getElementsByTagName('img');
        $imageObjects = [];
        foreach ($images as $image) {
            $src = $image->getAttribute('src');

            if (!$src) {
                continue;
            }

            if (!is_null($srcFilter) && !$srcFilter($src)) {
                continue;
            }

            $alt = $image->getAttribute('alt');
            $class = $image->getAttribute('class');
            $contentBefore = $this->findTextContent($image->previousSibling, '', self::LEFT);
            $contentAfter = $this->findTextContent($image->nextSibling, '', self::RIGHT);
            $figureCaption = $this->getFigureCaption($image);

            $contentImage = new Image($src, $alt, $class, $contentBefore, $contentAfter, $figureCaption);
            array_push($imageObjects, $contentImage);
        }

        return $imageObjects;
    }

    private function getFigureCaption(\DOMNode $node, bool $recursing = false): ?string
    {
        if (!$recursing) {
            $parent = $node->parentNode;

            if (strtolower($parent->nodeName) !== 'figure') {
                return null;
            }

            $node = $parent->firstChild;
        }

        if (strtolower($node->nodeName) === 'figcaption') {
            return $node->nodeValue;
        }

        $next = $node->nextSibling;

        if (is_null($next)) {
            return null;
        }

        return $this->getFigureCaption($next, true);
    }

    private function findTextContent(?\DOMNode $node, string $textContent, int $direction): string
    {
        if (is_null($node)) {
            return trim($textContent);
        }

        if ($node->nodeType === XML_ELEMENT_NODE) {
            return trim($textContent);
        }

        $nextNode = $direction === self::LEFT ? $node->previousSibling : $node->nextSibling;

        if ($node->nodeType === XML_TEXT_NODE) {
            $textContent = join(' ', $direction === self::LEFT ?
                [$node->nodeValue, $textContent] : [$textContent, $node->nodeValue]);
        }

        return $this->findTextContent($nextNode, $textContent, $direction);
    }

    /*
        Given a src string, and an alt string
        set the alt text equal to the provided alt text
        for each element with the same src
    */
    private function setImageAlt(string $src, string $alt): void
    {

        $xpath = new \DOMXPATH($this->dom);
        $images = $xpath->evaluate("//img[@src=\"{$src}\"]");

        if (is_null($images) || $images === false) {
            return;
        }

        foreach ($images as $image) {
            $image->removeAttribute('alt');
            $image->setAttribute('alt', $alt);
        }
    }

    /*
        Given a html string, either an entire or partial document,
        and a map of image sources and their alt attributes,
        set the alt text for each image and return the modified html
    */
    public function setImageAlts($map): string
    {
        foreach ($map as $src => $alt) {
            $this->setImageAlt($src, $alt);
        }

        return $this->dom->saveHTML();
    }
}

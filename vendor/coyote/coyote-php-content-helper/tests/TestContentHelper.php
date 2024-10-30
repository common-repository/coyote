<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

require_once('vendor/autoload.php');

use Coyote\ContentHelper;

class TestContentHelper extends TestCase
{
    public function testCreateContentHelper()
    {
        $helper = new ContentHelper("<img src='foo.jpg'>");
        $this->assertInstanceOf(ContentHelper::class, $helper);
    }

    public function testWellFormedHTML()
    {
        $wellHTML = "<!DOCTYPE html>\n<html><head><title>Test</title></head><body><img src=\"foo.jpg\"></body></html>";
        $helper = new ContentHelper($wellHTML);
        $map = [];
        $html = $helper->setImageAlts($map);
        $this->assertEquals($wellHTML, $html);
    }

    public function testMalformedHTML()
    {
        $malHTML = "<img src=\"foo.jpg\"";
        $helper = new ContentHelper($malHTML);
        $map = [];
        $html = $helper->setImageAlts($map);
        $this->assertStringContainsString("<img src=\"foo.jpg\">", $html);
    }

    public function testLoadSectionInContentHelper()
    {
        $helper = new ContentHelper("<footer></footer>");
        $this->assertInstanceOf(ContentHelper::class, $helper);
    }

    public function testGetImages()
    {
        $helper = new ContentHelper("<img src='foo.jpg'>");
        $images = $helper->getImages();
        $this->assertCount(1, $images);
    }

    public function testIgnoreNoSrcImage()
    {
        $helper = new ContentHelper("<img>");
        $images = $helper->getImages();
        $this->assertCount(0, $images);
    }

    public function testGetImagesWithSrc()
    {
        $helper = new ContentHelper("<img src='foo.jpg'>");
        $images = $helper->getImages();
        $src = $images[0]->getSrc();
        $this->assertEquals('foo.jpg', $src);
    }

    public function testNoImagesAtAll()
    {
        $helper = new ContentHelper("<html></html>");
        $images = $helper->getImages();
        $this->assertCount(0, $images);
    }

    public function testReturnsImageInstanceArray()
    {
        $helper = new ContentHelper("<img src='foo.jpg'>");
        $images = $helper->getImages();
        $this->assertInstanceOf(ContentHelper\Image::class, array_pop($images));
    }

    public function testSetAltWithMap()
    {
        $helper = new ContentHelper("<img src='https://images.unsplash.com/photo-1653989830251-5455245ae4e9?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1227&q=80'><img src='boo.jpg'><img src='notthere.jpg'>");
        $map = ['https://images.unsplash.com/photo-1653989830251-5455245ae4e9?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1227&q=80' => 'one', 'boo.jpg' => 'two', 'fail.jpg' => 'shouldnt work'];

        $newText = $helper->setImageAlts($map);
        $newHelper = new ContentHelper($newText);

        $images = $newHelper->getImages();

        $alt1 = $images[0]->getAlt();
        $alt2 = $images[1]->getAlt();
        $alt3 = $images[2]->getAlt();

        $this->assertEquals('one', $alt1);
        $this->assertEquals('two', $alt2);
        $this->assertNull($alt3);
    }

    public function testSetAltWithEmptyMap()
    {
        $helper = new ContentHelper("<img src='foo.jpg'><img src='boo.jpg' alt='two'><img src='notthere.jpg'>");
        $map = [];

        $newText = $helper->setImageAlts($map);
        $newHelper = new ContentHelper($newText);

        $images = $newHelper->getImages();

        $alt1 = $images[0]->getAlt();
        $alt2 = $images[1]->getAlt();
        $alt3 = $images[2]->getAlt();

        $this->assertNull($alt1);
        $this->assertEquals('two', $alt2);
        $this->assertNull($alt3);
    }

    public function testNoSrcInHTMLMatchingMapSrc()
    {
        $helper = new ContentHelper("<img src='foo.jpg'>");
        $map = ['fo.jpg' => 'one', 'bo.jpg' => 'two', 'fail.jpg' => 'shouldnt work'];

        $newText = $helper->setImageAlts($map);
        $newHelper = new ContentHelper($newText);

        $images = $newHelper->getImages();

        $this->assertCount(1, $images);
    }

    public function testChangeAltFromCurrentWithMap()
    {
        $helper = new ContentHelper("<img src='foo.jpg' alt='three'><img src='boo.jpg' alt='four'><img src='notthere.jpg' alt='five'>");
        $map = ['foo.jpg' => 'one', 'boo.jpg' => 'two', 'fail.jpg' => 'shouldnt work'];

        $newText = $helper->setImageAlts($map);
        $newHelper = new ContentHelper($newText);

        $images = $newHelper->getImages();

        $alt1 = $images[0]->getAlt();
        $alt2 = $images[1]->getAlt();
        $alt3 = $images[2]->getAlt();

        $this->assertEquals('one', $alt1);
        $this->assertEquals('two', $alt2);
        $this->assertEquals('five', $alt3);
    }

    public function testChangeElementsAltWithSameSrc()
    {
        $helper = new ContentHelper(
            "<img src='foo.jpg' alt='three'><img src='foo.jpg' alt='four'><img src='notthere.jpg' alt='five'>"
        );
        $map = ['foo.jpg' => 'one', 'boo.jpg' => 'two', 'fail.jpg' => 'shouldnt work'];

        $newText = $helper->setImageAlts($map);
        $newHelper = new ContentHelper($newText);

        $images = $newHelper->getImages();

        $alt1 = $images[0]->getAlt();
        $alt2 = $images[1]->getAlt();
        $alt3 = $images[2]->getAlt();

        $this->assertEquals('one', $alt1);
        $this->assertEquals('one', $alt2);
        $this->assertEquals('five', $alt3);
    }

    public function testChangeElementsAltWithNoImageElements()
    {
        $helper = new ContentHelper("<html></html>");
        $map = ['foo.jpg' => 'one', 'boo.jpg' => 'two', 'fail.jpg' => 'shouldnt work'];

        $newText = $helper->setImageAlts($map);
        $newHelper = new ContentHelper($newText);
        $images = $newHelper->getImages();

        $this->assertCount(0, $images);
    }

    /**
     * @dataProvider beforeAfterData
     */
    public function testParseBeforeAfterContent($expectedResult, $input)
    {
        $helper = new ContentHelper($input);
        $images = $helper->getImages();

        $beforeAfterArr = [$images[0]->getContentBefore(), $images[0]->getContentAfter()];

        $this->assertSame($expectedResult, $beforeAfterArr);
    }

    /**
     * @dataProvider figureCaptionData
     * @param $output
     * @param $input
     */
    public function testFigureCaptionIsParsed($output, $input)
    {
        $helper = new ContentHelper($input);
        $images = $helper->getImages();
        $image = array_pop($images);
        $this->assertSame($image->getFigureCaption(), $output);
    }

    private function figureCaptionData(): array
    {
        $figureWithFigCaption = <<<FIGURE
        <figure>
<figcaption>This is a captioned image.</figcaption>
<img src="some-src" alt="some-alt">
</figure>
FIGURE;

        $noCaptionCase = [null, '<div><img src="test"></div>'];
        $captionCase = ['some-caption', '<figure><img src="test"><figcaption>some-caption</figcaption></figure>'];
        $captionCase2 = ['This is a captioned image.', $figureWithFigCaption];
        $noFigureCase = [null, '<img src="test"><figcaption>some-caption</figcaption>'];
        $noFigCaptionCase = [null, '<figure><img src="test"></figure>'];
        $emptyFigCaptionCase = ['', '<figure><img src="test"><figcaption></figcaption></figure>'];

        return [$noCaptionCase, $captionCase, $captionCase2, $noFigureCase, $noFigCaptionCase, $emptyFigCaptionCase];
    }

    private function beforeAfterData(): array
    {
        return [
            [
                ['',''],
                '<div><img src="test"></div>',
            ],
            [
                ['before',''],
                '<div>before<img src="test"></div>',
            ],
            [
                ['','after'],
                '<div><img src="test">after</div>',
            ],
            [
                ['before','after'],
                '<div>before<img src="test">after</div>'
            ],
            [
                ['This is extra text before',''],
                '<div>This is extra text<!-- Comment -->before<img src="test"></div>',
            ],
            [
                ['[caption]', 'This is a test caption[/caption]'],
                '<div>[caption]<img src="test">This is a test caption[/caption]</div>',
            ],
        ];
    }

    /**
     * @dataProvider complexUrlData
     */
    public function testComplexUrls($expectedResult, $input)
    {
        $helper = new ContentHelper($input);
        $images = $helper->getImages();

        $this->assertEquals($expectedResult, $images[0]->getSrc());
    }

    private function complexUrlData(): array
    {
        return [
            [
                'https://images.unsplash.com/photo-1653989830251-5455245ae4e9?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1227&q=80',
                '<div><img src="https://images.unsplash.com/photo-1653989830251-5455245ae4e9?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1227&q=80"></div>'
            ]
        ];
    }

    private function srcFilterData(): array
    {
        return [
            [1, '<img src="https://example.org/foo.png">', null],
            [1, '<img src="https://example.org/foo.png">', function ($src):bool { return preg_match('/^https?:\/\//', $src) === 1; }],
            [0, '<img src="//example.org/foo.png">', function ($src):bool { return preg_match('/^https?:\/\//', $src) === 1; }],
            [1, '<img src="//example.org/foo.png">', function ($src):bool { return preg_match('/^\/\//', $src) === 1; }],
            [0, '<img src="../foo.png">', function ($src):bool { return preg_match('/^\/\//', $src) === 1; }],
        ];
    }

    /**
     * @dataProvider srcFilterData
     */
    public function testGetImagesWithSrcFilter($expectedResult, $input, $filter)
    {
        $helper = new ContentHelper($input);
        $images = $helper->getImages($filter);
        $this->assertCount($expectedResult, $images);
    }
}

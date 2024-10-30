# Coyote PHP Content Helper

A composer package PHP helper for the Coyote Drupal plugin, and Coyote Wordpress plugin. 

## Overview

The purpose of the package is to parse HTML content within string, and put the necessary image attributes for Coyote plugins into an abstracted image object. The content helper also has the functionality of changing the alt text of images based on the src of the images by parsing the HTML content within a string. If the HTML within the string is malformed then no image elements can be found when parsed.


## Usage

To use the ContentHelper functions for gathering images a ContentHelper object must first be constructed, with input of a string of fully formed HTML.

```
$helper = new ContentHelper($wellFormedHTML);
```

### Get an array of Images

You can call the getImages function to get an array of Image objects.
```
$helper = new ContentHelper($wellFormedHTML);
$imageArray = $helper->getImages();
```

### Setting alt text for images

There are 2 functions for setting the alt text of an image

1. setImageAlt(string $src, string $alt)
2. setImageAlts($map)

For setImageAlt given a src string, and an string alt, the alt text for each image with that src will be changed to the given alt.
This will return the newly modified HTML string.

```
$helper = new ContentHelper($wellFormedHTML);
$src = "foo.jpg";
$newAlt = "This is new alt test";
$newHTML = $helper->setImageAlt($src,$alt);
```

For setImageAlts given a map with the key being the src, and the value being the alt it will traverse the map and change the alt text for each src given to the alt text value and return the newly modified HTML string.

```
$helper = new ContentHelper($wellFormedHTML);
$map = ['foo.jpg'=>'New Alt for Foo','test.jpg'=>'New Alt for Test','example.jpg'=>'New Alt for Example'];
$newHTML = $helper->setImageAlts($map);
```
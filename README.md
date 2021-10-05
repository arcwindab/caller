# ArcWind Caller
[![Latest Stable Version](https://poser.pugx.org/arcwindab/caller/v/stable.svg)](https://packagist.org/packages/arcwindab/caller)
[![Latest Unstable Version](https://poser.pugx.org/arcwindab/caller/v/unstable.svg)](https://packagist.org/packages/arcwindab/caller)
[![Build Status](https://travis-ci.org/arcwindab/caller.svg)](https://travis-ci.org/arcwindab/caller)  

[![Total Downloads](https://poser.pugx.org/arcwindab/caller/downloads)](https://packagist.org/packages/arcwindab/caller)
[![GitHub issues open](https://img.shields.io/github/issues/arcwindab/caller.svg)](https://github.com/arcwindab/caller/issues)

[![License](https://poser.pugx.org/arcwindab/caller/license.svg)](https://packagist.org/packages/arcwindab/caller)

## Disclaimer
In the words of Abraham Lincoln:
> Pardon my French

My English, and technical terms in code, is not very good - I'm not a native speaker.  
Sorry for any confusion that may occur.

## Install
ArcWind Caller is available on [Packagist](https://packagist.org/packages/arcwindab/caller) and installation via Composer is the recommended way to install ArcWind Caller. Just add this line to your composer.json file:
```
"arcwindab/caller": "@dev"
```
or run
```
composer require arcwindab/caller
```

## Run
```
<?php
//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing my own user agent
$caller = new arcwindab\caller('My custom UA');
echo $caller->get('https://filesamples.com/samples/document/txt/sample1.txt');
```

<?php include_once __DIR__.'/../src/caller.class.php';

/** Initiate caller class
 */
$caller = new arcwindab\caller();


/** The simplest method get($url) send a 
 * GET-request and returns the content of that URL as a string.
 */
echo $caller->get('https://filesamples.com/samples/document/txt/sample1.txt');
<?php include_once __DIR__.'/../src/caller.class.php';

/** Initiate caller class
 */
$caller = new arcwindab\caller();
$caller->set_useragent('ArcWind/'.$caller->get_version());
$caller->set_post(true);

/** The simplest method get($url) send a 
 * POST-request and returns the content of that URL as a string.
 */
print_r($caller->get_contents($caller->debug_url, array('postvar' => 'test')));
print_r($caller->curl($caller->debug_url, array('postvar' => 'test')));
print_r($caller->file_get_contents($caller->debug_url, array('postvar' => 'test')));
print_r($caller->fopen($caller->debug_url, array('postvar' => 'test')));

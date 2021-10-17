<?php include_once __DIR__.'/../src/caller.class.php';

/** Initiate caller class
 */
$caller = new arcwindab\caller();
$caller->set_useragent('ArcWind/'.$caller->get_version());
$caller->set_ip('8.8.8.8');

/** The simplest method get($url) send a 
 * GET-request and returns the content of that URL as a string.
 */
echo $caller->get($caller->debug_url);

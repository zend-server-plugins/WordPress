<?php

namespace ZRayWordpress;

class Wordpress {
		
	public function beforeExit($context, &$storage) {
		$storage['generalInfo'][] = array('name'=>'WordPress Version','value'=>$GLOBALS['wp_version']);
		$storage['generalInfo'][] = array('name'=>'Debug Mode (WP_DEBUG)','value'=>WP_DEBUG ? 'On' : 'Off');
		$storage['generalInfo'][] = array('name'=>'Debug Log (WP_DEBUG_LOG)','value'=>WP_DEBUG_LOG ? 'On' : 'Off');
		$storage['generalInfo'][] = array('name'=>'Script Debug (SCRIPT_DEBUG)','value'=>SCRIPT_DEBUG ? 'On' : 'Off');
		$storage['generalInfo'][] = array('name'=>'Template','value'=>get_template());
		$storage['generalInfo'][] = array('name'=>'Template Directory','value'=>str_replace('\\','/',realpath(get_template_directory())));
		$storage['generalInfo'][] = array('name'=>'Doing Crons','value'=>get_transient( 'doing_cron' ) ? 'Yes' : 'No');
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
		    $storage['generalInfo'][] = array('name'=>'Save Queries (SAVEQUERIES)','value'=>SAVEQUERIES ? 'On' : 'Off');
		}
	}
	
}

$zre = new \ZRayExtension('WordPress');

$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$actionBaseUrl = $protocol . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];

$zre->setMetadata(array(
	'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
    'actionsBaseUrl' => $actionBaseUrl,
));

$zre->setEnabledAfter('wp_initial_constants');

$zre->traceFunction('wp_initial_constants',function(){}, function() use ($zre) {

	$zrayWordpress = new Wordpress();

	// Cache
	include 'WordpressCache.php';
	$wordpressCache = new WordpressCache();
	$zre->traceFunction('wp_cache_get', function(){}, array($wordpressCache, 'wpCacheGetExit'));
	$zre->traceFunction('wp_cache_close', function(){}, array($wordpressCache, 'beforeExit'));

	// Hooks
	include 'WordpressHooks.php';
	$wordpressHooks = new WordpressHooks($zre);
	$wordpressHooks->traceHooks();
	$zre->traceFunction('wp_cache_close', function(){}, array($wordpressHooks, 'beforeExit'));

	// Crons
	include 'WordpressCrons.php';
	$wordPressCrons = new WordpressCrons();
	$zre->traceFunction('wp_cache_close', function(){}, array($wordPressCrons, 'beforeExit'));

	// wp query
	include 'WordpressWpQuery.php';
	$wordpressWpQuery = new WordpressWpQuery();
	$zre->traceFunction('wp_cache_close', function(){}, array($wordpressWpQuery, 'beforeExit'));

	// plugins
	include 'WordpressPlugins.php';
	$wordpressPlugins = new WordpressPlugins();
	$zre->traceFunction('wp_cache_close', function(){}, array($wordpressPlugins, 'beforeExit'));
	
	
	$zre->traceFunction('wp_cache_close', function(){}, array($zrayWordpress, 'beforeExit'));
});

$zre->attachAction('runCron', 'ZRayWordpress\shutdown', function(){ 
    
    try {
        wp_schedule_single_event(time()-1, $_POST['hook'], array('ZRayAction' => true));
    } catch(Exception $e) {
        echo json_encode(array('success' => false));
    }
    $crons_arr = array();
    try{
        include_once 'WordpressCrons.php';
        $wordPressCrons = new WordpressCrons();
        $crons_arr = $wordPressCrons->getCrons();
    } catch(Exception $e) { }
    echo json_encode(array('success' => true, 'crons' => $crons_arr));
});

$zre->attachAction('runWPQuery', 'ZRayWordpress\shutdown', function(){ 
    
    if (! $result = new \WP_Query( $_POST['query'] )){
        echo json_encode(array('success' => false));
    }
    if(http_response_code() == 404){
        http_response_code (200);
    }
    echo json_encode(array('success' => true, 'result' => $result));
});
    
function shutdown() {}
if (isset($_POST['ZRayAction'])) {
    register_shutdown_function('ZRayWordpress\shutdown');
}
?>
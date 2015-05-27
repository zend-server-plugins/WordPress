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

$zre->setMetadata(array(
	'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
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

	
?>
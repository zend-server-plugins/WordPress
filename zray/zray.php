<?php

namespace ZRayWordpress;

class Wordpress {
	private $zre;
	private $_profilePlugins = array();
	private $_profileThemes = array();
	
	public function __construct(&$zre){
		$this->zre = $zre;
	}
	
	public function wpRunExit($context, &$storage) {
		//Plugins List
		$this->plugins=array();
		try{
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$apl=get_option('active_plugins');
			$plugins=get_plugins();
			$state_plugins=array();
			if(is_array($apl) && count($plugins)>0){
				foreach ($apl as $p){           
					if(isset($plugins[$p])){
						 array_push($state_plugins, $p);
					}           
				}
			}
			//Multisite plugins
			$mupl=get_mu_plugins();
			if(is_array($mupl) && count($plugins)>0){
				foreach ($mupl as $p => $v){
					$plugins[$p]=$v;
					array_push($state_plugins, $p);
				}
			}
			
			$swplugs=get_site_option('active_sitewide_plugins');
			if(is_array($swplugs) && count($plugins)>0){
				foreach ($swplugs as $p => $v){           
					if(isset($plugins[$p])){
						 array_push($state_plugins, $p);
					}
				}
			}
			if(count($plugins)>0){
				foreach($plugins as $p=>$plugin){
					$state='Inactive';
					if(in_array($p,$state_plugins)){
						$state='Active'; 
					}
					$this->plugins[] = array('name'=>$plugin['Name'],'version'=>$plugin['Version'],'state'=>$state,'path'=>$p,'loadtime'=>'0');
				}
			}
		}catch(Exception $e){
		}
		$pluginsTime=0;
		if (count($this->_profilePlugins)>0) {
			foreach($this->_profilePlugins as $name => $time){
				$found=false;
				$pluginsTime+=$time;
				foreach($this->plugins as $key => $plugin){
					if(strpos($plugin['path'] . DIRECTORY_SEPARATOR,$name)!==false){
						$this->plugins[$key]['loadtime']=$time;
						$found=true;
					}
				}
				if(!$found){
					$this->plugins[]=array('name'=>$name,'version'=>'?','state'=>'On','loadtime'=>$time);
				}
			}
		}
		$storage['plugins']=$this->plugins;
		// Store Plugins Stats
		$pluginsOtherChart=0;
		$pluginsArr=array();
		$others=array();
		if($pluginsTime>0){
			foreach($this->plugins as $plugin){
				if($plugin['loadtime']>=$pluginsTime*0.15){
					$pluginsArr[]=$plugin;
				}else{
					$others[]=$plugin;
					$pluginsOtherChart += $plugin['loadtime'];
				}
			}
		}
		
		$count=3;
		if(count($pluginsArr)<$count&&count($others)>0){
			usort($others, function($a, $b){
				return strcmp($b['loadtime'], $a['loadtime']);
			});
			if(count($others)<$count){
				$count=count($others);
			}
			foreach(array_slice($others, 0, $count) as $item){
				$pluginsArr[] = $item;
				$pluginsOtherChart-=$item['loadtime'];
			}
		}
		
		$storage['pluginsStats'] = $pluginsArr;
		
		if($pluginsOtherChart>0){
			$storage['pluginsStats'][]=array('name'=>'Others','loadtime'=>$pluginsOtherChart);
		}

		
		//General Info
		$storage['generalInfo'][] = array('name'=>'WordPress Version','value'=>$GLOBALS['wp_version']);
		$storage['generalInfo'][] = array('name'=>'Debug Mode (WP_DEBUG)','value'=>WP_DEBUG ? 'On' : 'Off');
		$storage['generalInfo'][] = array('name'=>'Debug Log (WP_DEBUG_LOG)','value'=>WP_DEBUG_LOG ? 'On' : 'Off');
		$storage['generalInfo'][] = array('name'=>'Script Debug (SCRIPT_DEBUG)','value'=>SCRIPT_DEBUG ? 'On' : 'Off');
		$storage['generalInfo'][] = array('name'=>'Template','value'=>get_template());
		$storage['generalInfo'][] = array('name'=>'Template Directory','value'=>str_replace('\\','/',realpath(get_template_directory())));
		$storage['generalInfo'][] = array('name'=>'Doing Crons','value'=>get_transient( 'doing_cron' ) ? 'Yes' : 'No');
		$storage['generalInfo'][] = array('name'=>'Plugins Directory','value'=>str_replace('\\','/',realpath(WP_PLUGIN_DIR)));
		$storage['generalInfo'][] = array('name'=>'Plugins Count','value'=>count($storage['plugins']));
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

	$zrayWordpress = new Wordpress($zre);

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

	$zre->traceFunction('wp_cache_close', function(){}, array($zrayWordpress, 'wpRunExit'));
});
	
	

?>
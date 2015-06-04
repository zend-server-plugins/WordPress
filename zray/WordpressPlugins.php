<?php

namespace ZRayWordpress;

class WordpressPlugins {
	
	private $_profilePlugins = array();
	
	public function beforeExit($context, &$storage) {
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
					if(!isset($plugin['path'])){
						continue;
					}
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
		
		$storage['generalInfo'][] = array('name'=>'Plugins Directory','value'=>str_replace('\\','/',realpath(WP_PLUGIN_DIR)));
		$storage['generalInfo'][] = array('name'=>'Plugins Count','value'=>count($storage['plugins']));		
	}
	
}

?>

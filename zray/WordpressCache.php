<?php

namespace ZRayWordpress;

class WordpressCache {
	
	private $_cache_hits = array();
	private $_cache_misses = array();
	private $_cache_pie_size_statistics = array();
	
	public function wpCacheGetExit($context, &$storage) {
		$group = $context['locals']['group'];
		$key = $context["functionArgs"][0];
	
		if ($context['locals']["found"]) {
			if (isset($this->_cache_hits[$group]) && isset($this->_cache_hits[$group][$key])) {
				$this->_cache_hits[$group][$key]++;
			} else {
				$this->_cache_hits[$group][$key] = 1;
			}
		} else {
			if (isset($this->_cache_misses[$group]) && isset($this->_cache_misses[$group][$key])) {
				$this->_cache_misses[$group][$key]++;
			} else {
				$this->_cache_misses[$group][$key] = 1;
			}
		}
	}
	
	public function beforeExit($context, &$storage){
		$this->storeCacheObjects($GLOBALS['wp_object_cache'], $storage);
		$this->storeHitsStatistics($GLOBALS['wp_object_cache'], $storage);
		$this->storeCachePieStatistics($storage);
	}
	
	private function storeCacheObjects($wp_object_cache, &$storage) {
		$data_array=array();
		foreach ($wp_object_cache->cache as $group => $group_items) {
			$group_size = 0;
			$group_hits = 0;
			$group_item_array=array();
			foreach($group_items as $group_item_name => $group_item) {
				 
				$item_size =  number_format( strlen( serialize( $group_item ) ) / 1024, 2 );
				$group_size += $item_size;
				 
				$hits = 0;
				if (isset($this->_cache_hits[$group][$group_item_name])) {
					$hits = $this->_cache_hits[$group][$group_item_name];
					$group_hits += $hits;
				}
				if(count($group_items)==1){
					$group_item_array = $hits;
				}else{
					$group_item_array[] = array('name' => $group_item_name, 'size' => $item_size , 'hits' => $hits);
				}
				 
			}
			$this->_cache_pie_size_statistics[$group] = floatval($group_size);
			// we lose temprorally $group_hits
			$data_array[] = array('name' => $group, 'size' => $group_size , 'hits' => $group_item_array);
		}
		$storage['cacheObjects'] = $data_array;
	}
	
	private function storeHitsStatistics($wp_object_cache, &$storage) {
		$total = 0;
		foreach ($this->_cache_pie_size_statistics as $count) {
			$total += $count;
		}
		// General hits/misses data
		$storage['cacheStats'] = array('hits' => $wp_object_cache->cache_hits, 'misses' => $wp_object_cache->cache_misses, 'totalSize' => $total);
	}
	
	private function storeCachePieStatistics(&$storage) {
		$total = 0;
		foreach ($this->_cache_pie_size_statistics as $count) {
			$total += $count;
		}
		$percent15 = $total * 0.15;
		$cachePieStats = array();
		$otherCount = 0;
		$others = array();
		foreach ($this->_cache_pie_size_statistics as $name => $value) {
			if ($value >= $percent15) {
				$cachePieStats[] = array('name' => $name, 'count' => $value);
			} else {
				$others[] = array('name' => $name, 'count' => $value);
				$otherCount += $value;
			}
		}
		$count=3;
		if(count($cachePieStats)<$count&&count($others)>0){
			usort($others, function($a, $b){
				return strcmp($b['count'], $a['count']);
			});
			if(count($others)<$count){
				$count=count($others);
			}
			foreach(array_slice($others, 0, $count) as $item){
				$cachePieStats[] = $item;
				$otherCount-=$item['count'];
			}
		}
		if ($otherCount > 0) {
				
			$cachePieStats[] = array('name' => 'Other', 'count' => $otherCount);
		}
		 
		$storage['cachePieStats'] = $cachePieStats;
	}
}

?>
<?php

	class ZWordPressPlugin extends ZAppsPlugin {
		
		public function resolveMVCEnter($context) {
			
		}
		
		private function getShortUrl() {
			$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : "";
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "";
			if (strstr($host, ":")) {
				$port = "";
			}			
			$path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
			
			if ($port && $port != 443 && $port != 80) {
				$url =  "{$host}:{$port}{$path}";
			} else {
				$url =  "{$host}{$path}";
			}
				
			return $url;
		}
		
		public function resolveMVCLeave($context) {
			
			if (!$this->resolved && defined('WPLANG') ) {
				$this->resolved = true;
				
				$shortUrl = $this->getShortUrl();
				$mvc =  array(
					$shortUrl						
				);
				
				$this->setRequestMVC($mvc);				
			}
		}		
		
		private $resolved = false;
	}
	
	$wpPlugin = new ZWordPressPlugin();
	$wpPlugin->setWatchedFunction("WP::main", array($wpPlugin, "resolveMVCEnter"), array($wpPlugin, "resolveMVCLeave"));
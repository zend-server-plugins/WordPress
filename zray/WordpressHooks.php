<?php

namespace ZRayWordpress;

class WordpressHooks {
	
	private $zre;
	private $_hooks = array();
	
	public function __construct(&$zre) {
		$this->zre = $zre;
	}
	
	public function beforeExit($context, &$storage) {
		$incId = 0;
		// Hooks List
		$hookers = array ();
		$core_hookers = array ();
		if (count ( $this->_hooks ) > 0) {
			foreach ( $this->_hooks as $hookName => $hook ) {
				foreach ( $hook as $hooker ) {
					if (is_string ( $hooker ['hookFunction'] )) {
						$hookKey = $hooker ['hookFunction'];
					} elseif (is_array ( $hooker ['hookFunction'] )) {
						if (is_string ( is_array ( $hooker ['hookFunction'] [0] ) )) {
							$hookKey = $hooker ['hookFunction'] [0];
						} elseif (is_object ( $hooker ['hookFunction'] [0] )) {
							$hookKey = get_class ( $hooker ['hookFunction'] [0] ) . '->' . $hooker ['hookFunction'] [1];
						}
					} else {
					}
					$filename = explode ( DIRECTORY_SEPARATOR, $hooker ['file'] );
					if (! $hooker ['hookCore']) {
						$hookers [] = array (
								'id' => ++ $incId,
								'function' => $hookKey,
								'file' => $hooker ['file'],
								'line' => $hooker ['line'],
								'filename' => end ( $filename ),
								'hookName' => $hookName,
								'hookType' => $hooker ['hookType'],
								'executionTime' => $hooker ['executionTime'],
								'hookSource' => $hooker ['hookSource'],
								'priority' => $hooker ['priority'],
								'hookCore' => $hooker ['hookCore'] 
						);
					} else {
						$core_hookers [] = array (
								'id' => ++ $incId,
								'function' => $hookKey,
								'file' => $hooker ['file'],
								'line' => $hooker ['line'],
								'filename' => end ( $filename ),
								'hookName' => $hookName,
								'hookType' => $hooker ['hookType'],
								'executionTime' => $hooker ['executionTime'],
								'hookSource' => $hooker ['hookSource'],
								'priority' => $hooker ['priority'],
								'hookCore' => $hooker ['hookCore'] 
						);
					}
				}
			}
		}
		$storage ['hooks'] = $hookers;
		$storage ['core_hooks'] = $core_hookers;
	}
	
	private function registerHook($context, $type) {
		$type = str_replace ( 'add_', '', $type );
		$type = ucfirst ( $type );
		$hookCore = false;
		if (defined ( 'WP_PLUGIN_DIR' ) && strpos ( $context ['calledFromFile'], realpath ( WP_PLUGIN_DIR ) ) !== false) {
			$matches = explode ( DIRECTORY_SEPARATOR, str_replace ( realpath ( WP_PLUGIN_DIR ), '', $context ['calledFromFile'] ) );
			$hookSource = $matches [1];
		} elseif (defined ( 'WPMU_PLUGIN_DIR' ) && strpos ( $context ['calledFromFile'], realpath ( WPMU_PLUGIN_DIR ) ) !== false) {
			$matches = explode ( DIRECTORY_SEPARATOR, str_replace ( realpath ( WP_PLUGIN_DIR ), '', $context ['calledFromFile'] ) );
			$hookSource = $matches [1];
		} elseif (function_exists ( 'get_theme_root' ) && strpos ( $context ['calledFromFile'], realpath ( get_theme_root () ) !== false )) {
			$matches = explode ( DIRECTORY_SEPARATOR, str_replace ( realpath ( WP_PLUGIN_DIR ), '', $context ['calledFromFile'] ) );
			$hookSource = $matches [1];
		} else {
			$hookSource = 'Core';
			$type .= ' (Core)';
			$hookCore = true;
		}
		
		if (! isset ( $this->_hooks [$context ['functionArgs'] [0]] )) {
			$this->_hooks [$context ['functionArgs'] [0]] = array ();
		}
		$this->_hooks [$context ['functionArgs'] [0]] [] = array (
				'hookFunction' => $context ['functionArgs'] [1],
				'file' => $context ['calledFromFile'],
				'line' => $context ['calledFromLine'],
				'executionTime' => $context ['durationExclusive'],
				'hookSource' => $hookSource,
				'hookType' => $type,
				'hookCore' => $hookCore,
				'priority' => isset ( $context ['functionArgs'] [2] ) ? $context ['functionArgs'] [2] : '10' 
		);
	}
	
	public function traceHooks() {
		$this->zre->traceFunction ( 'add_action', function () {}, function ($context) {
			$this->registerHook ( $context, 'add_action' );
		} );
		$this->zre->traceFunction ( 'add_filter', function () {}, function ($context) {
			$this->registerHook ( $context, 'add_filter' );
		} );
	}
	
}

?>
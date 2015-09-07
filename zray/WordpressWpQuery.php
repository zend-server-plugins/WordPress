<?php

namespace ZRayWordpress;

class WordpressWpQuery {

		public function beforeExit($context, &$storage) {
		global $wp_query;
		$this->_wpquery = array ();
        
		if (! empty ( $wp_query->query )) {
            
			$this->_wpquery ['Query'] = http_build_query ( $wp_query->query );
		}
		if (! empty ( $wp_query->request )) {
			$this->_wpquery ['Request'] = $wp_query->request;
		}
		$queriedObject = get_queried_object ();
		if (! empty ( $queriedObject )) {
			$this->_wpquery ['Object'] = $queriedObject;
		}
		$this->_wpquery ['Object ID'] = get_queried_object_id ();
		if (count ( $this->_wpquery ) == 0) {
			return false;
		}
		$storage ['wp_query'] [] = $this->_wpquery;
	}
	
}

?>
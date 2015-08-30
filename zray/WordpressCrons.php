<?php

namespace ZRayWordpress;

class WordpressCrons {
	
	public function beforeExit($context, &$storage) {
		$storage['crons'] = $this->getCrons();
	}
    
    public function getCrons(){
        $schedules = array ();
        $crons_arr = array ();
		foreach ( wp_get_schedules () as $key => $schedule ) {
			$schedules [$key] = $schedule ['display'];
		}
		
		if (is_array ( _get_cron_array () )) {
			foreach ( _get_cron_array () as $time => $crons ) {
				foreach ( $crons as $name => $cron ) {
					foreach ( $cron as $subcron ) {
						$crons_arr [] = array (
								'hook' => $name,
								'schedule' => empty ( $schedules [$subcron ['schedule']] ) ? $subcron ['schedule'] : $schedules [$subcron ['schedule']],
								'nextExecution' => human_time_diff ( $time ) . (time () > $time ? ' ago' : ''),
								'arguments' => count ( $subcron ['args'] ) > 0 ? print_r ( $subcron ['args'], true ) : '' 
						);
					}
				}
			}
		}
        return $crons_arr;
    }
	
}

?>
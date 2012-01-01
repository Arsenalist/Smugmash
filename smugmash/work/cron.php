<?php
require_once( "../ezsql/ezsql.php" );

set_time_limit(0);
ini_set("memory_limit", "500M");  
$jobs = $db->get_results('select * from requests where status = "notstarted" order by id asc limit 0,3');
for ($i=0; $i<count($jobs); $i++) {
   exec('php -f do.php ' . $jobs[$i]->id);
}

?>

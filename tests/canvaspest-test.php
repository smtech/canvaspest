<pre><?php
	
require_once('common.inc.php');

// header('Content-Disposition: attachment; filename="' . date('Y-m-d_H-i-s') .'_All Active Users.csv";');
// header('Content-Type: text/csv');

$api = new CanvasPestImmutable((string) $secrets->canvas->test->url, (string) $secrets->canvas->test->token);

$response = $api->get('courses/1658/sections', array('include' => array('students','enrollments')));

var_dump($response);

?></pre>
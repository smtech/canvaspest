<pre><?php
	
require_once __DIR__ . '/../vendor/autoload.php';

use smtech\CanvasPest\CanvasPest;

// header('Content-Disposition: attachment; filename="' . date('Y-m-d_H-i-s') .'_All Active Users.csv";');
// header('Content-Type: text/csv');

$secrets = simplexml_load_string(file_get_contents('secrets.xml'));

$api = new CanvasPest((string) $secrets->canvas->test->url, (string) $secrets->canvas->test->token);
$sql = new mysqli(
	(string) $secrets->mysql->host,
	(string) $secrets->mysql->username,
	(string) $secrets->mysql->password,
	(string) $secrets->mysql->database
);
	
$users = $api->get('accounts/1/users');

?></pre>

<ol><?php
	
foreach ($users as $user) {
	echo "<li>{$user['name']}</li>";
}

?></ol>
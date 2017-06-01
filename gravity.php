<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Galaxy</title>
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
</head>
<body>
<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client();

$response = $client->request(
	'GET',
	'https://www.zooniverse.org/api/subjects/queued?http_cache=true&workflow_id=1610',
    [
        'headers'  => [
        	'Accept' => 'application/vnd.api+json; version=1',
        	'Accept-Encoding' => 'gzip, deflate, sdch, br',
        	'Content-Type' => 'application/json',
        	'Cookie' => '_gat_UA-1224199-17=1; _ga=GA1.2.487743418.1496215935; _gid=GA1.2.1174176294.1496215935',
        	'Referer' => 'https://www.zooniverse.org/projects/zooniverse/gravity-spy/classify',
        ]
    ]
);

$dir = 'testImages' . DIRECTORY_SEPARATOR;
$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);
foreach($files as $file) {
    if ($file->isDir()){
        rmdir($file->getRealPath());
    } else {
        unlink($file->getRealPath());
    }
}
rmdir($dir);


// delete all files
// $files = glob('testImages/*'); // get all file names
// foreach($files as $img){ // iterate files
//   if(is_file($img))
//     unlink($img); // delete file
// }

$result = json_decode($response->getBody());

// $i = 0;
foreach ($result as $value) {
	// echo "<pre>";
	// print_r($value);
	// echo "</pre>";
	foreach ($value as $value2) {
		echo "<pre>";
		print_r($value2->href);
		echo "</pre>";
		$subject = filter_var($value2->href, FILTER_SANITIZE_NUMBER_INT);
		mkdir('testImages/' . $subject, 0777, true);
		foreach ($value2 as $value3) {
			// echo "<pre>";
			// print_r($value3);
			// echo "</pre>";
			foreach ($value3 as $value4) {
				// echo "<pre>";
				// print_r($value4);
				// echo "</pre>";
				foreach ($value4 as $key5 => $value5) {
					echo "<pre>";
					print_r($value5);
					echo "</pre>";
					echo "<div><img src='" . $value5 . "' width='200'></div>";
					$link = $value5;
					$destdir = 'testImages';
					$img = file_get_contents($link);
					// put the subject of the 4 imgs inside a folder
					file_put_contents($destdir. DIRECTORY_SEPARATOR . $subject . substr($link, strrpos($link,'/')), $img);

					// if ($i == 1) {
					// 	exit;
					// }
					// $i++;
				}
			}
		}
	}
}

?>
<script type="text/javascript">
$(window).on('load', function () {
	window.location = 'gravity2.php';
});
</script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Galaxy</title>
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
</head>
<body>
<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client();

$response = $client->request(
	'GET',
	'https://www.zooniverse.org/api/subjects/queued?http_cache=true&workflow_id=1934',
	// old url with 3 classes
	// https://www.zooniverse.org/api/subjects/queued?http_cache=true&workflow_id=1610
	// new url with 6 classes
	// https://www.zooniverse.org/api/subjects/queued?http_cache=true&workflow_id=1934
	// new url with 10 classes
	// https://www.zooniverse.org/api/subjects/queued?http_cache=true&workflow_id=1935
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
$destdir = 'testImages';
$result = json_decode($response->getBody());
// $i = 0;
foreach ($result as $value) {
	// echo "<pre>";
	// print_r($value);
	// echo "</pre>";
	foreach ($value as $value2) {
		// echo "<pre>";
		// print_r($value2->href);
		// echo "</pre>";
		$subject = filter_var($value2->href, FILTER_SANITIZE_NUMBER_INT);
		mkdir($destdir . DIRECTORY_SEPARATOR . $subject, 0777, true);
		echo "<h3>" . $subject . ' Label: ' . ($value2->metadata->{'#Label'} ?? '') . "</h3>";
		// if (!empty($value2->metadata->{'#Label'})) {
		// 	file_put_contents($destdir. DIRECTORY_SEPARATOR . $subject . DIRECTORY_SEPARATOR . $value2->metadata->{'#Label'}, '');
		// }
		// file_put_contents($destdir. DIRECTORY_SEPARATOR . $subject . substr($link, strrpos($link,'/')), '');
		// if (!empty($value2->metadata->{'#Label'})) echo " Label: " . $value2->metadata->{'#Label'};
		foreach ($value2 as $value3) {
			// show label if any
			// if (!empty($value3->{'#Label'})) echo " Label: " . $value3->{'#Label'};
			// echo "<pre>";
			// print_r($value3);
			// echo "</pre>";
			if (is_array($value3)) {
				foreach ($value3 as $value4) {
					// echo "<pre>";
					// print_r($value4);
					// echo "</pre>";
					foreach ($value4 as $key5 => $value5) {
						// echo "<pre>";
						// print_r($value5);
						// echo "</pre>";
						$link = $value5;
						$img = file_get_contents($link);
						// put the subject of the 4 imgs inside a folder
						file_put_contents($destdir. DIRECTORY_SEPARATOR . $subject . substr($link, strrpos($link,'/')), $img);
						// show images
						$imgFileName = str_replace(['/', '.png'], '', substr($link, strrpos($link,'/')));
						echo "<a href='" . $value5 . "' target='_blank'>";
							echo "<figure class='figure col-md-3'>";
								echo "<img src='" . $value5 . "' class='figure-img img-fluid rounded' alt='A generic square placeholder image with rounded corners in a figure.'>";
								echo "<figcaption class='figure-caption'>" . $imgFileName . "</figcaption>";
							echo "</figure>";
					    echo "</a>";
						// if ($i == 1) {
						// 	exit;
						// }
						// $i++;
					}
				}
			}
		}
		echo "<hr>";
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
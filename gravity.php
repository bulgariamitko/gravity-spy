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
	// url with 3 classes
	// https://www.zooniverse.org/api/subjects/queued?http_cache=true&workflow_id=1610
	// url with 6 classes IN USE
	// https://www.zooniverse.org/api/subjects/queued?http_cache=true&workflow_id=1934
	// url with 10 classes
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
// find already labaled subjects from zooinverse
$labaled = json_decode(file_get_contents("results/labelsFromZooniverse.json"), true);
// $i = 0;
// echo "<pre>";
// print_r($result->subjects);
// echo "</pre>";

foreach ($result->subjects as $subjectDetails) {
	$subject = filter_var($subjectDetails->href, FILTER_SANITIZE_NUMBER_INT);
	mkdir($destdir . DIRECTORY_SEPARATOR . $subject, 0777, true);
	$label = $subjectDetails->metadata->{'#Label'} ?? '';
	echo "<h3>" . $subject . ' Label: ' . $label . "</h3>";
	if (!empty($label)) {
		// store subjects inside a json file
        $labaled[$subject] = $label;
        file_put_contents("results/labelsFromZooniverse.json", json_encode($labaled));
	}
	foreach ($subjectDetails->locations as $image) {
		$link = $image->{'image/png'};
		$img = file_get_contents($link);
		// put the subject of the 4 imgs inside a folder
		file_put_contents($destdir. DIRECTORY_SEPARATOR . $subject . substr($link, strrpos($link,'/')), $img);
		// show images
		$imgFileName = str_replace(['/', '.png'], '', substr($link, strrpos($link,'/')));
		echo "<a href='" . $link . "' target='_blank'>";
			echo "<figure class='figure col-md-3'>";
				echo "<img src='" . $link . "' class='figure-img img-fluid rounded' alt='A generic square placeholder image with rounded corners in a figure.'>";
				echo "<figcaption class='figure-caption'>" . $imgFileName . "</figcaption>";
			echo "</figure>";
	    echo "</a>";
	}
	echo "<hr>";
}
exit;
?>
<script type="text/javascript">
$(window).on('load', function () {
	window.location = 'gravity2.php';
});
</script>
</body>
</html>
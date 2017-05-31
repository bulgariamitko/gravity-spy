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

$result = json_decode($response->getBody());



// echo "<pre>";
// echo $result;
// echo "</pre>";
// exit;

// $re = '/https:\/\/panoptes-uploads\.zooniverse\.org\/production\/subject_location\/.*.png/';

// preg_match_all($re, $result, $matches, PREG_SET_ORDER, 0);

// // Print the entire match result
// // var_dump($matches);

// foreach ($matches[0] as $key => $value) {
// 	echo "<pre>";
// 	print_r($value);
// 	echo "</pre>";
// }

foreach ($result as $key => $value) {
	// echo "<pre>";
	// print_r($value);
	// echo "</pre>";
	foreach ($value as $key2 => $value2) {
		// echo "<pre>";
		// print_r($value2);
		// echo "</pre>";
		foreach ($value2 as $key3 => $value3) {
			// echo "<pre>";
			// print_r($value3);
			// echo "</pre>";
			foreach ($value3 as $key4 => $value4) {
				// echo "<pre>";
				// print_r($value4);
				// echo "</pre>";
				foreach ($value4 as $key5 => $value5) {
					// echo "<pre>";
					// print_r($value5);
					// echo "</pre>";
					// $link= $value5;
					// $destdir = 'testImages/';
					// $img=file_get_contents($link);
					// file_put_contents($destdir.substr($link, strrpos($link,'/')), $img);
				}
			}
		}
	}
}



// // convert images from png to jpg
// $shellCommand1 = shell_exec('mogrify -format jpg testImages/*.png');
// // delete png images as we will not need them
// $shellCommand2 = shell_exec('rm testImages/*.png');

// guess classifier
$files = scandir('testImages');
foreach ($files as $file) {
	echo "<pre>";
	print_r($file);
	echo "</pre>";
	if ($file != '.' || '..') {
		// guess classifier
		$shellCommand3 = shell_exec('cd tf_files/; python label_image.py ../testImages/' . $file);
		echo "<pre>";
		print_r($shellCommand3);
		echo "</pre>";
	}
}

// echo "<pre>";
// print_r($shellCommand3);
// echo "</pre>";

// echo $response->getBody();
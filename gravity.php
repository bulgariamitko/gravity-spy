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

foreach ($result as $value) {
	// echo "<pre>";
	// print_r($value);
	// echo "</pre>";
	foreach ($value as $value2) {
		// echo "<pre>";
		// print_r($value2);
		// echo "</pre>";
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
					$link= $value5;
					$destdir = 'testImages/';
					$img=file_get_contents($link);
					file_put_contents($destdir.substr($link, strrpos($link,'/')), $img);
				}
			}
		}
	}
}

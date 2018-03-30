<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Send data to zooniverse</title>
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
</head>
<body>
<form method="post">
	<h2>Enter login data:</h2>
	<input type="text" name="authorization" placeholder="Authorization" class="form-control" required>
	<input type="text" name="cookie" placeholder="Cookie" class="form-control" required>
	<input type="text" name="session" placeholder="Session" class="form-control" required>
	<input type="submit" name="login" class="btn btn-info" value="Send data with login details">
</form>
<form method="post">
	<input type="submit" name="file" class="btn btn-success" value="Or take values from file 'env.php'">
</form>
<?php

// take data from file
include('env.php');
$_POST['session'] = $session;
$_POST['authorization'] = $authorization;
$_POST['cookie'] = $cookie;

if (!empty($_POST)) {
	// take initual data, what is send and what is not and send it
	$storedResults = json_decode(file_get_contents("results/results.json"), true);
	$sendResults = json_decode(file_get_contents("results/sendResults.json"), true);
	$result = array_diff_assoc($storedResults, $sendResults);
	// echo "<pre>";
	// print_r(array_count_values($storedResults));
	// echo "</pre>";
	// echo "<pre>";
	// print_r(count($storedResults));
	// echo "</pre>";
	// echo "<pre>";
	// print_r(count($sendResults));
	// echo "</pre>";
	// echo "<pre>";
	// print_r(count($result));
	// echo "</pre>";
	// exit;

	$i = 1;
	foreach ($result as $subject => $class) {
		// depends on the class we have to adjust the total content length
		if ($class == 'blip' || $class == 'none') {
			$contentLength = '726';
		}  elseif ($class == "power") {
			$contentLength = '727';
		} elseif ($class == "violin") {
			$contentLength = "728";
		} elseif ($class == 'whistle' || $class == "koifish") {
			$contentLength = '729';
		}

		echo "<pre>";
		print_r($i . " => Subject: " . $subject . ", Class: " . strtoupper($class));
		echo "</pre>";

		$currentTime = date('Y-m-d') . 'T' . date('h:i:s.v') . 'Z';

		// if we dont have sended those set of images send them
		if (!array_key_exists($subject, $sendResults)) {
			  $curl = curl_init();

			  curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://www.zooniverse.org/api/classifications",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 500,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",  // CHANGE SESSION ID !!!!
			  CURLOPT_POSTFIELDS => "{\"http_cache\":true,\"classifications\":{\"annotations\":[{\"value\":[{\"choice\":\"" . strtoupper($class) . "\",\"answers\":{},\"filters\":{}}],\"task\":\"T1\"}],\"metadata\":{\"workflow_version\":\"68.24\",\"started_at\":\"" . $currentTime . "\",\"user_agent\":\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36\",\"user_language\":\"en\",\"utc_offset\":\"-10800\",\"subject_dimensions\":[{\"naturalWidth\":800,\"naturalHeight\":600,\"clientWidth\":739,\"clientHeight\":555},null,null,null],\"session\":\"" . $_POST['session'] . "\",\"finished_at\":\"" . $currentTime . "\",\"viewport\":{\"width\":1366,\"height\":333}},\"links\":{\"project\":\"1104\",\"workflow\":\"1934\",\"subjects\":[\"" . $subject . "\"]},\"completed\":true}}",
			  CURLOPT_HTTPHEADER => array(
			    "accept: application/vnd.api+json; version=1",
			    "accept-encoding: gzip, deflate, br",
			    "accept-language: bg,en-GB;q=0.8,en;q=0.6,de;q=0.4",
			    "authorization: " . $_POST['authorization'],
			    "cache-control: no-cache",
			    "content-type: application/json",
			    "cookie: " . $_POST['cookie'],
			    "origin: https://www.zooniverse.org",
			    "referer: https://www.zooniverse.org/projects/zooniverse/gravity-spy/classify"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);



			if ($err) {
				echo "<pre>";
				 print_r($err);
				 echo "</pre>";
			} else {
				echo "<pre>";
				print_r(json_decode($response));
				echo "</pre>";
			    // store subjects inside a json file
			    $sendResults[$subject] = $class;
			    file_put_contents("results/sendResults.json", json_encode($sendResults));
			}
		// break;
		}
		$i++;
	} // end loop
} // end if
?>
<script type="text/javascript">
$(window).on('load', function () {
    window.location = 'index.php';
});
</script>
</body>
</html>
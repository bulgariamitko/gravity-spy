<?php
$storedResults = json_decode(file_get_contents("results/results.json"), true);
$sendResults = json_decode(file_get_contents("results/sendResults.json"), true);
$result = array_diff_assoc($storedResults, $sendResults);

foreach ($storedResults as $subject => $class) {
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
	print_r($subject);
	echo "</pre>";

	echo "<pre>";
	print_r(strtoupper($class));
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
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "{\"http_cache\":true,\"classifications\":{\"annotations\":[{\"value\":[{\"choice\":\"" . strtoupper($class) . "\",\"answers\":{},\"filters\":{}}],\"task\":\"T1\"}],\"metadata\":{\"workflow_version\":\"125.16\",\"started_at\":\"" . $currentTime . "\",\"user_agent\":\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36\",\"user_language\":\"en\",\"utc_offset\":\"-10800\",\"subject_dimensions\":[{\"naturalWidth\":800,\"naturalHeight\":600,\"clientWidth\":739,\"clientHeight\":555},null,null,null],\"session\":\"ebb69aa9a0ac54a45157b943fe84ef31c37c8674850a5a84434f6c6f68ba5d01\",\"finished_at\":\"" . $currentTime . "\",\"viewport\":{\"width\":1366,\"height\":333}},\"links\":{\"project\":\"1104\",\"workflow\":\"1610\",\"subjects\":[\"" . $subject . "\"]},\"completed\":true}}",
		CURLOPT_HTTPHEADER => array(
			"accept: application/vnd.api+json; version=1",
			"accept-encoding: gzip, deflate, br",
			"accept-language: bg,en-GB;q=0.8,en;q=0.6,de;q=0.4",
			"cache-control: no-cache",
			"content-length: " . $contentLength,
			"content-type: application/json",
			"cookie: _ga=GA1.2.945642440.1496385598; _gid=GA1.2.582162765.1496385598; _gat_UA-1224199-17=1",
			"dnt: 1",
			"origin: https://www.zooniverse.org",
			"postman-token: e332780b-1a0a-3a2c-930d-c88a98043c10",
			"referer: https://www.zooniverse.org/projects/zooniverse/gravity-spy/classify",
			"user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36"
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
			print_r($response);
			echo "</pre>";
		    // store subjects inside a json file
		    $sendResults[$subject] = $class;
		    file_put_contents("results/sendResults.json", json_encode($sendResults));
		}

	}
}
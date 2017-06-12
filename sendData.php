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
<?php
$storedResults = json_decode(file_get_contents("results/results.json"), true);
$sendResults = json_decode(file_get_contents("results/sendResults.json"), true);
$result = array_diff_assoc($storedResults, $sendResults);

if (!empty($_POST['login'])) {
	$i = 1;
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
		print_r($i . " => Subject: " . $subject . ", Class: " . strtoupper($class));
		echo "</pre>";

		$currentTime = date('Y-m-d') . 'T' . date('h:i:s.v') . 'Z';

		// if we dont have sended those set of images send them
		if (!array_key_exists($subject, $sendResults)) {
			// $client = new Client();
			// try {
			// $response = $client->request(
			// 	'GET',
			// 	'https://www.zooniverse.org/api/classifications',
			//     [
			//         'headers'  => [
			//         	'Accept' => 'application/vnd.api+json; version=1',
			//         	'Accept-Encoding' => 'gzip, deflate, br',
			// 		    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.eyJkYXRhIjp7ImlkIjo5NTg2NzIsImxvZ2luIjoiYnVsZ2FyaWFfbWl0a28iLCJkbmFtZSI6ImJ1bGdhcmlhX21pdGtvIiwic2NvcGUiOlsidXNlciIsInByb2plY3QiLCJncm91cCIsImNvbGxlY3Rpb24iLCJjbGFzc2lmaWNhdGlvbiIsInN1YmplY3QiLCJtZWRpdW0iLCJvcmdhbml6YXRpb24iLCJwdWJsaWMiXSwiYWRtaW4iOmZhbHNlfSwiZXhwIjoxNDk2ODQxODcyLCJpc3MiOiJwYW4tcHJvZCIsInJuZyI6IjFiZDkifQ.JIPkXkfi-dR31t4r8RAp4-UZCfjXWm5eQbJY3vXKiTcxhlHyDLkd_e5-IKQBtkdNwPLH870a6zOfpGjyGyf6Cmxrb9-_QYW_5vqzUmCfDzf_eGrPxfq5evIZIUEnmjccpePtG80Jcp1c00567ZspQP0llfc0RzG0jNH7sguB_2QJA_Bh2DU_oKCRw3MLUeSzu36pV5l8ajnyIxpDUxqtdUH12VJ2d_pK0hAuUbxB-FNM9xQFaIgJFjujZl-StG6diqDS0ZfyfyqZOM7UR5YABu_gIf0-EuuAiRa2LQXh6BQU4HoW1Ui3IBB0J84dVWsr6OONdH-3udK_oJiucGB5_dY8A2UCGx4A8oAl3XDPPLTh-OrbnTso9SPGevSLQiR1DpuBaz8JgeAGa3mDRvxflN1A4EeEQzjVRYum04Mdx0kFcIxeIHfU3ODMI86PoxqYyO4IT7eZELlLK7bvPU_vD6eDNKmZVlLm0QFMUlcOQGsaGLJZ-ro-VJcHaqmGXERdPl-dWDjwfWcEPqrFbawCHknTfrVFItvjANU7LdQnTMGVl4-qEQsqYnXV1N4L_qMO0WOYShKixm6tIvr4Nq32brer8DDIJQqyMo9DGPMYl5Svh_qjafbYnLLf2IUbI97pIhbcr1RD_xauN9vvk6NdWqeTV6q3mrEq6olFyc0tIto',
			// 		    'Content-Type' => 'application/json',
			// 		    'Content-Length' => 551,
			// 		    'Cookie' => '_ga=GA1.2.945642440.1496385598; _gid=GA1.2.582162765.1496385598; _gat_UA-1224199-17=1; remember_user_token=W1s5NTg2NzJdLCIkMmEkMTAkZkFWMmxVNU8xb3I2Skwwbi5vajZKZSIsIjE0OTYxNDU1NDcuNDI2NDc1NSJd--64586e8d62a4902f6270781e4b9fe5edaececf20; _Panoptes_session=bWdLVUh4cHlHbW9jVDhINjNtM3pza0xZbkJ6MGcrakIyNzFRaWhXdkhjZVkrYVV4RFgzbVpWWTR3MmRFWHB4N25FZktxY0JVVEtqK3FkZlJKc2cwWDdyQzVHa2VVVW9aamVjN05YbTJuVnIvOWFCNzZ1RndNTEZ2NmNYNTJJTC94VlpVTVh3ajVkd1NjL3ZnNEZ5b3NJQ1k3c1crVEVpbWJBTW11UGtQRnE2VkZXRmZobENaaFgzWHZvbVg5V2p2QmlqaDI3MHhoN1U0NE1veTU5SzZySFF1RVlOU1QvamNZcEpWSjBCZENmdzFPSmlGQ2drbzJlOGtpeDdFem84NS0tdHpIdHdndjV6WFFnVjBnVnVYT0lqdz09--8f222578c64d202e093aa64528f02b7fba593d6c; remember_user_token=W1s5NTg2NzJdLCIkMmEkMTAkZkFWMmxVNU8xb3I2Skwwbi5vajZKZSIsIjE0OTYxNDU1NDcuNDI2NDc1NSJd--64586e8d62a4902f6270781e4b9fe5edaececf20; gsScrollPos=566; _Panoptes_session=elRodUNvbktVUFozc0J2U2tpU3UwU0psQlRmcy9OTGRlQzhkcnB3WGhZTTlubXBBWXhmMWJGT0VJUnlMN3VJTHZid1Q5Mm9vNmxmKzZibWRIamJyK09oRnlRT1JnbXExQWhyY21jdCt0RXJBbytWVlowelhQUUtXN2J6cGFadUJYZHp4K0l6UkV5bURRSUphVGwyc2hITDJDUnI4a2pMUjZVMThXTUpEM045RGp3bnA3OTRRdVVJaXBhanZRUkZ5NXZMR2s2Y0k5WkdKZ2NUMkdmUEtPMEJlalpaL0hTek1lNnhqN1VBNlJpNHhkcnNXYXJNUUJEM3plR2tYTkUxTy0tZmczNzdNL0psaEIrN1lYMGhyWk5sUT09--2a69235f5a66a8f5ac64dc286d865a00c85ed461',
			// 		    'Referer' => 'https://www.zooniverse.org/projects/zooniverse/gravity-spy/classify',
			//         ],
			//         'json' => [
			//         	array(
			// 			   'http_cache' => true,
			// 			   'classifications' => 
			// 			  array(
			// 			     'annotations' => 
			// 			    array (
			// 			      0 => 
			// 			      array(
			// 			         'value' => 
			// 			        array (
			// 			          0 => 
			// 			          array(
			// 			             'choice' => strtoupper($class),
			// 			             'answers' => 
			// 			            array(
			// 			            )),
			// 			             'filters' => 
			// 			            array(
			// 			            ),
			// 			          ),
			// 			        ),
			// 			         'task' => 'T1',
			// 			      ),
			// 			    ),
			// 			     'metadata' => 
			// 			    array(
			// 			       'workflow_version' => '125.16',
			// 			       'started_at' => $currentTime,
			// 			       'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
			// 			       'user_language' => 'en',
			// 			       'utc_offset' => '-10800',
			// 			       'subject_dimensions' => 
			// 			      array (
			// 			        0 => 
			// 			        array(
			// 			           'naturalWidth' => 800,
			// 			           'naturalHeight' => 600,
			// 			           'clientWidth' => 739,
			// 			           'clientHeight' => 555,
			// 			        ),
			// 			        1 => NULL,
			// 			        2 => NULL,
			// 			        3 => NULL,
			// 			      ),
			// 			       'session' => '26ebd609a02fb31c93e5657bfac19b88a34dea50253e19baca4139a7abbdab01',
			// 			       'finished_at' => $currentTime,
			// 			       'viewport' => 
			// 			      array(
			// 			         'width' => 1366,
			// 			         'height' => 333,
			// 			      ),
			// 			    ),
			// 			     'links' => 
			// 			    array(
			// 			       'project' => '1104',
			// 			       'workflow' => '1610',
			// 			       'subjects' => 
			// 			      array (
			// 			        0 => $subject,
			// 			      ),
			// 			    ),
			// 			     'completed' => true,
			// 			  ),
			//         ]
			//     ]
			// );

			
			// 	var_dump($response->getBody());
			// 	$result = json_decode($response->getBody());
			// 	echo "<pre>";
			// 	print_r($result);
			// 	echo "</pre>";
			// } catch (ClientException $e) {
			//     echo "<pre>";
			//     print_r($e->getRequest());
			//     echo "</pre>";
			//     echo "<pre>";
			//     print_r($e->getResponse());
			//     echo "</pre>";
			//     echo $e->getResponse()->getBody();
			// }
			
			// exit;

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
</body>
</html>
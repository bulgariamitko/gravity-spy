<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

$storedResults = json_decode(file_get_contents("results/results.json"), true);
$sendResults = json_decode(file_get_contents("results/sendResults.json"), true);
$result = array_diff_assoc($storedResults, $sendResults);

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
		  CURLOPT_POSTFIELDS => "{\"http_cache\":true,\"classifications\":{\"annotations\":[{\"value\":[{\"choice\":\"" . strtoupper($class) . "\",\"answers\":{},\"filters\":{}}],\"task\":\"T1\"}],\"metadata\":{\"workflow_version\":\"68.24\",\"started_at\":\"" . $currentTime . "\",\"user_agent\":\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36\",\"user_language\":\"en\",\"utc_offset\":\"-10800\",\"subject_dimensions\":[{\"naturalWidth\":800,\"naturalHeight\":600,\"clientWidth\":739,\"clientHeight\":555},null,null,null],\"session\":\"edec72fba1db41c515f0fd69a1f94061e4245058f60a14b6f8403ab7d1a13ee9\",\"finished_at\":\"" . $currentTime . "\",\"viewport\":{\"width\":1366,\"height\":333}},\"links\":{\"project\":\"1104\",\"workflow\":\"1934\",\"subjects\":[\"" . $subject . "\"]},\"completed\":true}}",
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/vnd.api+json; version=1",
		    "accept-encoding: gzip, deflate, br",
		    "accept-language: bg,en-GB;q=0.8,en;q=0.6,de;q=0.4",
		    "authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.eyJkYXRhIjp7ImlkIjo5NTg2NzIsImxvZ2luIjoiYnVsZ2FyaWFfbWl0a28iLCJkbmFtZSI6ImJ1bGdhcmlhX21pdGtvIiwic2NvcGUiOlsidXNlciIsInByb2plY3QiLCJncm91cCIsImNvbGxlY3Rpb24iLCJjbGFzc2lmaWNhdGlvbiIsInN1YmplY3QiLCJtZWRpdW0iLCJvcmdhbml6YXRpb24iLCJwdWJsaWMiXSwiYWRtaW4iOmZhbHNlfSwiZXhwIjoxNDk3MDI2OTM3LCJpc3MiOiJwYW4tcHJvZCIsInJuZyI6ImU0MmEifQ.jN_EljrBdx_GkjMbzV84Igv-TFDThSnOpEnotODecd4G6slpq4QexcVwQSbq7McvgsVk2YyOPQ2wCcaNx-kgBDh8D4RgZs8PlqzUe9kxK9tU0wM-symP_MsB-nvGe6P181upjkJp_IxsCb6dJ2oaKzxhYUCJegNG8j_cAl5AseGhXw100K76Ys5HKPCe-jx_k7ug7LMS9YTzWxXdUSfvkfwP1BkAPRpor1fN6Ho6UDC2qCGS8BjEfOOUQE-xlBSPkIAhhpfJM44_Y2Ez1c3k_hDhExsg8uKVosy0z-bP8gVkPsrTIEuhFIkaB1DeBDZBoaewwxi9Qk4_DkntlS3c-iNTHrA2EK3zq83KVWZelrMPUlBgGtmLT4Q9zxwtVo8YVl7MP5zZkzoJaGtGVpxSjNIn5-JCsfcedX04c_u5e_Gv1k6wtvUTC65DZ6aOLgGeBK_KcjzE2GEXZ1k02roQW0K_fBB8-aKVziFlVIWPbmMP65n-m49Ve15HgV4abhD9LOfvCVNrh7GPI1QIMXjcu1PRcXbhXkPhmu-OLs5TKUCwfQ-ZydImSzy_waOX2nvrVmg7jdKNrhCKkpw_rpfbuTR3NrsDQt6r5TkcbmANhgkqiNgm-_913QmjAx_oMFLc2FhAM704wJOYJZhR2NI9hzVsO3I7HdKcfAXpKWl8ou0",
		    "cache-control: no-cache",
		    "content-type: application/json",
		    "cookie: _ga=GA1.2.945642440.1496385598; _gid=GA1.2.582162765.1496385598; _gat_UA-1224199-17=1; gsScrollPos=566; remember_user_token=W1s5NTg2NzJdLCIkMmEkMTAkZkFWMmxVNU8xb3I2Skwwbi5vajZKZSIsIjE0OTY4MzY5ODQuNTc3MTYxNiJd--00674cb280f10fc5119ec0a9f98c66dc702c3dc8; _Panoptes_session=aklCWG81UzV6ckJtb0lKRlZCZFR6azNDNnQrN0UvUWJ0dXFoa2dGT1ljT1VIZ1FZQTZTamhXN3Vwek8rK3hzSFVvcGx3enEvdzdJSzNzcmpsK3F5TXo0bTE1L3VqZnU5dTdjZWtMWHo4aHpoUGVSaG9tVCt5T09OMGljdVhEZkpmbmVVTnRZNUpSRDN6eTV3RHQxMWp3dk13SmRqRzRGQXpCM0p5bGU0Zm9FPS0tNCtkRVF0bktCNmN6cTJhWDcrTjVEUT09--d7c203433234e62134ff440613d52e6896bc2826; gsScrollPos=566; remember_user_token=W1s5NTg2NzJdLCIkMmEkMTAkZkFWMmxVNU8xb3I2Skwwbi5vajZKZSIsIjE0OTY4MzY5ODQuNTc3MTYxNiJd--00674cb280f10fc5119ec0a9f98c66dc702c3dc8; gsScrollPos-233=106; _Panoptes_session=OUg0UXR3Q1UrNnJxT3owZ1UwSUx4ME1ncDhScEV1WE9Bd1lDTXNEeEl4MGtrUmtoaHdZUFpGa0RNMnhFYkZWRFd2ZGEvTURjb1N2VW50VzVUV1J0T2NwM01BdVNJUFZYT3lHZ242eitGT1BCUGpkZ0lIM29HKzNYRFlRNEV0VDhTNmpZazY5NVBJMG1KbFo1emhJNzl0OTIyYXRvdXovNFVYNEhEY1hqYkFnPS0taU1JWEVMMEI4cTYrVk4rYkFaSzM0dz09--92b9af7affa05c9672be9aca85cc7181c66f0d28",
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
}
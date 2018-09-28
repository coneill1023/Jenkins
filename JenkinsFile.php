<?php

	// function to get api data
	function api_request($url, $type, $authorization, $payload = '') {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => $type,
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_HTTPHEADER     => $authorization
		));

		$response 	= curl_exec($curl);
		$err 		= curl_error($curl);

		curl_close($curl);

		if ($err) {
			return $err;
		} else {
			return $response;
		}
	}


	// get latest run id
	$authorization = array(
		"authorization: api_key aW1xOXFjNG91MmpzZ2psZ2ZuOTNtcGZmbWczdDYwajJhYnI2dTVzYjdsdTI3YWdzZjEzajgwOGtjMCY5NSYxNDgzNzI2MDg5ODgz",
		"content-type: application/json"
	);

	$runs = json_decode(api_request(
		'https://api.observepoint.com/v2/web-audits/72142/runs',
		'GET',
		$authorization
	));

	$run_id = $runs[0]->id;

	$authorization = array(
		"authorization: Bearer bTE4OThmZGpoYmoxODRuZ2FxZnN0cGtkZmsxNHJpYzV2ZjFxZmNxMDdnMWdwaDB0a2c1OGMwb3I2MCY5NSYxNTM4MDYyMjcxOTA5",
		"content-type: application/json"
	);

	// get rule failures
	$rule_results = json_decode(api_request(
		'https://app.observepoint.com/api/report/compliance/business/condition-overview?run_id=' . $run_id . '&rule_id=50030879&result_type=failed&limit=100&skip=0',
		'POST',
		$authorization,
		"{\"itemId\":82170981,\"itemType\":\"tag\",\"parentId\":null,\"parentType\":null}"
	));

	$string = $rule_results->data->description->name . " is missing on the following pages:\n";

	$values = $rule_results->data->conditionValues;

	foreach ($values as $obj => $value) {
		$string .= $value->url . "\n";
	}

	$payload = (object) [
		"text" => $string
	];

	function slack() {
		global $payload;

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL            => 'https://hooks.slack.com/services/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',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => json_encode($payload),
			CURLOPT_HTTPHEADER     => array(
				"content-type: application/json"
			)
		));

		$response 	= curl_exec($curl);
		$err 		= curl_error($curl);

		curl_close($curl);

		if ($err) {
			return $err;
		} else {
			return $response;
		}
	}

	slack();

?>

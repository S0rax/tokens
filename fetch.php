<?php
const link = "https://dmginc.gg/di_custom/token-processing/search/SubmitHandle.php";

/**
 * @param $a array
 * @param $b array
 * @return array
 */
function getLogs($a, $b)
{
	$ch = curl_init(link);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, "Content-Type: application/x-www-form-urlencoded");
	curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . json_encode([$a, $b]));
	$response = curl_exec($ch);
	curl_close($ch);
	return json_decode($response);
}

/**
 * @param $bp array
 * @return array
 */
function modify($bp)
{
	$tmp = [$bp, $bp, $bp, $bp];
	$tmp[0]["val"] = "Recruit Token";
	$tmp[1]["val"] = "Apprentice Recruit Token";
	$tmp[2]["val"] = "Journeyman Recruit Token";
	$tmp[3]["val"] = "Master Recruit Token";
	return $tmp;
}

$ret = [];
$data = json_decode($_POST["data"]);
$head = [
	"qcid" => "qc_1",
	"key" => "member_name",
	"operator" => "0",
	"concat" => "1",
	"val" => $data,
	"position" => "0"
];
$body = [
	"qcid" => "qc_2",
	"key" => "title",
	"operator" => "0",
	"concat" => "2",
	"val" => null,
	"position" => "2"
];

foreach (modify($body) as $value) {
	array_push($ret, getLogs($head, $value));
}

header("Content-Encoding: gzip");
echo gzencode(json_encode($ret));
<?php
$start = microtime(true);
const MAX = 432000;
const LINK = "https://dmginc.gg/di_custom/token-processing/search/SubmitHandle.php";

/**
 * @param $a array
 * @param $b array
 * @return array
 */
function getLogs($a, $b)
{
	$ch = curl_init(LINK);
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
	$ret = [$bp, $bp, $bp, $bp];
	$ret[0]["val"] = "Recruit Token";
	$ret[1]["val"] = "Apprentice Recruit Token";
	$ret[2]["val"] = "Journeyman Recruit Token";
	$ret[3]["val"] = "Master Recruit Token";
	return $ret;
}

$values = [];
$data = strtolower(json_decode($_POST["data"]));
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

foreach (modify($body) as $value) $values = array_merge($values, getLogs($head, $value));

usort($values, fn($a, $b) => $a->event_date - $b->event_date);

$length = count($values);
$ret = [];
$tmp = [];

for ($i = 0; $i < $length; ++$i) {
	$time = date($values[$i]->event_date);
	for ($j = $i; $j < $length; ++$j) {
		if (date($values[$j]->event_date) - $time < MAX) {
			array_push($tmp, $values[$j]);
		} else {
			if (count($tmp) >= count($ret)) $ret = $tmp;
			$tmp = [];
			break;
		}
	}
}

if (microtime(true) - $start < 4) {
	usleep(rand(5, 15) * 1e5);
}

header("Content-Encoding: gzip");
echo gzencode(json_encode($ret));
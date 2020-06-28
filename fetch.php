<?php
const MAX = 432000;
const LINK = "https://dmginc.gg/di_custom/token-processing/search/SubmitHandle.php";

const TOKEN_TYPES = [
	"Recruit Token",
	"Apprentice Recruit Token",
	"Journeyman Recruit Token",
	"Master Recruit Token",
];

function getTokenLogsForName(string $name): array
{
	$tokenLogs = [];
	foreach (TOKEN_TYPES as $tokenType) {
		$tokenLogs = array_merge($tokenLogs, getLogsForNameAndTokenType($name, $tokenType));
	}
	return $tokenLogs;
}

function getLogsForNameAndTokenType(string $name, string $tokenType): array
{
	$ch = curl_init(LINK);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, "Content-Type: application/x-www-form-urlencoded");
	curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . json_encode(
			[
				[
					"qcid" => "qc_1",
					"key" => "member_name",
					"operator" => "0",
					"concat" => "1",
					"val" => $name,
					"position" => "0"
				],
				[
					"qcid" => "qc_2",
					"key" => "title",
					"operator" => "0",
					"concat" => "2",
					"position" => "2",
					"val" => $tokenType
				]
			]
		)
	);
	$response = curl_exec($ch);
	curl_close($ch);
	return json_decode($response);
}

function sortByEventDate(stdClass $a, stdClass $b)
{
	return $a->event_date - $b->event_date;
}

function getLongestTokenLogStreak(array $tokenLogs): array
{
	$tokenLogStreak = [];

	for ($count = count($tokenLogs), $i = 0; $i < $count; $i++) {
		$tokenLogsUntil = getTokenLogsUntil(
			array_slice($tokenLogs, $i),
			((int)$tokenLogs[$i]->event_date + MAX)
		);
		if (count($tokenLogStreak) < count($tokenLogsUntil)) {
			$tokenLogStreak = $tokenLogsUntil;
		}
	}

	return $tokenLogStreak;
}

function getTokenLogsUntil(array $logs, int $until): array
{
	$tokenLogs = [];
	foreach ($logs as $log) {
		if ($log->event_date < $until) {
			$tokenLogs[] = $log;
		} else {
			break;
		}
	}
	return $tokenLogs;
}


$start = microtime(true);
$name = strtolower(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$path = "./cache/" . $name . ".json";
$cache = file_get_contents($path);

if ($cache and $start - @filemtime($path) < 600) {
	$ret = $cache;
} else {
	$tokenLogs = getTokenLogsForName($name);
	usort($tokenLogs, 'sortByEventDate');
	$ret = json_encode(getLongestTokenLogStreak($tokenLogs));
	file_put_contents($path, $ret);
}

if (microtime(true) - $start < 4) {
	usleep(rand(5, 15) * 1e5);
}

header("Content-Encoding: gzip");
echo gzencode($ret);

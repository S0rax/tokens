<?php
const MAX = 432000;
const LINK = "https://dmginc.gg/di_custom/token-processing/search/SubmitHandle.php";
const CACHE_TIME_IN_SECONDS = 600;

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

function getUserDataFromCache(string $name): ?string {
	$path = getCachePath($name);

	if (!file_exists($path)) {
		return null;
	}

	// If cache is too old do not read from it
	if (getTimeSinceModification($path) > CACHE_TIME_IN_SECONDS) {
		return null;
	}

	$cache = file_get_contents($path);
	if (false !== $cache) {
		return $cache;
	}

	return null;
}

function getCachePath(string $name): string {
	return sprintf('./cache/%s.json', $name);
}

function saveUserDataToCache(string $data, string $name): void {
	$path = getCachePath($name);

	// Do not write cache if it hasn't been older than supposed cache time
	if (getTimeSinceModification($path) < CACHE_TIME_IN_SECONDS) {
		return;
	}

	file_put_contents($path, $data);
}

/**
 * If the given file path does not exist the current timestamp will be returned
 * @param string $path
 * @return float
 */
function getTimeSinceModification(string $path): float
{
	if (!file_exists($path)) {
		return microtime(true);
	}

	return microtime(true) - filemtime($path);
}

$start = microtime(true);
$name = strtolower(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));

$data = getUserDataFromCache($name);

if (null === $data) {
	$tokenLogs = getTokenLogsForName($name);
	usort($tokenLogs, 'sortByEventDate');
	$data = json_encode(getLongestTokenLogStreak($tokenLogs));
}

saveUserDataToCache($data, $name);

if (microtime(true) - $start < 4) {
	usleep(rand(5, 15) * 1e5);
}

header("Content-Encoding: gzip");
echo gzencode($data);

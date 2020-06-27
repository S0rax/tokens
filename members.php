<?php
$name = strtolower(filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING));
$data = file_get_contents("https://dmginc.gg/?app=core&module=system&controller=ajax&do=findMember&input=" . $name);
header("Content-Encoding: gzip");
echo gzencode($data);
<?php
require(dirname(__FILE__) . "/global.php");

$type = substr($_REQUEST['type_c'], 0, -2); //忽略最后两位“_c”
$md5key = filterRelativePath($_REQUEST['key']);
$resdir = filterRelativePath($_REQUEST['res']);

if ($_SERVER['HTTP_IF_NONE_MATCH'] == $md5key) {
    header("HTTP/1.1 304 Not Modified");
} else if($type == "js" || $type == "css") {
	$cacheFile = __FILES_PATH."res_c/{$type}/{$resdir}/{$md5key}.{$type}";
	if($type == "css") {
		header("Content-Type: text/css");
	} else if($type == "js") {
		header("Content-Type: application/javascript");
	}
	header("Content-Encoding: gzip");
	header("Etag: ".$md5key);
	$expires = 365*86400; //客户端缓存1年
	header("Pragma: public");
	header("Cache-Control: maxage=".$expires);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
	
	$data = file_get_contents($cacheFile);
	echo gzencode($data);
} else {
	logWarn("Unknown type: {$type}.");
}

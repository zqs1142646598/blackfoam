<?php


function smarty_function_load_css($params, &$smarty) {
	$loadMethod = ResourceHelper::getLoadMethod();
	//装载外部资源
	if($loadMethod == "EXTERNAL") {
		if(load_external_resource("css", $params)) return;
	}
		
	$files = array();
	foreach($params as $key => $val) {
		if(substr($key, 0, 4) == 'file') {
			$val = trim($val);
			if($val == '') continue;
			$num = intval(substr($key, 4));
			$files[$num] = $val;
		}
	}
	ksort($files);
	if($loadMethod == "ORIGIN") { //显示原始文件
		foreach($files as $file) {
			$url = RewriteHelper::getURL("css", array("file"=>$file));
			echo "<link href='{$url}' rel='stylesheet' />\r\n";
		}
	} else if($loadMethod == "PAGE") { //显示到页面中
		$result = __auto_create_css_cache($files, true);
		echo "<style>\r\n".trim($result['data'])."\r\n</style>\r\n";
	} else {
		$result = __auto_create_css_cache($files);
		$url = RewriteHelper::getURL("css_c", array("key"=>$result['md5key'],"res"=>$result['resdir']));
		echo "<link href='{$url}' rel='stylesheet' />\r\n";
	}
}

function __auto_create_css_cache($files, $returnData=false) {
	if(ResourceHelper::isExternalOpen() && defined("__EXTERNAL_RES_URL")) {
		$baseURL = __EXTERNAL_RES_URL;
	} else {
		$baseURL = __RESOURCE_BASE_URL;
	}
	//1. 检查文件是否存在，并取得最后修改时间
	$loadFiles = array();
	$check = "";
	$resdir = "";
	foreach($files as $file) {
		$file = str_replace(array('//', '..'), array('/', ''), $file);
		if($file == '' || substr($file, -4) != '.css')  continue;
		if(substr($file, 0, 1) == '/') $file = substr($file, 1);
		$pathfile = __ROOT_PATH.'res/css/'.$file;
		$modifyTime = @filemtime($pathfile);
		if($modifyTime == false) {
			logWarn("Load CSS ERROR - miss file: ".$pathfile);
			continue;
		}
		$resdir .= substr($file, 0, 1); //取文件名的第一个字母作为文件名
		$check .= $file . '|' . $modifyTime . '|';
		$loadFiles[] = array('file'=>$file, 'mtime'=>$modifyTime);
	}
	//2. 检查是否有缓存文件
	$md5key = md5($check);
	$result = array('resdir'=>$resdir, 'md5key'=>$md5key);
	$cacheFile = __FILES_PATH.'res_c/css/'.$resdir.'/'.$md5key.'.css';
	if(file_exists($cacheFile)) {
		if($returnData) {
			$result['data'] = file_get_contents($cacheFile);
		}
		return $result;
	}
	$data = "";
	foreach($loadFiles as $info) {
		$originFile = __ROOT_PATH.'res/css/'.$info['file'];
		$str = file_get_contents($originFile);
		$subBaseURL = $baseURL."css/";
		$subdir = dirname($info['file']);
		if($subdir != "" && $subdir != ".") {
			$subBaseURL .= $subdir . "/";
		}
		$str = FastPHP_CSSMin::minify($str, $subBaseURL);
		$data .= $str . "\r\n";
	}
	if(file_exists(dirname($cacheFile)) == false) {
		mkdir(dirname($cacheFile), 0777, true);
	}
	file_put_contents($cacheFile, $data);
	if($returnData) {
		$result['data'] = $data;
	}
	return $result;
}
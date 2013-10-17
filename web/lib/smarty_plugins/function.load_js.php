<?php


function smarty_function_load_js($params, &$smarty) {
	$loadMethod = ResourceHelper::getLoadMethod();
	//装载外部资源: jQuery CDN
	if(ResourceHelper::isExternalOpen() && defined("__EXTERNAL_JQUERY_URL")) {
		foreach($params as $key => $val) {
			if(substr($key, 0, 4) == 'file' && ($val == "jquery.js" || $val == "jquery.min.js")) {
				echo "<script language='JavaScript' src='".__EXTERNAL_JQUERY_URL."'></script>\r\n";
				unset($params[$key]);
				break;
			}
		}
	}
	//装载外部资源: 外部存储
	if($loadMethod == "EXTERNAL") {
		if(load_external_resource("js", $params)) return;
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
			$url = RewriteHelper::getURL("js", array("file"=>$file));
			echo "<script language='JavaScript' src='{$url}'></script>\r\n";
		}
	} else if($loadMethod == "PAGE") { //显示到页面中
		$result = __auto_create_js_cache($files, true);
		echo "<script language='JavaScript'>\r\n".trim($result['data'])."\r\n</script>\r\n";
	} else {
		$result = __auto_create_js_cache($files);
		$url = RewriteHelper::getURL("js_c", array("key"=>$result['md5key'],"res"=>$result['resdir']));
		echo "<script language='JavaScript' src='{$url}'></script>\r\n";
	}
}

function __auto_create_js_cache($files, $returnData=false) {
	//1. 检查文件是否存在，并取得最后修改时间
	$loadFiles = array();
	$check = "";
	$resdir = "";
	foreach($files as $file) {
		$file = str_replace(array('//', '..'), array('/', ''), $file);
		if($file == '' || substr($file, -3) != '.js')  continue;
		if(substr($file, 0, 1) == '/') $file = substr($file, 1);
		$pathfile = __ROOT_PATH.'res/js/'.$file;
		$modifyTime = @filemtime($pathfile);
		if($modifyTime == false) {
			logWarn("Load JS ERROR - miss file: ".$pathfile);
			continue;
		}
		$resdir .= substr($file, 0, 1); //取文件名的第一个字母作为文件名
		$check .= $file . '|' . $modifyTime . '|';
		$loadFiles[] = array('file'=>$file, 'mtime'=>$modifyTime);
	}
	//2. 检查是否有缓存文件
	$md5key = md5($check);
	$result = array('resdir'=>$resdir, 'md5key'=>$md5key);
	$cacheFile = __FILES_PATH.'res_c/js/'.$resdir.'/'.$md5key.'.js';
	if(file_exists($cacheFile)) {
		if($returnData) {
			$result['data'] = file_get_contents($cacheFile);
		}
		return $result;
	}
	$data = "";
	foreach($loadFiles as $info) {
		//检查是否已是.min文件
		$originFile = __ROOT_PATH.'res/js/'.$info['file'];
		if(substr($info['file'], -7, 4) != '.min') { //转换为.min文件
			$minFile = __FILES_PATH.'res_c/jsmin/'.substr($info['file'], 0, -2).$info['mtime'].'.min.js';
			if(file_exists($minFile) == false) {
				$str = JSMin::minify(file_get_contents($originFile));
				if(file_exists(dirname($minFile)) == false) {
					mkdir(dirname($minFile), 0777, true);
				}
				file_put_contents($minFile, $str);
			} else {
				$str = file_get_contents($minFile);
			}
		} else { //源文件已是压缩过的，直接读取
			$str = file_get_contents($originFile);
		}
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
<?php
if(!defined("__FILE_GLOBAL_PHP")) {

define("__FILE_GLOBAL_PHP", true);
define("__ROOT_PATH", dirname(__FILE__)."/");

/**
 * 自动装载类
 * 类目录结构：
 * 		1. ClassName中以“_”分隔，作为类的各级目录名。
 * 		2. “Action, Dao”后缀的根目录是：lib/classes/{ModuleName}/{action|dao}/。 
 *	 			{ModuleName}是类名中“_”分隔的部分，默认取“default”
 * 		3. 默认类的根目录为lib/includes/
 */
function __autoload($class) {
	$pathArr = explode("_", $class);
	$className = $pathArr[count($pathArr) - 1];
	
	if(count($pathArr) > 1) {
		$classPath = implode("/", array_slice($pathArr, 0, -1));
	} else {
		$classPath = "";
	}
	
	if(substr($class, -3) == "Dao") {
		if($classPath == "") {
			$classPath = "Default";
		}
		$classPath = __ROOT_PATH . "lib/classes/" . $classPath . "/Dao/";
	} else if(substr($class, -6) == "Action") {
		if($classPath == "") {
			$classPath = "Default";
		}
		$classPath = __ROOT_PATH . "lib/classes/" . $classPath . "/Action/";
	} else if(substr($class, -6) == "Helper") {
		if($classPath == "") {
			$classPath = "Default";
		}
		$classPath = __ROOT_PATH . "lib/classes/" . $classPath . "/Helper/";
	} else {
		$classPath = __ROOT_PATH . "lib/includes/" . $classPath . "/";
	}
	
	include_once($classPath . $className . ".php");
}

//运行FastPHP框架函数
function fastphp_run_action($actionkey) {
	global $__ACTION_KEY_ALIAS;
	$module = "Default";
	$action = "Home";
	$method = "Index";
	$actionkey = trim($actionkey);
	if($actionkey != "") {
		//检查别名actionkey别名表
		if(isset($__ACTION_KEY_ALIAS[$actionkey])) {
			$config = $__ACTION_KEY_ALIAS[$actionkey];
			if(!empty($config['Module'])) $module = $config['Module'];
			if(!empty($config['Action'])) $action = $config['Action'];
			if(!empty($config['Method'])) $method = $config['Method'];
		} else {
			$tmp = explode('.', $actionkey, 2);
			if(count($tmp) > 1 && !empty($tmp[1])) $method = $tmp[1];
			if(!empty($tmp[0])) {
				$action = $tmp[0];
				if(strpos($action, "_") > 0) {
					$tmp = explode('_', $action, 2);
					$module = $tmp[0];
					$action = $tmp[1];
				}
			}
		}
	}
	if($module != "Default") $action = $module .'_'. $action;
	$action .= "Action";
	
	// Create Action Class
	$obj = new $action;
	if(defined("__RUN_UNIX_MODE") && __RUN_UNIX_MODE && $action != get_class($obj)) {
		$msg = "[FastPHP] not exists action: {$action} (similar action: ".classget_class($obj).")";
		logError($msg);
		redirect302(__HOME_URL."notfound.php?msg=".urlencode($msg));
	}
	$obj->execute($method);
}

require_once(__ROOT_PATH . "etc/define.php");
require_once(__ROOT_PATH . "etc/config_rewrite.php");
require_once(__ROOT_PATH . "lib/functions/func.Common.php");

date_default_timezone_set(__TIMEZONE); // 设置PHP运行环境的时区
mb_internal_encoding(__CHARSET); // 设置mbstring类库的默认字符编码

// 自动创建必要目录
if(file_exists(__FILES_PATH . "templates_c/") == false) {
	mkdir(__FILES_PATH . "templates_c/", 0777, true);
}

}

<?php
class RewriteHelper {
	public static function getURL($type, $params=null) {
		switch($type) {
			case 'css':
			case 'img':
			case 'js':
			case 'file':
				$url = self::getResource($type, $params['file']);
				break;
			case 'css_c':
			case 'js_c':
				$url = self::getCompiledResource($type, $params);
				break;
			default:
				$url = self::getCustomPage($type, $params);
				break;
		}
		return $url;
	}
	
	protected static function getCustomPage($type, $params) {
		//转换ActionKey为，缩写格式
		if($pos = strpos($type, '_')) {
			if(substr($type, 0, $pos) == "Default") {
				$type = substr($type, $pos + 1);
			}
		}
		if($pos = strrpos($type, '.')) {
			if(substr($type, $pos) == ".Index") {
				$type = substr($type, 0, $pos);
			}
		}
		//Note: 在此编写自定义的URL规则
		switch($type) {
		default:
			$url = FastPHP_Rewrite::generateURL($type, $params);
		}
		return $url;
	}

	protected static function getResource($type, $url) {
		$baseURL = __RESOURCE_BASE_URL;
		$prefix = "";
		$version = "";
		switch($type) {
			case 'css':
				$prefix = "css/";
				if(defined("__VERSION_CSS")) $version = __VERSION_CSS;
				break;
			case 'img':
				$prefix = "img/";
				if(defined("__VERSION_IMG")) $version = __VERSION_IMG;
				break;
			case 'js':
				$prefix = "js/";
				if(defined("__VERSION_JS")) $version = __VERSION_JS;
				break;
			case 'file':
				$domain = self::getImageDomain($url);
				$baseURL = "http://{$domain}/";
				break;
			default:
				throw new Exception("unknown type.({$type})");
		}
		$url = $baseURL . $prefix . $url;
		if($version != "") $url .= "?".$version;
		return $url;
	}
	
	protected static function getCompiledResource($type, $params) {
		$url = __HOME_URL . "resource.php?type_c=".$type;
		$str = "";
		foreach($params as $key => $val) {
			$str .= "&{$key}=".urlencode($val);
		}
		$url .= $str;
		return $url;
	}
	
}
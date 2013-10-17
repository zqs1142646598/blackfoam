<?php
class ResourceHelper {
	public static function isExternalOpen() {
		global $_RESOURCE_CONFIG;
		$ret = false;
		if(defined("__EXTERNAL_RES_SWITCH") && __EXTERNAL_RES_SWITCH) {
			$ret = __EXTERNAL_RES_SWITCH;
		}
		if(isset($_RESOURCE_CONFIG['EXTERNAL_SWITCH'])) {
			$ret = $_RESOURCE_CONFIG['EXTERNAL_SWITCH'];
		}
		return $ret;
	}
	
	public static function getLoadMethod() {
		global $_RESOURCE_CONFIG;
		$ret = "ORIGIN";
		if(defined("__RESOURCE_LOAD_METHOD") && __RESOURCE_LOAD_METHOD) {
			$ret = __RESOURCE_LOAD_METHOD;
		}
		if(isset($_RESOURCE_CONFIG['LOAD_METHOD'])) {
			$ret = $_RESOURCE_CONFIG['LOAD_METHOD'];
		}
		return $ret;
	}
}
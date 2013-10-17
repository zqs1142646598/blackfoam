<?php
/**
 * Project:     ActionPHP (The MVC Framework) 
 * File:        Rewrite.php
 *
 * This framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author XuLH <hansen@fastphp.org>
 */

class FastPHP_Rewrite {
	public static function generateURL($type, $params) {
		global $_FASTPHP_REWRITE_RULE;
		$url = __HOME_URL;
		if(defined("__REWRITE_RULE_MODE") && __REWRITE_RULE_MODE != "CLOSE" && isset($_FASTPHP_REWRITE_RULE[$type])) {
			//移除空值参数
			if(count($params) > 0) {
				foreach($params as $key => $val) {
					if(empty($val)) {
						unset($params[$key]);
					}
				}
			}
			$config = $_FASTPHP_REWRITE_RULE[$type];
			$rule = substr($config['rule'], 1); //跳过第一个 “/”
			if(preg_match_all('/\{\$([^\}]+)\}/', $rule, $matches)) {
				$cnt = count($matches[1]);
				for($i=0; $i<$cnt; $i++) {
					$key = $matches[1][$i];
					if(isset($params[$key])) {
						$matches[1][$i] = FastPHP_Request::encode($params[$key]);
						unset($params[$key]);
					} else {
						$matches[1][$i] = "";
					}
				}
				$rule = str_replace($matches[0], $matches[1], $rule);
				$url .= $rule;
				if(count($params) > 0) {
					ksort($params); //剩余参数默认按key排序
					$mode = __REWRITE_RULE_MODE;
					if(!empty($config['mode'])) {
						$mode = $config['mode'];
					}
					if($mode == "PERFECT" || $mode == "PERFECT_R301") {
						if(substr($url, -1) != '/') {
							$url .= '/';
						}
						$str = "";
						foreach($params as $key => $val) {
							$val = FastPHP_Request::encode($val);
							$str .= "--{$key}-{$val}";
						}
						$url .= substr($str, 2).".htm";
					} else {
						$str = "";
						foreach($params as $key => $val) {
							$str .= "&{$key}=".urlencode($val);
						}
						$url .= '?' . substr($str, 1);
					}
				}
			} else {
				$url .= $rule;
			}
		} else {
			$url .= "action.php?actionkey=".$type;
			if(empty($params)) {
				return $url;
			}
			$str = "";
			foreach($params as $key => $val) {
				$str .= "&{$key}=".urlencode($val);
			}
			$url .= $str;
		}
		return $url;
	}
	
	public static function parseRequest() {
		global $_FASTPHP_REWRITE_RULE;
		$actionkey = self::parseActionKey();
		//检查URL是否规范化
		if(FastPHP_Request::isGetMethod()) { //仅GET方法才可能跳转
			$mode = __REWRITE_RULE_MODE;
			$config = & $_FASTPHP_REWRITE_RULE[$actionkey];
			if(!empty($config['mode'])) {
				$mode = $config['mode'];
			}
			if(!empty($_COOKIE['fastphp_r301_mark'])) {
				setcookie("fastphp_r301_mark", "", time()-86400, "/");
			} else if($mode == "BASIC_R301" || $mode == "PERFECT_R301") {
				$params = $_GET;
				if(isset($params['actionkey'])) {
					unset($params['actionkey']);
				}
				$url = RewriteHelper::getURL($actionkey, $params);
				//die($url);
				$r301 = false;
				if(substr($url, 0, 7) == 'http://') {
					if(strcmp("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $url) !== 0) {
						$r301 = true;
					}
				} else if(substr($url, 0, 8) == 'https://') {
					if(strcmp("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $url) !== 0) {
						$r301 = true;
					}
				} else {
					if(strcmp($_SERVER['REQUEST_URI'], $url) !== 0) {
						$r301 = true;
					}
				}
				if($r301) {
					setcookie("fastphp_r301_mark", "1", 0, "/");
					redirect301($url);
				}
			}
		}
		return $actionkey;
	}
	
	private static function parseActionKey() {
		global $_FASTPHP_REWRITE_RULE;
		// 解析 Path & Query
		$path = $_SERVER['REQUEST_URI'];
		$query = "";
		$pos = strpos($path, "?");
		if($pos !== false){
			$query = substr($path, $pos);
			$path = substr($path, 0, $pos);
		}
		// 解析 Home Path
		$homePath = __HOME_URL;
		if($homePath == "" || substr($homePath, -1) != "/") {
			logFatal("__HOME_URL must end with '/'.");
		} else if(substr($homePath, 0, 7) == "http://" || substr($homePath, 0, 8) == "https://") {
			$pos = strpos($homePath, "/", 10);
			if($pos !== false) {
				$homePath = substr($homePath, $pos);
			} else {
				$homePath = "/";
			}
		}
		if(defined("__REWRITE_RULE_MODE") == false || __REWRITE_RULE_MODE == "CLOSE") {
			return self::getDefaultActionKey($homePath, $path);
		}
		$homePathLen = strlen($homePath);
		$pathLen = strlen($path);
		if($pathLen < $homePathLen || $homePath != substr($path, 0, $homePathLen)) {
			return self::getDefaultActionKey($homePath, $path);
		}
		$path = substr($path, $homePathLen - 1);
		//提取URL中的静态化参数部分
		$parsedParams = array();
		$staticParamStr = "";
		if($pathLen > 5 && substr($path, -4) == ".htm") { //静态化参数必须以 .htm 结尾
			$pos = strrpos($path, '/');
			$staticParamStr = substr($path, $pos + 1, -4); //最后一个 "/" 与 ".htm" 之间的内容
			$path = substr($path, 0, $pos); //不包含结尾的“/”
			$pathLen = $pos;
		} else if(substr($path, -1) == '/') {
			$path = substr($path, 0, -1); //移除结尾的“/”
			$pathLen--;
		}
		//URL规则匹配
		$find_actionkey = null;
		foreach($_FASTPHP_REWRITE_RULE as $key => &$config) {
			$rule = $config['rule'];
			if($pathLen == 0) { //特殊情形 - 首页地址
				if($rule == "/") {
					$find_actionkey = $key;
					break;
				}
				continue;
			}
			$ruleLen = strlen($rule);
			$pos = strpos($rule, '{');
			if($pos === false) {
				if(substr($rule, -1) == '/') {
					if(substr($rule, 0, -1) == $path) {
						break;
					}
				} else if($rule == $path) { //没有表达式，结尾没有 “/” 
					break;
				}
				continue;
			}
			if($pos > $pathLen - 1
				|| ($pos == $pathLen && $rule[$pos-1] != '/')
				|| substr($rule, 0, $pos) != substr($path, 0, $pos)) {
				continue;
			}
			//正则匹配
			$regex = "";
			$varNames = array();
			for($i=0; $i<$ruleLen; $i++) {
				if($rule[$i] == '/') {
					$regex .= "\\/";
				} else if($rule[$i] == '{') {
					//do check
					//if($rule[$i+1] != '$') {
					//}
					$i += 2; //跳过 “{$”
					//$rule[$i] = "";
					$varName = "";
					for(; $i<$ruleLen; $i++) {
						if($rule[$i] == '}') {
							$i++; //跳过 }
							break;
						}
						$varName .= $rule[$i];
					}
					if(!empty($config['type'][$varName])) {
						$regex .= "(".$config['type'][$varName].")";
						$varNames[] = $varName;
					} else {
						logDebug("[RewriteRule] ActionKey: {$key}, Rule={$rule}, IgnoreVariable={$varName}.");
					}
				} else {
					$regex .= $rule[$i];
				}
			}
			$matches = array();
			if(@preg_match("/^{$regex}/", $path, $matches) == false) {
				continue;
			}
			//找到了
			$find_actionkey = $key;
			//提取变量
			foreach($varNames as $index => $varName) {
				$parsedParams[$varName] = FastPHP_Request::decode($matches[$index + 1]);
			}
			break;
		}
		if($find_actionkey !== null) {
			//解析自动静态化变量
			if($staticParamStr != "") {
				$arr = explode("--", $staticParamStr);
				foreach($arr as $str) {
					$arr2 = explode("-", $str, 2);
					$parsedParams[$arr2[0]] = FastPHP_Request::decode($arr2[1]);
				}
			}
			//已有变量的优先级高
			$_GET = array_merge($parsedParams, $_GET);
			$_REQUEST = array_merge($parsedParams, $_REQUEST);
			return $find_actionkey;
		} else {
			return self::getDefaultActionKey($homePath, $path);
		}
	}

	private static function getDefaultActionKey($homePath, $path) {
		if(!empty($_REQUEST['actionkey'])) {
			return $_REQUEST['actionkey'];
		}
		if($path == "/" || $homePath == $path || $homePath."index.php" == $path) {
			return "Home";
		} else {
			return "NotFound";
		}
	}
	
}
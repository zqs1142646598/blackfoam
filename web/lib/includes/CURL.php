<?php
/*
Sean Huber CURL library
This library is a basic implementation of CURL capabilities.
It works in most modern versions of IE and FF.
==================================== USAGE ====================================
It exports the CURL object globally, so set a callback with setCallback($func).
(Use setCallback(array('class_name', 'func_name')) to set a callback as a func
that lies within a different class)
Then use one of the CURL request methods:
get($url);
post($url, $vars); vars is a urlencoded string in query string format.
Your callback function will then be called with 1 argument, the response text.
If a callback is not defined, your request will return the response text.

[Informational 1xx]
100="Continue"
101="Switching Protocols"
[Successful 2xx]
200="OK"
201="Created"
202="Accepted"
203="Non-Authoritative Information"
204="No Content"
205="Reset Content"
206="Partial Content"
[Redirection 3xx]
300="Multiple Choices"
301="Moved Permanently"
302="Found"
303="See Other"
304="Not Modified"
305="Use Proxy"
306="(Unused)"
307="Temporary Redirect"
[Client Error 4xx]
400="Bad Request"
401="Unauthorized"
402="Payment Required"
403="Forbidden"
404="Not Found"
405="Method Not Allowed"
406="Not Acceptable"
407="Proxy Authentication Required"
408="Request Timeout"
409="Conflict"
410="Gone"
411="Length Required"
412="Precondition Failed"
413="Request Entity Too Large"
414="Request-URI Too Long"
415="Unsupported Media Type"
416="Requested Range Not Satisfiable"
417="Expectation Failed"
[Server Error 5xx]
500="Internal Server Error"
501="Not Implemented"
502="Bad Gateway"
503="Service Unavailable"
504="Gateway Timeout"
505="HTTP Version Not Supported"

*/
class CURL {
	var $callback = false;
	public static $instances = array();
	private $timeout = 30; //默认超时时间为30秒
	private $cookiePool = array();
	/**
	 * @var bool 是否在curl内容中返回header信息
	 */
	private $headerEnabled = true;
	private $customerCookies = array();
	private $headers = array();
	
	function getInstance()
	{
    	if(isset(self::$instances[1])) {
	    	$instance = self::$instances[1];
	    	if(!empty($instance) && is_object($instance)) {
	    		return $instance;
	    	}
    	}
    	$instance = new CURL();
    	self::$instances[1] = $instance;
    	return $instance;
	}
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	function addCookie($cookieName, $cookieValue) {
		$this->customerCookies[$cookieName] = $cookieValue;
	}
	function setCallback($func_name) {
		$this->callback = $func_name;
	}
	function setHeaderEnable($enabled) {
		$this->headerEnabled = $enabled;
	}
	function setTimeout($second)
	{
		$this->timeout = $second;
	}
	function doRequest($method, $url, $vars = null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		
		curl_setopt($ch, CURLOPT_HEADER, $this->headerEnabled);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
		$flag = 0;
		$newHeaders = array();
		foreach($this->headers as $key => $value) {
			$str = $key . ": " . $value;
			$key2 = strtolower($key);
			if($key2 == "user-agent") {
				$flag |= 0x01;
			}
			$newHeaders[] = $str;
		}
		if(count($newHeaders) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $newHeaders);
		}
		if(($flag & 0x01 == 0) && isset($_SERVER['HTTP_USER_AGENT'])) {
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		}
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//增加COOKIE
		$info = parse_url($url);
		$domainTree = $this->getDomainTree($info['host']);
		$usingCookies = array();
		foreach($domainTree as $domain) {
			if(isset($this->cookiePool[$domain]) == false) {
				continue;
			}
			$optionsCookies = $this->cookiePool[$domain];
			foreach($optionsCookies as $cookieName => $cookieInfo) {
				$usingCookies[$cookieName] = $cookieInfo['value'];
			}
		}
		//增加Customer Cookie
		foreach($this->customerCookies as $name => $value) {
			$usingCookies[$name] = $value;
		}
		if(count($usingCookies) > 0) {
			$cookieStr = "";
			foreach($usingCookies as $name => $value) {
				if($cookieStr != "") {
					$cookieStr .= "; ";
				}
				$cookieStr .= $name."=".$value;
			}
			curl_setopt($ch, CURLOPT_COOKIE, $cookieStr);
		}
		
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

		//获得请求状态及数据
		$data = curl_exec($ch);
		$info = curl_getinfo($ch);
		if($this->headerEnabled) {
			$info['header'] = "";
			if($pos = strpos($data, "\r\n\r\n")) {
				$info['header'] = substr($data, 0, $pos);
				$data = substr($data, $pos+4);
			}
			//解析header，取得Cookie
			$arr = explode("\r\n", $info['header']);
			if(count($arr) == 1 && strpos($arr[0], " 100 ")) {
				if($pos = strpos($data, "\r\n\r\n")) {
					$info['header'] = substr($data, 0, $pos);
					$data = substr($data, $pos+4);
				}
				//解析header，取得Cookie
				$arr = explode("\r\n", $info['header']);
			}
			foreach($arr as $line) {
				$arr2 = explode(":", $line, 2);
				if(strtolower($arr2[0]) != 'set-cookie') {
					continue;
				}
				$arr3 = explode(";", $arr2[1]);
				$cnt3 = count($arr3);
				$cookieName = "";
				$record = array();
				for($i=0; $i<$cnt3; $i++) {
					$arr4 = explode("=", $arr3[$i], 2);
					$name = trim($arr4[0]);
					$value = trim($arr4[1]);
					if($i == 0) { //第1个是Cookie变量
						$cookieName = $name;
						$record['value'] = $value; 
					} else {
						$record[$name] = $value;
					}
				}
				if($cookieName == "" || $record['value'] == "deleted") {
					continue;
				}
				if(!empty($record['expires'])) {
					$record['expires'] = strtotime($record['expires']);
				}
				$cookieDomain = $domainTree[0];
				if(isset($record['domain']) && in_array($cookieDomain, $domainTree)) {
					$cookieDomain = $record['domain'];
				}
				$this->cookiePool[$cookieDomain][$cookieName] = $record;
			}
		}
		$info['data'] = $data;
		if(curl_errno($ch)) {
			$info['error'] = curl_errno($ch) . ": " . curl_error($ch);
		}
		curl_close($ch);

		if ($data) {
			if ($this->callback) {
				$callback = $this->callback;
				$this->callback = false;
				$info['data'] = call_user_func($callback, $info['data']);
			}
		}
		return $info;
	}
	function get($url) {
		return $this->doRequest('GET', $url, null);
	}
	function get_contents($url) {
		$this->setHeaderEnable(false);
		$info = $this->doRequest('GET', $url, null);
		if($info['http_code'] != 200) {
			return false;
		}
		return $info['data'];
	}
	function post($url, $vars) {
		return $this->doRequest('POST', $url, $vars);
	}
	
	protected function getDomainTree($host) {
		$arr = explode(".", $host);
		$cnt = count($arr);
		$result = array($host);
		if(is_numeric($arr[$cnt-1])) {
			return $result; //IP地址
		}
		for($i=1; $i<$cnt-1; $i++) { //至少留两位
			$result[] = implode(".", array_slice($arr, $i));
		}
		return $result;
	}
}
?>
<?php
/**
 * Project:     ActionPHP (The MVC Framework) 
 * File:        Request.php
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

class FastPHP_Request {
	protected static $a = 0;
	protected static $A = 0;
	protected static $z = 0;
	protected static $Z = 0;
	protected static $n0 = 0;
	protected static $n9 = 0;
	protected static $filter = array();
	
	protected static function init() {
		if(self::$a != 0) {
			return;
		}
		self::$a = ord('a');
		self::$A = ord('A');
		self::$z = ord('z');
		self::$Z = ord('Z');
		self::$n0 = ord('0');
		self::$n9 = ord('9');
//		self::$filter = array(ord('.')=>1, ord(',')=>1);
		self::$filter = array(ord('.')=>1);
	}
	
	/**
	 * URL参数编码
	 */
	public static function &encode($str) {
		if(!is_string($str)) {
			return $str;
		}
		self::init();
		//return base64_encode($str);
		$ret = "";
		$len = strlen($str);
		$in = false;
		for($loop=0; $loop<$len; $loop++) {
			$ch = ord($str[$loop]);
			if(($ch >= self::$a && $ch <= self::$z)
				|| ($ch >= self::$A && $ch <= self::$Z)
				|| ($ch >= self::$n0 && $ch <= self::$n9)
				|| isset(self::$filter[$ch])) {
				if($in) {
					$ret .= "_";
					$in = false;
				}
				$ret .= chr($ch);
				continue;
			}
			if($in == false) {
				$ret .= "_";
				$in = true;
			}
			$ret .= chr(self::$a + intval($ch/16));
			$ret .= chr(self::$a + intval($ch%16));
			if($ch > 127 && $loop+1<$len) {
				$ch = ord($str[++$loop]);
				$ret .= chr(self::$a + intval($ch/16));
				$ret .= chr(self::$a + intval($ch%16));
			}
		}
		return $ret;
	}
	

	/**
	 * URL参数解码
	 */
	public static function &decode($str) {
		self::init();
		$ret = "";
		$len = strlen($str);
		for($loop=0; $loop<$len; $loop++) {
			if($str[$loop] == '_') {
				for($loop++; $loop<$len; $loop+=2) {
					if($str[$loop] == '_' || $loop+1 == $len) {
						break;
					}
					$ch = $str[$loop];
					$ch2 = $str[$loop+1];
					$word = (ord($ch) - self::$a) * 16;
					$word += ord($ch2) - self::$a;
					if($word < 0) {
						$word = ord('?');
					}
					$ret .= chr($word);
				}
			} else {
				$ret .= $str[$loop];
			}
		}
		return $ret;
	}
	
	/**
	 * 检查是否为Ajax请求
	 */
	public static function isAjaxRequest() {
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}
		return false;
	}

	/**
	 * 检查是否为HTTP GET请求
	 */
	public static function isGetMethod() {
		if(isset($_ENV['REQUEST_METHOD']) && $_ENV['REQUEST_METHOD'] == "GET") {
			return true;
		}
		if(isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "GET") {
			return true;
		}
		return false;
	}

	
}
<?php
/**
 * Project:     ActionPHP (The MVC Framework)
 * File:        CSSMin.php
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

class FastPHP_CSSMin {
	
	public static function minify($css, $baseURL) {
	  	$baseURL = trim($baseURL);
		if(substr($baseURL, -1) != "/") {
			$baseURL .= "/";
		}
		//提取所有URL
		$matches = array();
		if(preg_match_all('/url\s*\(([^\)]+)\)/i', $css, $matches)) {
			$cnt = count($matches[0]);
			$searchArr = array();
			$replaceArr = array();
			$map = array();
			for($i=0; $i<$cnt; $i++) {
				if(isset($map[$matches[0][$i]])) {
					continue;
				}
				$map[$matches[0][$i]] = 1;
				$searchArr[] = $matches[0][$i];
				$replaceArr[] = $matches[1][$i];
			}
			$cnt = count($replaceArr);
			for($i=0; $i<$cnt; $i++) {
				$url = trim(str_replace("\"", "", $replaceArr[$i]));
				$url = self::getAbsoluteURL($url, $baseURL);
				$replaceArr[$i] = "url({$url})";
			}
			//替换操作
			$css = str_replace($searchArr, $replaceArr, $css);
		}
		return $css;
	}
	
	public static function getAbsoluteURL($url, $baseURL) {
		if(strtolower(substr($url, 0, 7)) == "http://" || strtolower(substr($url, 0, 8)) == "https://") {
			return $url;
		}
		if(strtolower(substr($baseURL, 0, 7)) == "http://" || strtolower(substr($baseURL, 0, 8)) == "https://") {
			$isAbsolote = true;
		} else {
			$isAbsolote = false;
		}
		if(substr($baseURL, -1) != "/") { //最后一位必须是“/”
			$baseURL .= "/";
		}
		if(substr($url, 0, 1) == "/") {
			if($isAbsolote) {
				$pos = strpos($baseURL, "/", 8);
				if($pos > 0) {
					return substr($baseURL, 0, $pos) . $url;
				} else {
					return $baseURL . $url;
				}
			} else {
				return $url;
			}
		}
		if(substr($url, 0, 2) == "./") {
			return $baseURL . substr($url, 2);
		}
		for($i=0; substr($url, 0, 3) == "../"; $i++) {
			$url = substr($url, 3);
		}
		$lastPos = strrpos($baseURL, '/');
		$len = strlen($baseURL);
		for(; $i>0; $i--) {
			if($lastPos == 0) {
				break;
			}
			$pos = strrpos($baseURL, "/", $lastPos - 1 - $len);
			if($isAbsolute) {
				if($pos < 10) { //到底了
					break;
				}
			} else {
				if($pos === false) { //找不到了
					break;
				}
			}
			$lastPos = $pos;
		}
		return substr($baseURL, 0, $lastPos + 1) . $url;
	}
	
}
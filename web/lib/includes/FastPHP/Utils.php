<?php
/**
 * Project:     ActionPHP (The MVC Framework) 
 * File:        Cache.php
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

class FastPHP_Utils {
	
	/**
	 * 列出目录的文件列表
	 * @path 路径
	 * @sortBy 排序方式(默认,文件名称,更新时间)
	 */
	public static function listFiles($path, $orderBy=NULL, $orderType="SORT_ASC") {
		if(is_dir($path) == false) {
			return NULL;
		}
		$handle = opendir($path);
		if($handle == false) {
			return NULL;
		}
		$fileList = array();
		while( false != ($file = readdir($handle)) ) {
			if($file == '.' || $file == '..') {
				continue;
			}
			$fileInfo['Name'] = $file;
			$fileInfo['IsDir'] = is_dir($path . $file);
			$fileInfo['Modified'] = filemtime($path . $file);
			$fileList[] = $fileInfo;
		}
		closedir($handle);
		if($orderBy == "Name") {
			$fileList = sortArray($fileList, "Name", $orderType);
		} else if($orderBy == "Modified") {
			$fileList = sortArray($fileList, "Modified", $orderType);
		}
		return $fileList;
	}
	
	/**
	 * 查找当前项目中，支持HTTP请求的全部方法
	 * 
	 * @param $moduleName 值“*”表示全部
	 * @param $actionName 值“*”表示全部
	 */
	public static function searchAllMethod($moduleName="*", $actionName="*") {
		$classPath = __ROOT_PATH . "lib/classes/";
		$result = array();
		//搜索Module
		$modules = array();
		if($moduleName == "*") {
			$files = self::listFiles($classPath);
			$cnt = count($files);
			for($i=0; $i<$cnt; $i++) {
				if($files[$i]['IsDir'] == false) {
					continue;
				}
				$modules[] = $files[$i]['Name'];
			}
		} else {
			$modules[] = $moduleName;
		}
		//搜索Action
		foreach($modules as $module) {
			$modulePath = $classPath . $module ."/Action/";
			$actions = array();
			if($actionName == "*") {
				$files = self::listFiles($modulePath);
				$cnt = count($files);
				for($i=0; $i<$cnt; $i++) {
					if($files[$i]['IsDir']) {
						continue;
					}
					$name = $files[$i]['Name'];
					$pos = strpos($name, ".");
					if($pos == false) {
						continue;
					}
					$name = substr($name, 0, $pos);
					if(substr($name, -6) == "Action") { //Action类
						$actions[] = substr($name, 0, -6);
					}
				}
			} else {
				$actions[] = $actionName;
			}
			//搜索Method
			foreach($actions as $action) {
				$className = $module . "_" . $action . "Action";
				$aliasName = "";
				if($module == "Default") {
					$aliasName = $action . "Action";
				}
				if($aliasName != "" && class_exists($aliasName)) {
					$className = $aliasName;
				} else if(class_exists($className) == false) {
					continue; //不存在的Action	
				}
				$methods = get_class_methods($className);
				foreach($methods as $method) {
					if(strlen($method) <= 8 || substr($method, 0, 2) != 'do' || substr($method, -6) != 'Action') {
						continue;
					}
					$method = substr($method, 2, -6);
					$result[$module][$action][] = $method;
				}
			}
		}
		return $result;
	}
	
}
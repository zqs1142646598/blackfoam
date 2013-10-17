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

class FastPHP_Cache {
	
	/**
	 * 取得缓存的文件（自动反序列化）
	 * @param $file - 缓存文件路径
	 * @param $notCheckExpire - 不检查缓存的过期时间（默认为false）
	 * @return 找不到数据，则返回false。否则，返回反序列化后的数据
	 */
	public static function getFileCache($file, $notCheckExpire=false) {
		if(file_exists($file) == false) return false;
		$data = file_get_contents($file);
		if(($pos=strpos($data, "\n")) === false) {
			return false;
		}
		$expireTime = trim(substr($data, 0, $pos));
		if($notCheckExpire == false && $expireTime > 0 && time() >= $expireTime) {
			return false;
		}
		return unserialize(substr($data, $pos+1));
	}
	
	/**
	 * 保存缓存文件
	 * 
	 * @param $file - 缓存文件名
	 * @param $data - 数据内容（mixed type，自动序列化)
	 * @param $cacheTime - 缓存时间（-1表示永不过期，相当于查找缓存数据时，不检查过期时间）
	 */
	public static function setFileCache($file, $data, $cacheTime=-1) {
		if(file_exists(dirname($file)) == false) mkdir(dirname($file), 0777, true);
		$expireTime = $cacheTime < 0 ? 0 : time() + $cacheTime;
		$str = $expireTime."\n".serialize($data);
		file_put_contents($file, $str);
	}
	
}
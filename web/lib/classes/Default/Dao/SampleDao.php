<?php
/**
 * Project:     ActionPHP (The MVC Framework) 
 * File:        SampleDao.php
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

class SampleDao {

	/**
	 * 自动创建数据表
	 */
	public static function autoCreateTable() {
		$sql = "CREATE TABLE IF NOT EXISTS `fastphp_SampleTable` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `VisitKey` varchar(32) COLLATE utf8_bin NOT NULL,
			  `RemoteIP` varchar(32) COLLATE utf8_bin NOT NULL,
			  `UserAgent` varchar(255) COLLATE utf8_bin NOT NULL,
			  `VisitCount` int(11) COLLATE utf8_bin NOT NULL,
			  `CreateTime` datetime NOT NULL,
			  `ChangeTime` datetime NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY (`VisitKey`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
		DBQuery::instance()->executeUpdate($sql);
	}
	
	/**
	 * 取得分页结果
	 * @param unknown_type $params
	 * @param unknown_type $ps
	 * @param unknown_type $pn
	 */
	public static function fetchList($params, $ps, $pn=1) {
		$params = DBQuery::filter($params);
		$sql = "SELECT * FROM fastphp_SampleTable ORDER BY ID DESC";
		$result = DBQuery::instance()->selectData($sql, $ps, $pn);
		return $result;
	}

	/**
	 * 取得单条详细
	 * @param unknown_type $id
	 */
	public static function getIDByVisitKey($visitKey) {
		$visitKey = DBQuery::filter($visitKey);
		$sql = "SELECT ID FROM fastphp_SampleTable WHERE VisitKey='{$visitKey}'";
		$id = DBQuery::instance()->getOne($sql);
		return $id;
	}

	/**
	 * 取得单条详细
	 * @param unknown_type $id
	 */
	public static function getDetail($id) {
		$id = DBQuery::filter($id);
		$sql = "SELECT * FROM fastphp_SampleTable WHERE ID='{$id}'";
		$row = DBQuery::instance()->getRow($sql);
		return $row;
	}

	/**
	 * 插入一条数据
	 * @param unknown_type $newRecord
	 */
	public static function installIt($newRecord) {
		$newRecord['CreateTime'] = getDateTime();
		$newRecord['ChangeTime'] = getDateTime();
		$sql = "INSERT INTO fastphp_SampleTable".DBQuery::toInsertStr($newRecord);
		DBQuery::instance()->executeUpdate($sql);
		return DBQuery::instance()->getInsertID();
	}

	/**
	 * 更新
	 * @param unknown_type $newRecord
	 */
	public static function updateIt($id, $newRecord) {
		$oldRecord = self::getDetail($id);
		if($oldRecord == null) { //旧记录不存在
			return false;
		}
		//比较新旧记录
		$updateStr = DBQuery::toUpdateStr($oldRecord, $newRecord);
		if($updateStr == "") { //没有变化，无需更新
			return true;
		}
		$updateStr .= ",ChangeTime='".getDateTime()."'";
		$sql = "UPDATE fastphp_SampleTable SET {$updateStr} WHERE ID='{$id}'";
		DBQuery::instance()->executeUpdate($sql);
		return true;
	}
	
}
<?php
class Admin_ManagerDao {
	
	/**
	 * 功能描述： 管理员登录
	 * 
	 * @param string $userName
	 * @param string $password
	 * 
	 * @return array status=1 登录失败 ; status=2登录成功; msg 操作提示; info 会员信息
	 */
	static function login($userName, $password) {
		$userName = DBQuery::filter($userName);
		$password = DBQuery::filter($password);
		
		$data = array('status'=>1, msg=>'账号或密码错误');
		if (empty($userName) || empty($password)) return $data;
		
		$sql = "SELECT * FROM `manager` WHERE `user_name`='{$userName}'";
		$row = DBQuery::instance()->getRow($sql);
		
		if (!empty($row)) {
			if ($row['password'] && $row['password']==md5(md5($password))) {
				$data['status'] = 2;
				$data['msg'] = '登录成功';
				$data['info'] = $row;
			}
		}
		return $data;
	}
	
}
<?php
class Admin_PublicAction extends BaseAction {
	function check() {} // 避免做登录检查
	
	function doLoginAction() {
		if (!empty($_POST)) {
			$userName = $_POST['username'];
			$password = $_POST['password'];
				
			$info = Admin_ManagerDao::login($userName, $password);
			if ($info['status']==2) {
				$_SESSION['user_name'] = $userName;
				$_SESSION['user_id'] = $info['info']['id'];
				redirect301('index.php?actionkey=Admin_Index');
			} else {
				$this->smarty->assign('error_msg', $info['msg']);
				$this->display();
			}
				
		} else {
			$this->display();
		}
	}
	
	function doLogoutAction() {
	
	}
}
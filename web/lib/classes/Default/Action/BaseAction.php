<?php
/**
 * 响应对象,保存了用于在View层显示的数据
 * @author XuLH <hansen@fastphp.org>
 * @date 2006-06-17
 */
abstract class BaseAction extends FastPHP_ActionClass {
	private $displayDisabled = false;
	private $startTime = 0;
	
	/* 检查入力参数,若是系统错误(严重错误,则抛出异常) */
	protected function check() {
		// 登录检查
		if (empty($_SESSION)) {
			redirect301('/index.php?actionkey=Admin_Public.Login');
		}
	}

	/* 资源回收 */
	protected function release() { }

	/* 禁用显示 */
	public function setDisplayDisabled($flag) {
		$this->displayDisabled = $flag;
	}
	
	public function beforeExecute() {
		$this->startTime = microtime(true);
		session_start();
		header("Content-type: text/html; charset=".__CHARSET);
	}
	
	public function beforeDisplay() {
		//设置默认值(项目相关)
		$this->smarty->assign("__DOCTYPE", '<!DOCTYPE HTML PUBLIC '
			. '"-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">');
		$this->smarty->assign("__CHARSET", __CHARSET);
	}
	
	public function occurException(Exception $e) {
		return true; //表示继续执行默认规则 
	}
	
	public function afterExecute() {
		if($this->displayDisabled == false && $this->isAjaxFlag == false) {
			logDebug("<center>Page Execution Time: <font color='red'>"
				.round(microtime(true)-$this->startTime, 3)."</font>s</center>");
		}
		
	}
}

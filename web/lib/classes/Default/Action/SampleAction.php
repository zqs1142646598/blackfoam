<?php
class SampleAction extends BaseAction {
	
	public function doIndexAction() {
		//显示默认TPL: /tpls/Default/Sample/Index.tpl
		$this->display();
	}
	
	/**
	 * 多CSS，多JS文件演示
	 */
	public function doDialogAction() {
		//显示默认TPL: /tpls/Default/Sample/Dialog.tpl
		$this->display();
	}
	
	/**
	 * 基本的DB操作演示
	 */
	public function doVisitLogAction() {
		//1. 自动创建测试数据表
		SampleDao::autoCreateTable();
		//2. 生成访问者标识符
		$visitKey = md5($_SERVER['REMOTE_ADDR'].'-'.$_SERVER['HTTP_USER_AGENT']);
		//3. 根据访问者标识符，找存在记录
		$id = SampleDao::getIDByVisitKey($visitKey);
		//4. 组建新记录
		$newRecord = array(
			'RemoteIP' => $_SERVER['REMOTE_ADDR'],
			'UserAgent' => $_SERVER['HTTP_USER_AGENT'],
			'VisitKey' => $visitKey,
		);
		//5. 更新到DB
		if($id != null) { //已存在，作update操作
			$oldRecord = SampleDao::getDetail($id);
			$newRecord['VisitCount'] = $oldRecord['VisitCount'] + 1;
			SampleDao::updateIt($id, $newRecord);
		} else { //作insert操作
			$newRecord['VisitCount'] = 1;
			SampleDao::installIt($newRecord);
		}
		//6. 分页取列表显示
		//   请求参数中的
		$pn = $this->request->get("pn"); //分页号
		$params = array();
		$result = SampleDao::fetchList($params, 10, $pn);
		//   结果传递给TPL
		$this->smarty->assign("result", $result);
		
		//显示默认TPL: /tpls/Default/Sample/VisitLog.tpl
		$this->display();
	}
	
}
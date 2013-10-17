<?php
class NotFoundAction extends BaseAction {
	
	public function doIndexAction() {
		header("HTTP/1.1 404 NotFound");
		echo "404 NotFound";
	}
	
	/**
	 * 程序异常处理
	 */
	public function doExceptionAction() {
		header("HTTP/1.1 500 Internal Server Error");
		echo "500 Internal Server Error";
	}
}
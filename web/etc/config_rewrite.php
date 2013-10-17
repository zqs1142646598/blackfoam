<?php
/**
 * URL重写规则配置
 * 若需启用，在define.php中，配置：
 * 		define("__REWRITE_RULE_MODE", "CLOSE");
 * 		MODE可选值：
 * 			CLOSE - 关闭（这是默认状态）
 * 			BASIC - 附加参数加问号存在于URL中
 * 			BASIC_R301 - 同上，检查URl唯一性，并跳转
 * 			PERFECT - 附加参数转换格式为 .htm
 * 			PERFECT_R301 - 同上，检查URl唯一性，并跳转
 * rule 对应的 URL 规则相对于 __HOME_URL
 * 
# URL重写规则配置参考（Apache版）
RewriteEngine On
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
RewriteRule .*	/action.php [L]
 */

$_FASTPHP_REWRITE_RULE = array(
	'Home' => array('rule'=>'/'),
	'Sample' => array('rule'=>'/sample/', 'type'=>array('sc'=>'[a-z]+')),
	'Sample.Dialog' => array('rule'=>'/dialog/{$Dialog}', 'type'=>array('Dialog'=>'\w+')),
	'NotFound' => array('mode'=>'CLOSE'),
);


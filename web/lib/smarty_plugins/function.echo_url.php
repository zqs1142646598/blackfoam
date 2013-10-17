<?php


function smarty_function_echo_url($params, &$smarty) {
	$type = $params['type'];
	unset($params['type']);
	echo RewriteHelper::getURL($type, $params);
}
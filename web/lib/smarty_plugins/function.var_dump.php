<?php


function smarty_function_var_dump($params, &$smarty)
{
	$v = $params['var'];
	echo '<pre>';
	var_dump($v);
	echo '</pre>';
}


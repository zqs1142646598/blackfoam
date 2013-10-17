<?php
require(dirname(__FILE__) . "/global.php");

$fastphp_actionkey = FastPHP_Rewrite::parseRequest();

fastphp_run_action($fastphp_actionkey);


<?php

function smarty_modifier_showdate($datetime) {
	echo substr($datetime, 0, 10); 
}
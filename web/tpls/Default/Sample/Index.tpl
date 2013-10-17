<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
{*
<link href="/res/css/sample/global.css" rel="stylesheet" />
<-- 
*}
{load_css
	file1='global.css'
}
{load_js
	 file1='jquery.js'
}
{* --> *}
</head>

<body>
{include file="Default/Sample/Include/Header.tpl"}
<div style="width:950px; height: 300px; margin:20px auto; ">
这是的Sample首页
<br />
<br />
<ul>
<li><a href="{echo_url type='Sample.Dialog'}">jQuery Dialog (多JS，多CSS文件装载演示)</a></li>
<li><a href="{echo_url type='Sample.VisitLog'}">VisitLog (基本DB操作演示)</a></li>
</ul>
</div>
{include file="Default/Sample/Include/Footer.tpl"}
</body>
</html>
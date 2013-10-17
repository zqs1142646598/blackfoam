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
	file2='jquery-ui/jquery.ui.core.css'
}
{load_js
	 file1='jquery.js'
}
{* --> *}
{literal}
<style type="text/css">
td,th {padding: 3px;}
</style>
{/literal}
</head>

<body>
{include file="Default/Sample/Include/Header.tpl"}
<div style="width:950px; height: 300px; margin:20px auto; ">

<strong>最近来访记录</strong>
&nbsp;&nbsp; <a href="{echo_url type='Sample'}">Back</a><br/>
<br>
共有<font>{$result.count}</font>条
<table border="1">
<tr>
	<th>RemoteIP</th>
	<th>UserAgent</th>
	<th>VisitCount</th>
	<th>CreateTime</th>
	<th>ChangeTime</th>
</tr>
{foreach item=row from=$result.data}
<tr>
	<td>{$row.RemoteIP}</td>
	<td>{$row.UserAgent}</td>
	<td>{$row.VisitCount}</td>
	<td>{$row.CreateTime}</td>
	<td>{$row.ChangeTime}</td>
</tr>
{/foreach}
</table>

</div>
{include file="Default/Sample/Include/Footer.tpl"}
</body>
</html>
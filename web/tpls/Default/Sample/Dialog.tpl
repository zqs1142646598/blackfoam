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
	file3='jquery-ui/jquery.ui.theme.css'
	file4='jquery-ui/jquery.ui.dialog.css'
}
{load_js
	 file1='jquery.js'
	 file2='plugins/jquery.ui.core.js'
	 file3='plugins/jquery.ui.position.js'
	 file4='plugins/jquery.ui.widget.js'
	 file5='plugins/jquery.ui.mouse.js'
	 file6='plugins/jquery.ui.draggable.js'
	 file7='plugins/jquery.ui.resizable.js'
	 file8='plugins/jquery.ui.dialog.js'
}
{* --> *}
{literal}
<script>
function completeDialog() {
	// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
	$( "#dialog:ui-dialog" ).dialog( "destroy" );

	$( "#dialog-message" ).dialog({
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
}
$(function() {
	
});
</script>
{/literal}
</head>

<body>
{include file="Default/Sample/Include/Header.tpl"}
<div style="width:950px; height: 300px; margin:20px auto; ">

<input type="button" name="button" value="Complete Dialog" onClick="completeDialog()"/>
&nbsp;&nbsp; <a href="{echo_url type='Sample'}">Back</a><br/>
<div id="dialog-message" title="Download complete" style="display:none;">
	<p>
		<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
		Your files have downloaded successfully into the My Downloads folder.
	</p>
	<p>
		Currently using <b>36% of your storage space</b>.
	</p>
</div>

</div>
{include file="Default/Sample/Include/Footer.tpl"}
</body>
</html>
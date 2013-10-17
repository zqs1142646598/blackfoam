/** status in ('NOTICE', 'VALID', 'ERROR'), default is 'NOTICE' **/ 

function stdform_message(obj, msg, status) {
	if(msg == null) msg = "";
	if(status != null) status = status.toUpperCase();
	var tips_line = $(obj).parent().find(".tips_line");
	
	if(tips_line == null && $(obj).parent().parent() != null) {
		tips_line = $(obj).parent().parent().find(".tips_line");
	}
	var status_icon = $(obj).parent().find(".status_icon");
	
	if(tips_line.size() > 1) tips_line = null; 
	
	if(tips_line != null) {
		//alert(msg);
		tips_line.html(msg);
	}
	//alert(obj);
	
	$(obj).removeClass('focus');
	$(obj).removeClass('error');
	if(status_icon != null) status_icon.removeClass('status_error');
	if(status == 'VALID') {
		if(status_icon != null) {
			status_icon.removeClass('status_error');
			status_icon.addClass('status_valid');
		}
		if(tips_line != null) {
			tips_line.removeClass('tips_error');
			tips_line.addClass('tips_valid');
		}
		$(obj).removeClass('focus');
		$(obj).removeClass('error');
	} else if(status == 'ERROR') {
		if(status_icon != null) {
			status_icon.removeClass('status_valid');
			status_icon.addClass('status_error');
		}
		if(tips_line != null) {
			tips_line.removeClass('tips_valid');
			tips_line.addClass('tips_error');
		}
		$(obj).removeClass('focus');
		$(obj).addClass('error');
	} else {
		if(tips_line != null) {
			tips_line.removeClass('tips_valid');
			tips_line.removeClass('tips_error');
		}
		$(obj).removeClass('error');
		$(obj).addClass('focus');
	}
}
//正则匹配
var validateRegExp={
	url:"^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$", //url
	email: "^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$", //邮件
	num:"^[1-9]\\d*\\.\\d*$|^0\\.\\d*[1-9]\\d*$|^[1-9][0-9]*$", //数字
	mobile: "0?(13|15)[0-9]{9}$", //手机 
	date: "^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$", //日期 
	phone: "/(^[0-9]{3,4}\-[0-9]{3,8}$)|(^[0-9]{3,8}$)|(^\([0-9]{3,4}\)[0-9]{3,8}$)|(^0{0,1}13[0-9]{9}$)/", //电话
	int:"^[^0][0-9]*$" //正整数
};


//验证规则
var validateRules={
	isNull:function(str){
		return (typeof str!="string" || str=="");
	},
	isUrl:function(str){
		return new RegExp(validateRegExp.url).test(str);
	},
	isEmail:function(str){
		return new RegExp(validateRegExp.email).test(str);
	},
	isMobile:function(str){
		return new RegExp(validateRegExp.mobile).test(str);
	},
	isPhone:function(str){
		return new RegExp(validateRegExp.phone).test(str);
	},
	isPrice:function(str){
		return new RegExp(validateRegExp.num).test(str);
	},
	isDate:function(str){
		return new RegExp(validateRegExp.date).test(str);
	},
	isInt:function(str){
		return new RegExp(validateRegExp.int).test(str);
	}
};
//函数
var validateFunction={
	url:function(option){
		var str = option.value;
		return validateRules.isUrl(str);
	},
	email:function(option){
		var str = option.value;
		return validateRules.isEmail(str);
	},
	mobile:function(option){
		var str = option.value;
		return validateRules.isMobile(str);
	},
	phone:function(option){
		var str = option.value;
		return validateRules.isPhone(str);
	},
	num:function(option){
		var str = option.value;
		return validateRules.isPrice(str);
	},
	date:function(option){
		var str = option.value;
		return validateRules.isDate(str);
	},
	int:function(option){
		var str = option.value;
		return validateRules.isInt(str);
	}
};

function __getPrompt(inputname, attrname) {
	if(typeof validatePrompt[inputname] != "object") return null;
	if(typeof validatePrompt[inputname][attrname] != "string") return null;
	if(validatePrompt[inputname][attrname] == "") return null;
	return validatePrompt[inputname][attrname];
}

function __stdform_check(obj) {
	var inputValue = obj.value;
	if(typeof(obj.__stdform_lastValue) != "undefined" && obj.__stdform_lastValue == inputValue) {
		return obj.__stdform_lastResult;
	}
	obj.__stdform_lastValue = inputValue;
	obj.__stdform_lastResult = true;
	if(typeof(stdform_check) == "function" && stdform_check(obj) == false) {
		obj.__stdform_lastResult = false;
		return false;
	}

	var inputname = obj.name;
	var status = 'VALID';
	var msg = '';
	
	if(validateRules.isNull(inputValue)){
		if(__getPrompt(inputname, 'isNull') != null) {
			status = 'ERROR';
			msg = __getPrompt(inputname, 'isNull');
		} else {
			status = "NOTICE";
		}
	}
	else if(__getPrompt(inputname, 'isUrl') != null && !validateFunction.url(obj)) {
		//url正则判断
		status = 'ERROR';
		msg = __getPrompt(inputname, 'isUrl');
	}
	else if(__getPrompt(inputname, 'isMobile') != null && !validateFunction.mobile(obj)){
		//手机号正则判断
		
		status = 'ERROR';
		msg = __getPrompt(inputname, 'isMobile');
	}
	else if(__getPrompt(inputname, 'isPhone') != null && !validateFunction.phone(obj)){
		//固定电话号码正则判断
		status = 'ERROR';
		msg = __getPrompt(inputname, 'isPhone');
	}
	else if(__getPrompt(inputname, 'isEmail') != null && !validateFunction.email(obj)){
		//邮箱正则判断
		status = 'ERROR';
		msg = __getPrompt(inputname, 'isEmail');
	}
	else if(__getPrompt(inputname, 'isPrice') != null && !validateFunction.num(obj)){
		//价格正则判断
		status = 'ERROR';
		msg = __getPrompt(inputname, 'isPrice');
	}
	else if(__getPrompt(inputname, 'isDate') != null && !validateFunction.date(obj)){
		//日期正则判断
		status = 'ERROR';
		msg = __getPrompt(inputname, 'isDate');
	}
	else if(__getPrompt(inputname, 'isInt') != null && !validateFunction.int(obj)){
		//正整数正则判断
		status = 'ERROR';
		msg = __getPrompt(inputname, 'isInt');
	}
	else if(__getPrompt(inputname, 'unSelect') != null && inputValue == 0){
		//未选择select中的类别
		status = 'ERROR';
		msg = __getPrompt(inputname, 'unSelect');
		
	}
	
	stdform_message(obj,msg,status);
	obj.__stdform_lastResult = (status != 'ERROR');
	return obj.__stdform_lastResult;
}

$(document).ready(function() {
$("form").each(function(i){
	if($(this).attr("id") == null || $(this).attr("id").substring(0, 7) != 'stdform') {return;}
	$(this).find("input,textarea,select").each(function(j) {
		if(this.tagName == "INPUT" && this.type != "text" && this.type != "password"
			&& this.type != "checkbox" && this.type != "radio" && this.type != "file") return;
		if(__getPrompt(this.name, "onFocus") != null) {
			$(this).focus(function() {
				if(typeof(stdform_focus) == "function" && stdform_focus(this) == false) return;
				
				var inputname = this.name;
				
				var msg = __getPrompt(inputname, 'onFocus');
				
				var status = 'NOTICE';
				stdform_message(this,msg,status);
			});
			$(this).blur(function() { __stdform_check(this); });
		}
		$(this).blur(function() { __stdform_check(this); });
	});
	$(this).submit(function() {
		var flag = 0;
		
		$(this).find("input,textarea,select").each(function(j) {
			if(this.tagName == "INPUT" && this.type != "text" && this.type != "password"
				&& this.type != "checkbox" && this.type != "radio" && this.type != "file") return;
			
			if(__stdform_ishide(this) == false) {
				if(__stdform_check(this) == false) flag = 1;
			}
		});
		if(flag == 1) return false;
		else if(typeof(stdform_submit) == "function" && stdform_submit(this) == false) return false;
		else return true;
	});
	
});
});

function __stdform_ishide(obj){
	if(typeof obj != "object") return false;
	
	if(typeof $(obj).get(0).style == "object" && $(obj).get(0).style.display == 'none') {
		return true;
	}
	
	if($(obj).parent() == null) return false;
	return __stdform_ishide($(obj).parent().get(0));
}

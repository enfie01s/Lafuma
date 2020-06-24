var reEmail=/^[0-9a-zA-Z_\.-]+@[0-9a-zA-Z_\.-]+\..{2,8}$/;

function checkEmail(str){
	var at="@";
	var dot=".";
	var lat=str.indexOf(at);
	var lstr=str.length;
	var ldot=str.indexOf(dot);
	if (str.indexOf(at)==-1){
		return false;
	}
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		return false;
	}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		return false;
	}
	if (str.indexOf(at,(lat+1))!=-1){
		return false;
	}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		return false;
	}
	if (str.indexOf(dot,(lat+2))==-1){
		return false;
	}
	if (str.indexOf(" ")!=-1){
		return false;
	}
	return true;
}

// Used in all pages to submit a form and optionally set a hidden 
// form varaible called 'navigate' to direct navgiation
function submitForm(formName, navigateValue) {
	if (navigateValue != null && navigateValue != "") {
		document.forms[formName].navigate.value = navigateValue;
	}
    document.forms[formName].submit();
}
var totalHeight;
var rollOpen;
var rollClose;
function divroller(divId,eventName)
{
	divObj=document.getElementById(divId);
	totalHeight=document.getElementById(divId).scrollHeight;
	if(divObj.offsetHeight>0&&eventName=="mouseout")
		{clearTimeout(rollOpen);divrollclose();}
	else
		{clearTimeout(rollClose);divrollopen();}
}
function divrollopen()
{
	if(divObj.offsetHeight<totalHeight)
	{
		divObj.style.height=divObj.offsetHeight+2+"px";
		rollOpen=setTimeout("divrollopen()",0);
	}
}
function divrollclose()
{
	if(divObj.offsetHeight>0)
	{
		divObj.style.height=divObj.offsetHeight-2+"px";
		rollClose=setTimeout("divrollclose()",0);
	}
}
function matchbill()
{
	document.forms[1].deliver_firstnamedeliver_.value=document.forms[1].firstnamebillingaddy.value;
	document.forms[1].deliver_lastnamedeliver_.value=document.forms[1].lastnamebillingaddy.value;
	document.forms[1].deliver_address1deliver_.value=document.forms[1].address1billingaddy.value;
	document.forms[1].deliver_address2deliver_.value=document.forms[1].address2billingaddy.value;
	document.forms[1].deliver_citydeliver_.value=document.forms[1].citybillingaddy.value;
	document.forms[1].deliver_statedeliver_.value=document.forms[1].statebillingaddy.value;
	document.forms[1].deliver_postcodedeliver_.value=document.forms[1].postcodebillingaddy.value;
	document.forms[1].deliver_countrydeliver_.value=document.forms[1].countrybillingaddy.value;
	document.forms[1].deliver_phonedeliver_.value=document.forms[1].phonebillingaddy.value;
}
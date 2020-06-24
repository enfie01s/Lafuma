//<![CDATA[

//For checking Null values

function isNull(aStr)
{
	var index;	
	for (index=0; index < aStr.length; index++)
	if (aStr.charAt(index) != ' ')
		return false;
	return true;
}

function PopWindow()
{
	window.open("","windowname","toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=425,height=425,top=50,left=50");
}

//For checking invalid E-Mail address

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



function checkphone(val){
	str = "^0";	
	var reg = new RegExp(str);
	return reg.test(val);
}



// For checking and allowing only certain numeric values for Quantity

function isNumeric(val,allow_dec,allow_neg){
	var str = "";
	if(allow_neg)         //value can be negative
	{
		str += "^-";
	}		
	if(allow_dec)         //value can be decimal 
	{
		str += "[0-9]{1,}\.{0,1}"; 
	}	
	str += "^[0-9]{1,}$";
	var reg = new RegExp(str);
	return reg.test(val);
}

	

function isAlphaNumeric(varData){
	varRegExp = new RegExp("^[A-Za-z0-9_]+$");
	if(!varRegExp.test(varData))
	{
		return true
	}	
	return false
}



function IsValidImageName(strVal){
	nNoOfArguments = IsValidImageName.arguments.length;
	//if parameter is not supplied
	if(nNoOfArguments < 1){
		return false;
	}
	//valid characters a supplied string value can have

	var sValidChars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-";
	strVal = new String(strVal);	//convert the value to a string object
	var bReturn = true	
	var i = new Number(0);
	while ((bReturn) && (i < strVal.length)){
		bReturn = (sValidChars.indexOf(strVal.charAt(i)) >= 0)
		i++
	}
	return (bReturn);	
}

/* 
	Date Should be in MM/DD/YY
	date 1 > date 2 return 1
	date 1 < date 2 return -1
	date 1 = date 2 return 0
*/

function compareDates(dt1,dt2)
{
	var datepart1 = dt1.split("/");
	var datepart2 = dt2.split("/");
	for(i=0;i<datepart1.length;i++)
	{
		datepart1[i] = parseInt(parseFloat(datepart1[i]));
		datepart2[i] = parseInt(parseFloat(datepart2[i]));	
	}	
	if(datepart1[2] > datepart2[2])
		return 1;
	else if(datepart1[2] < datepart2[2])	
		return -1;
	else if(datepart2[2] == datepart1[2])
	{
		if(datepart1[0] > datepart2[0])
			return 1;
		else if(datepart1[0] < datepart2[0])	
			return -1;
		else if(datepart1[0] == datepart2[0])
		{
			if(datepart1[1] > datepart2[1])
				return 1;
			else if(datepart1[1] < datepart2[1])	
				return -1;	
		}
	}
	return 0;	
}

function isdefined(variable){
	return (typeof(variable) == "undefined")?  false: true;
} 

function isDigit(num) {
	if (num.length>1){return false;}
	var string="1234567890";
	if (string.indexOf(num)!=-1){return true;}
	return false;
}

function getValue(frm, fieldname){
	var field = eval("document." + frm + "."+fieldname);
	if(isdefined(field)){
		return field.value;
	}
	else
	{
		return false;
	}
}

function trimchar(pstr){
	var lenstr = pstr.length;
	for(var i = 0 ; pstr.charAt(i) == " "; i++);
	for(var j = pstr.length - 1; pstr.charAt(j)== " "; j--);

	if (i > j)
		pstr = "";
	else
		pstr = pstr.substring(i,j);

	return pstr;
}

function mod10 (strNum) {
	var nCheck = 0;
	var nDigit = 0;
	var bEven = false;
	for (n = strNum.length - 1; n >= 0; n--)
	{
		var cDigit = strNum.charAt (n);
		if (isDigit (cDigit))
		{
			var nDigit = parseInt(cDigit, 10);
			if (bEven)
			{
				if ((nDigit *= 2) > 9)
					nDigit -= 9;alert(nDigit);
			}
			nCheck += nDigit;
			bEven = ! bEven;
		}
		else if (cDigit != ' ' && cDigit != '.' && cDigit != '-')
		{
			return false;
		}
	}
	return (nCheck % 10) == 0;
}

function expired( month, year ){
	var now = new Date();
	var expiresIn = new Date(year,month,0,0,0);
	expiresIn.setMonth(expiresIn.getMonth()+1);
	if( now.getTime() < expiresIn.getTime() ) return false;
	return true;
}

function isBeforeNow(cardStartMonth, cardStartYear){//validate start date
	var now = new Date();
	var startsIn = new Date(cardStartYear,cardStartMonth,0,0,0);
	startsIn.setMonth(startsIn.getMonth()-1);
	if(startsIn.getTime() < now.getTime() ) 
	{
		return false;
	}
	return true;
}

function validatefields(pstCardStartMonth,pstCardStartYear,pstIssuenumber) {
	if (pstIssuenumber !="") { // filled in, so we need to verify
		if(isNaN(parseInt(pstIssuenumber))){
			alert("Sorry! Please enter a valid issue number.");
			return false;
		}
	}
	if (pstCardStartMonth!="" && pstCardStartYear !=""){ // filled in, so we need to verify
		if (isBeforeNow(pstCardStartMonth, pstCardStartYear))
		{ 
			alert("Sorry! The start date you have entered would make this card invalid.");
			return false;
		}
	}
	return true;
}

function validateCard(cnum,cardType,cardMonth,cardYear,cardStartMonth, cardStartYear, issuenumber, pstCardType, ccv2) {//add parameters for start date and issue number
	if( cnum.length == 0 ) {   
		alert("Please enter a valid card number.");
		return false; 
	}
	for( var i = 0; i < cnum.length; ++i ) { 
		var c = cnum.charAt(i);
		if( c < '0' || c > '9' ) {
			alert("Please enter a valid card number. Use only digits. do not use spaces or hyphens.");
			return false;
		}
	}
	var length = cnum.length;
	switch( cardType ) {
		case 'm':
		case 'MC':
		case 'Mastercard':
			if( length != 16 ) {
				alert("Please enter a valid MasterCard number.");
				return;
			}
			var prefix = parseInt( cnum.substring(0,2));
			if( prefix < 51 || prefix > 55) {
				alert("Please enter a valid MasterCard Card number.");
				return;
			}
			break;
		case 'v':
		case 'VISA':
		case 'DELTA':
			if(length != 16 && length != 13) {
				alert("Please enter a valid Visa Card number.");
				return;
			}
			var prefix = parseInt( cnum.substring(0,1));   
			if( prefix != 4 ) {
				alert("Please enter a valid Visa Card number.");
				return;
			}
			if(isNull(ccv2) || !isNumeric(ccv2)){//validation for ccv2
				alert("Please enter a valid security code.");
				return false;
			}
			break;
		case 'a': 
		case 'AMEX':
			if( length != 15 ) {
				alert("Please enter a valid American Express Card number.");
				return;
			}
			var prefix = parseInt( cnum.substring(0,2));  
			if(prefix != 34 && prefix != 37 ) {
				alert("Please enter a valid American Express Card number.");
				return;
			}
			break;
		case 'd':
		case 'DISCOVER':
			if( length != 16 ) {
				alert("Please enter a valid Discover Card number.");
				return;
			}
			var prefix = parseInt( cnum.substring(0,4));  
			if( prefix != 6011 ) {
				alert("Please enter a valid Discover Card number.");
				return;
			}
			break;
		case 'DC':
		case 'DinnersClub':
			if( length != 14 ) {
				alert("Please enter a valid Dinners Club Card number.");
				return;
			}
			var prefix = parseInt( cnum.substring(0,3)); 
			if((prefix < 300 || prefix > 305) || (prefix != 36 && prefix != 38 )) {
				alert("Please enter a valid Discover Card number.");
				return;
			}
			break;
	}
	if( !mod10( cnum ) ) {
		alert("Sorry! this is not a valid credit card number.");
		return false;
	}
	if(isNull(ccv2) || !isNumeric(ccv2)){//validation for ccv2
		alert("Please enter a valid security code.");
		return false;
	}
	if(cardMonth == "" || cardYear == "")//check the expiry date is not empty
	{
		alert("Please enter the expiry date for this card.");
		return false;
	}
	else
	{
		if( expired( cardMonth, cardYear ) ) {   
			alert("Sorry! The expiry date you have entered would make this card invalid.");
			return false;
		}
	}
	/*code to check start date and/or issue number depending upon card type*/

	if (pstCardType == "SWITCH" || pstCardType == "Maestro" || pstCardType == "SOLO") 
	{
		if ((cardStartMonth !="" && cardStartYear !="") || issuenumber !="") 
		{ 
			if( validatefields(cardStartMonth,cardStartYear,issuenumber)){
				return true;
			}
			else
			{
				return false;
			} 
		}
		else
		{
			alert("Please enter an issue number or start date for this card.");
			return false;
		}
		return true;
	}
	else
	{
		if( validatefields(cardStartMonth,cardStartYear,issuenumber)){
			return true;
		}
		else
		{
			return false;
		}
	}
	return true;
}

function getCKDRadio(radio) {
	var selected = false;
	var ckVal = "none";
	if(radio.length)
	{
		for (var i = 0; i < radio.length; i++) 
		{
			if (radio[i].checked && radio[i].name=='paymethod')  
			{
				selected = true;
				ckVal = radio[i].value;
				break;
			}
		}
	}
	else
	{
		selected = true;
	}
	if (selected == false)
	{
		alert("Please select a payment option.");
	}
	return ckVal;
}

function checkPAY(form){
	var radio=getCKDRadio(form);
	if(radio!="none"){
		var ckVal = radio;
		if(ckVal == "cc"){
			if(form.cc_n> '9' ) {
				alert("Please enter a valid card number. Use only digits. do not use spaces or hyphens.");
				return false;
			}
		}
		var length = form.cc_number.length;
		var cardType = form.cc_type.value;
		switch( cardType ) {
			case 'm':
			case 'MC':
			case 'Mastercard':
				if( length != 16 ) {
					alert("Please enter a valid MasterCard number.");
					return;
				}
				var prefix = parseInt( form.cc_number.substring(0,2));
				if( prefix < 51 || prefix > 55) {
					alert("Please enter a valid MasterCard Card number.");
					return;
				}
				break;
			case 'v':
			case 'VISA':
				if(length != 16 && length != 13) {
					alert("Please enter a valid Visa Card number.");
					return;
				}
				var prefix = parseInt( form.cc_number.substring(0,1));
				if( prefix != 4 ) {
					alert("Please enter a valid Visa Card number.");
					return;
				}
				if(isNull(ccv2) || !isNumeric(ccv2)){//validation for ccv2
					alert("Please enter a valid security code.");
					return false;
				}
				break;
			case 'a': 
			case 'AMEX':
				if( length != 15 ) {
					alert("Please enter a valid American Express Card number.");
					return;
				}
				var prefix = parseInt( form.cc_number.substring(0,2));
				if(prefix != 34 && prefix != 37 ) {
					alert("Please enter a valid American Express Card number.");
					return;
				}
				break;
			case 'd':
			case 'DISCOVER':
				if( length != 16 ) {
					alert("Please enter a valid Discover Card number.");
					return;
				}
				var prefix = parseInt( form.cc_number.substring(0,4));
				if( prefix != 6011 ) {
					alert("Please enter a valid Discover Card number.");
					return;
				}
				break;
			case 'DC':
			case 'DinnersClub':
				if( length != 14 ) {
					alert("Please enter a valid Dinners Club Card number.");
					return;
				}
				var prefix = parseInt( form.cc_number.substring(0,3));
				if((prefix < 300 || prefix > 305) || (prefix != 36 && prefix != 38 )) {
					alert("Please enter a valid Discover Card number.");
					return;
				}
				break;
		}
		if( !mod10( form.cc_number ) || form.cc_number.value=="" ) {
			alert("Sorry! this is not a valid credit card number.");
			return false;
		}
		if(isNull(form.cv2.value) || !isNumeric(form.cv2.value)){//validation for ccv2
			alert("Please enter a valid security code.");
			return false;
		}
		if(form.cc_month[form.cc_month.selectedIndex].value == "" || form.cc_year[form.cc_year.selectedIndex].value == "")//check the expiry date is not empty
		{
			alert("Please enter the expiry date for this card.");
			return false;
		}
		else
		{
			if( expired( form.cc_month[form.cc_month.selectedIndex].value, form.cc_year[form.cc_year.selectedIndex].value ) ) {   
				alert("Sorry! The expiry date you have entered would make this card invalid.");
				return false;
			}
		}
		/*check start date and/or issue number depending upon card type*/
		if (cardType == "SWITCH" || cardType == "Maestro" || cardType == "SOLO")
		{
			if ((form.cc_start_month[form.cc_start_month.selectedIndex].value !="" && form.cc_start_year[form.cc_start_year.selectedIndex].value !="") || form.issuenumber !="") 
			{ 
				if( validatefields(form.cc_start_month,form.cc_start_year,form.issuenumber.value)){
					return true;
				}
				else 
				{
					return false;
				}  
			}
			else
			{
				alert("Please enter an issue number or start date for this card.");
				return false;
			}
			return true; 
		}
		else
		{
			if( validatefields(form.cc_start_month[form.cc_start_month.selectedIndex].value,form.cc_start_year[form.cc_start_year.selectedIndex].value,form.issuenumber.value)){
				return true;
			}
			else 
			{
				return false;
			}
		}
		return true;
	}
}

function validateForm(form){
	if(checkPAY(form)) 
		return true;
	else
		return false;
}

//]]>
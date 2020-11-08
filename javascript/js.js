var xmlHttp
var innerstr;

function changeColor1(val,Color,ii) {
   document.getElementById(val+'a'+ii).className=Color;
   document.getElementById(val+'b'+ii).className=Color;
   if (val=='tdvisits' || val=='tdsystem'){
   document.getElementById(val+'c'+ii).className=Color;
   }
}

function funcaccstats(str,str1,obj,val1)
{
if (val1!=''){
obj.innerHTML=val1;
}
xmlHttp=GetXmlHttpObject()
innerstr=str1;
if (xmlHttp==null)
{
alert ("Browser does not support HTTP Request")
return
} 
var url="index.php"
url=url+"?p=stats&p1="+str
url=url+"&sid="+Math.random()
xmlHttp.onreadystatechange=stateChanged ;
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
} 

function stateChanged() 
{ 
if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
{ 
document.getElementById(innerstr).innerHTML=xmlHttp.responseText 
} 
} 

function GetXmlHttpObject()
{ 
var objXMLHttp=null
if (window.XMLHttpRequest)
{
objXMLHttp=new XMLHttpRequest()
}
else if (window.ActiveXObject)
{
objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
}
return objXMLHttp
} 

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return curtop;
}
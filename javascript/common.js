 function OpenWindow(page,name,w,h,scroll)
 {
  setari = 'height='+h+',width='+w+',scrollbars='+scroll+',resizable';
  win = window.open(page,name,setari);
 }

 function changemodel(selected_model_id) {

  myArray=modelsArray;
  myForm=document.formarticle;
  var arrayLength = modelsID.length;
  var selMakeIndex = document.formarticle.make.selectedIndex;
  var selMake = document.formarticle.make.options[selMakeIndex].value;
  var modelIndex=0;
  document.formarticle.model.options.length = 0;
  for (var i=0;i<arrayLength;i++)  //For all makes
  {
    var mkSplitArray =  modelsID[i];
    if (selMake == mkSplitArray)  // For the choosen make
    {
      myForm.model.options[modelIndex] =   new Option (varall, 0);
      modelIndex++;
      for (var j=0;j<myArray[mkSplitArray].length;j++) //For all the models within
      {
        var splitArray = myArray[mkSplitArray][j].split("|");
        var modelName = splitArray[1];
        var modelId = splitArray[0];

          myForm.model.options[modelIndex] =   new Option (modelName, modelId);
          if (selected_model_id == modelId)
          {
            myForm.model.selectedIndex = modelIndex;
          }
          modelIndex++;
      } // for j
      break;
    }
  } // for i
}


 function changecity(selected_model_id) {

  myArray=modelscountryArray;
  myForm=document.formarticle;
  var arrayLength = modelscountryID.length;
  var selMakeIndex = document.formarticle.country.selectedIndex;
  var selMake = document.formarticle.country.options[selMakeIndex].value;
  var modelIndex=0;
  document.formarticle.city.options.length = 0;
  for (var i=0;i<arrayLength;i++)  //For all makes
  {
    var mkSplitArray =  modelscountryID[i];
    if (selMake == mkSplitArray)  // For the choosen make
    {
      myForm.city.options[modelIndex] =   new Option (varall, 0);
      modelIndex++;
      for (var j=0;j<myArray[mkSplitArray].length;j++) //For all the models within
      {
        var splitArray = myArray[mkSplitArray][j].split("|");
        var modelName = splitArray[1];
        var modelId = splitArray[0];

          myForm.city.options[modelIndex] =   new Option (modelName, modelId);
          if (selected_model_id == modelId)
          {
            myForm.city.selectedIndex = modelIndex;
          }
          modelIndex++;
      } // for j
      break;
    }
  } // for i
}

var browserinfos=navigator.userAgent
var ns4=document.layers
var ns6=document.getElementById&&!document.all&&!browserinfos.match(/Opera/)
var ie=document.all&&!browserinfos.match(/Opera/)
var ie5=document.all&&document.getElementById&&!browserinfos.match(/Opera/)
var opera=browserinfos.match(/Opera/)

function RunSlideShow( imageFiles, imageUrl, imageTitle, imageText, displaySecs)
{
  var imageSeparatorFiles = imageFiles.indexOf(";");
  var nextImage = imageFiles.substring(0,imageSeparatorFiles);

  imageUrl=imageUrl.replace("&amp;","&");

  var imageSeparatorUrl = imageUrl.indexOf(";");
  var nextUrl = imageUrl.substring(0,imageSeparatorUrl);

  var imageSeparatorTitle = imageTitle.indexOf(";");
  var nextTitle = imageTitle.substring(0,imageSeparatorTitle);

  var imageSeparatorText = imageText.indexOf(";");
  var nextText = imageText.substring(0,imageSeparatorText);

  if (ie || ie5)

  {
    document.getElementById('pictureName').style.filter="blendTrans(duration=2)";
    document.getElementById('pictureName').filters.blendTrans.Apply();

  }

  document.getElementById('pictureName').src = nextImage;
  document.getElementById('pictureUrl').href = nextUrl;
  document.getElementById('pictureUrl1').href = nextUrl;
  nextText = nextText.substring(0,200);
  document.getElementById('pictureText').innerHTML = nextText;
  document.getElementById('pictureUrl1').innerHTML = nextTitle;

  if (ie || ie5)

  {
    document.getElementById('pictureName').filters.blendTrans.Play();
  }

  var futureImages= imageFiles.substring(imageSeparatorFiles+1,imageFiles.length)
    + ';' + nextImage;
  var futureUrl= imageUrl.substring(imageSeparatorUrl+1,imageUrl.length)
    + ';' + nextUrl;
  var futureTitle= imageTitle.substring(imageSeparatorTitle+1,imageTitle.length)
    + ';' + nextTitle;
  var futureText= imageText.substring(imageSeparatorText+1,imageText.length)
    + ';' + nextText;
  futureText = futureText.replace("'","`");
  futureText = futureText.replace('"',"`");        
  setTimeout("RunSlideShow('"+futureImages+"','"+futureUrl+"','"+futureTitle+"','"+futureText+"',"+displaySecs+")",
    displaySecs*1000);
}

function setCookie(c_name,value,expiredays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate()+expiredays);
document.cookie=c_name+ "=" +escape(value)+
((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

var xmlHttp
var innerstr;

function funcget(str,email,str1,obj,val1)
{
var val = emailCheck(email);
if (!val)
{
    return false;
}
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
url=url+"?pp="+str
url=url+"&email="+email
url=url+"&sid="+Math.random()
xmlHttp.onreadystatechange=stateChanged ;
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}

function funcget1(str,make1,cat1,state1,city1,str1,obj,val1)
{

if (val1!=''){
obj.innerHTML='<font style="font-size:16px;">'+val1+'</font>';
}


xmlHttp=GetXmlHttpObject()
innerstr=str1;
if (xmlHttp==null)
{
alert ("Browser does not support HTTP Request")
return
}
var url="index.php"
url=url+"?pp="+str
url=url+"&country="+cat1
url=url+"&state="+state1
url=url+"&city="+city1
url=url+"&sid="+Math.random();

xmlHttp.onreadystatechange=stateChanged ;
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}


function funcget1cc(str,make1,cat1,str1,obj,val1)
{

if (val1!=''){
obj.innerHTML='<font style="font-size:16px;">'+val1+'</font>';
}


xmlHttp=GetXmlHttpObject()
innerstr=str1;
if (xmlHttp==null)
{
alert ("Browser does not support HTTP Request")
return
} 
var url="index.php"
if (innerstr=='provinceid'){
url=url+"?pp=province1"
}else{
url=url+"?pp="+str	
}
url=url+"&first="+cat1
url=url+"&second="+make1
url=url+"&sid="+Math.random();

xmlHttp.onreadystatechange=stateChanged ;
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
} 


function funcgetx1(str,make1,cat1,str1,obj,val1)
{

if (val1!=''){
obj.innerHTML='<font style="font-size:16px;">'+val1+'</font>';
}


xmlHttp=GetXmlHttpObject()
innerstr=str1;
if (xmlHttp==null)
{
alert ("Browser does not support HTTP Request")
return
}
var url="index.php"
url=url+"?ppx="+str
url=url+"&category="+cat1
url=url+"&make="+make1
url=url+"&sid="+Math.random();

xmlHttp.onreadystatechange=stateChangedx ;
xmlHttp.open("GET",url,true)
xmlHttp.send(null)
}
function stateChangedx()
{
if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
{
document.getElementById(innerstr).innerHTML=xmlHttp.responseText

}
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
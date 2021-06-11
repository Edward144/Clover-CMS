var http_host = location.protocol + "//";
var server_name = location.hostname;
var root_dir = "/";

$("script").each(function(index) {    
    var host = window.location.host;    
    var src = $("script")[index]['src'];
    
    if(src.indexOf(host) >= 0) {
        src = src.split(host)[1];
        
        if(src.indexOf("docRoot") >= 0) {            
            var srcSplit = src.split("/js")[0];
            root_dir = srcSplit + "/";
        }
    }
});
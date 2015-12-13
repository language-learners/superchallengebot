main = {
init : function()
{
    // the table
    sorttable.init();
    
    // make all anchors of class 'toggle' actually do a toggle
    var anchors = document.getElementsByTagName("a");
    for(var i = 0; i < anchors.length; i++) {
        var target = anchors[i].hash.substr(1);
        if(anchors[i].className.indexOf("toggle") != -1) {
            anchors[i].onclick = (function(anchor, targetid) {return function(){
                    toggleHideable(anchor, targetid);
                    return false;
                };}(anchors[i], target));
        }
        
        if(anchors[i].className.indexOf("jumptokeyword") != -1) {
            anchors[i].onclick = (function(targetid) {return function() {
                    expandKeywords();
                    setTimeout(function(){window.location.hash = "#" + targetid;}, 500);
                    return false;
                };}(target));
        }
    }
    
    // register scroll
    window.onscroll = scroll;
    
    // close the getting started box
    document.getElementById("closegetstarted").onclick = closeGetStarted;
    
}};

function toggleHideable(anchor, targetid)
{
    var element = document.getElementById(targetid);
    
    // toggle on opacity
    if(element.style.opacity == 0) {
        element.style.height = "auto";
        element.style.height = element.offsetHeight + "px";
        element.style.opacity = "1";
        anchor.innerHTML = anchor.innerHTML == "show more" ? "show less" : "hide";
    } else {
        element.style.height = "0";
        element.style.opacity = "0";
        anchor.innerHTML = anchor.innerHTML == "show less" ? "show more" : "show";
    }
}

function expandKeywords()
{
    var element = document.getElementById("keywords");
    
    element.style.height = "auto";
    element.style.height = element.offsetHeight + "px";
    element.style.opacity = "1";
    
    document.getElementById("expandkeywords").innerHTML = "hide";
}

function closeGetStarted()
{
    var element = document.getElementById("getstarted");
    element.style.display = 'none';
    
    // don't show again
    var index = -1;
    if(document.cookie != document.cookie) {
        index = document.cookie.indexOf("langchallenge.hidegetstarted");
    }

    if (index == -1) {
        var now = new Date();
        now.setMonth(now.getMonth()+6);
        var expiry = now.toUTCString();
        document.cookie = "langchallenge_hidegetstarted=true; expires=" + expiry;
    }

}

function scroll () {
    document.getElementById('background').style.top = -Math.round(window.pageYOffset * 0.8) + 'px';
}

function resetPreferences() {
    document.cookie = "langchallenge_sortcolumn=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    document.cookie = "langchallenge_hidegetstarted=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    window.location.reload();
    window.scroll(0, 0);
}
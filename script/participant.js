participant = {
    data : [],
    postUnits : [],
    labels : [],
    
    graph : function()
    {
        for(var key in this.data) {
        if(this.data.hasOwnProperty(key)) {
            // calculate totals
            this.calculateTotals(key);
            
            // where to draw
            var element = document.getElementById(key + "graph");

            // morris option format
            var options = {
                element : element,
                data : this.data[key],
                xkey: 'time',
                ykeys: ['amount'],
                labels: this.labels[key],
                dateFormat : function(t) {return titleFromTimestamp(t, key);},
                postUnits : this.postUnits[key],
                lineColors : ['#000'],
                hideHover : true
            };
            Morris.Line(options);
            
        }}
    },
    
    calculateTotals : function(key)
    {
        this.data[key].sort(sortByAmount);
        
        var total = 0;
        for(var datakey in this.data[key]) {
        if(this.data[key].hasOwnProperty(datakey)) {
            total += this.data[key][datakey]['amount'];
            this.data[key][datakey]['amount'] = total;
        }}
    },
    
    titleAt : function(timestamp, key)
    {
        // each datapoint
        for(var datakey in this.data[key]) {
        if(this.data[key].hasOwnProperty(datakey)) {

            var dataDate = new Date(this.data[key][datakey]['time']);
            var timeDate = new Date(timestamp);
            if(dataDate.getTime() == timeDate.getTime()) { return this.data[key][datakey]['title']; }
        }}
        return "Title not found at " + timestamp;
    }
};

function sortByAmount(a, b)
{
    var timeA = new Date(a['time']);
    var timeB = new Date(b['time']);
    return timeA.getTime() - timeB.getTime();
}

function titleFromTimestamp(timestamp, key)
{
    return participant.titleAt(timestamp, key);
}

function formatTimestamp(timestamp)
{
    var date = new Date(timestamp);
    return date.getDate() + "-" + date.getMonth() + 1 + "-" + date.getFullYear();
}

function setGraphVisibility(key, visible)
{
    var graphelement = document.getElementById(key + "graph");
    var listelement = document.getElementById(key + "list");
    
    // toggle on opacity
    if(visible) {
        graphelement.style.height = "500px";
        //graphelement.style.height = graphelement.offsetHeight + "px";
        graphelement.style.opacity = "1";
        
        listelement.style.height = "0";
        listelement.style.opacity = "0";
        //anchor.innerHTML = anchor.innerHTML == "show more" ? "show less" : "hide";
    } else {
        listelement.style.height = "auto";
        listelement.style.height = listelement.offsetHeight + "px";
        listelement.style.opacity = "1";
        
        graphelement.style.height = "0";
        graphelement.style.opacity = "0";
        //anchor.innerHTML = anchor.innerHTML == "show less" ? "show more" : "show";
    }
}

window.onload=function()
{
    participant.graph()
    window.onscroll = scroll;
};

function scroll () {
    document.getElementById('background').style.top = -Math.round(window.pageYOffset * 0.8) + 'px';
}
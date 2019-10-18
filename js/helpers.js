

if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(search, pos) {
        return this.substr(!pos || pos < 0 ? 0 : +pos, search.length) === search;
    };
}

function getCampaignID() {

    var windowHash = window.location.hash;
    var count = windowHash.match(/\//g);
    var $campaignID = "";

    if (count != null && count.length == 3 ) {

        var $hashValue = window.location.hash;
        var $strLastPos = $hashValue.lastIndexOf("/")+1;
        $campaignID = $hashValue.slice($strLastPos, $hashValue.length);
    }
    return $campaignID;
}


function removeHash () {
    history.pushState(
        "",
        document.title,
        window.location.pathname + window.location.search
    );
}


//jQuery('head link[href="//gadgets.impartmedia.com/css/all.cssz"]').remove();
//jQuery('head link[href="//gadgets-pvt.impartmedia.com/css/all.cssz"]').remove();
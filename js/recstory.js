function getPosY() {
    PosY = 0;
    if( typeof( window.pageYOffset ) == "number" ) {
        PosY = window.pageYOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        PosY = document.body.scrollTop;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        PosY = document.documentElement.scrollTop;
    }
    return PosY;
}

jQuery(function(){
   
    var a_closed = false;
    var a_hidden = true;
    jQuery("#animbox").stop().css({right:"-1000px"});
    jQuery(window).scroll(function() {
        var lastScreen = getPosY() + jQuery(window).height() < jQuery(document).height() * scrollposition ? false : true;
        if (lastScreen && !a_closed) {
            jQuery("#animbox").stop().animate({right:"0px"});
            a_hidden = false;
        }
        else if (a_closed && getPosY() == 0) {
            a_closed = false;
        }
        else if (!a_hidden) {
            a_hidden = true;
            jQuery("#animbox").stop().animate({right:"-1000px"});
        }
    });
    jQuery("#closex").click(function() {
        jQuery("#animbox").stop().animate({right:"-1000px"});
        a_closed = true;
        a_hidden = true;
    });
});

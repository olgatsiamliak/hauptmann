$(document).ready(function(){  
    $('.activeLang').click(function (){
        $('#langChoice').toggleClass('show');
        $('#languagesMenu').toggleClass('openLang');
    })
    $('body').click(function (event){
            var target = $(event.target);
            if ( !target.is('a[href]') ) {
                $('#langChoice').removeClass('show'); 
                $('#languagesMenu').removeClass('openLang');
            }            
        });
    $("#sliderHolder").owlCarousel({
        loop: true,
        margin: 30,
        dots: true,
        nav: false,
        navText: false,
        items: 1,
        singleItem:true,
        autoplay: true,
        smartSpeed:1000, 
        autoplayTimeout:2500, 
    });
    var i = 1;
        $('#sliderHolder .owl-dot').each(function(){
        $(this).text('0' + i);
        i++;
    });
    
    $("#owlProuctCarusel").owlCarousel({
        loop: true,
        margin: 30,
        dots: true,
        nav: false,
        navText: false,
        items: 4,
        autoplay: true,
        smartSpeed:1000, 
        autoplayTimeout:2500,
        autoplayHoverPause:true,
        responsive: false
    });
    var j = 1;
        $('#owlProuctCarusel .owl-dot').each(function(){
        $(this).text('0' + j);
        j++;
    });
    $('#owlProuctCarusel .owl-dots').css('top', '-' + $('#owlProuctCarusel').height() - 113);
    $('.sliderTitleHolder').css({'margin-top': '-' + ($('.sliderTitleHolder').height() / 2 + 50), 'height': $('.sliderTitleHolder').height(), 'top': '-' + $('.imageHolder').height() / 2});
});
$(window).scroll(function() {
    if ($(window).scrollTop() > 90 ){
    	$('nav').addClass('fixed');
    } else {	
    	$('nav').removeClass('fixed');	
     };
});

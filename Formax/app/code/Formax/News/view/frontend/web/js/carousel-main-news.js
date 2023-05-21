define([
    'jquery',
    'owl_carousel',
    'mage/translate'
  ], function(
    $
  ){

    var owlFixLastDotCal = (event) => {
      if ( $(window).width() <= 768 ) {
        var pagesize=event.page.size;
        var numitems=event.item.count;
        var mod=numitems % pagesize;
        var i=event.item.index;
        setTimeout(function(){
            if (mod === (i % pagesize) && (numitems-i)==pagesize) {
                $(event.target).find('.owl-dots div:last')
                .addClass('active')
                .siblings().removeClass('active')
            }
            if (numitems<=pagesize) {
                $(event.target).find('.owl-dots').hide();
            }else{
                $(event.target).find('.owl-dots').show();
            }
        }, 1);
      }
    };

    var owlHideDotsCal= (event) => {
      if ( $(window).width() <= 768 ) {
        var pagesize=event.page.size;
        var numitems=event.item.count;
        setTimeout(function(){
            if (numitems<=pagesize) {
                $(event.target).find('.owl-dots').hide();
            }else{
                $(event.target).find('.owl-dots').show();
            }
        }, 1);
      }
    };

    var mainNewsCarousel = {
        
        init: function() {
            mainNewsCarousel.generateCarousel();
        },

        generateCarousel: function() {
            let actionBtn = $('.carousel-main-news .action.main-news-action').detach();
            $('.carousel-main-news .main-news-box-out').append(actionBtn);

            $('.carousel-main-news .main-news-box').owlCarousel({
                nav: false,
                dots: true,
                items:1,
                thumbs: false,
                loop: false,
                margin: 0,
                touchDrag: true,
                mouseDrag: false,
                transition: 'slide',
                responsive:{
                    600: {
                        dots            :true,
                        items           :2,
                        center          :false,
                        stagePadding    :0
                    },
                    768: {
                        dots            :false,
                        items           :3,
                        center          :false,
                        stagePadding    :0
                    }
              }
            }).on('changed.owl.carousel', owlFixLastDotCal)
            .on('initialized.owl.carousel', owlHideDotsCal)
            .on('resized.owl.carousel', owlFixLastDotCal);
        }

    }

    return {
        'carousel-main-news': mainNewsCarousel.init
    };
  }
);
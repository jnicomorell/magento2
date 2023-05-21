define([
    'jquery',
    'owl_carousel',
    'mage/translate'
  ], function(
    $
  ){
    var Carousel = {
        
        init: function() {
            if ( $(window).width() <= 767 ) {
                Carousel.generateCarousel();
            }
        },

        generateCarousel: function() {
            let actionBtn = $('.coop-benefits-carousel .action.benefits-action').detach();
            $('.coop-benefits-carousel .carousel-box-out').append(actionBtn);

            $('.coop-benefits-carousel .carousel-box').owlCarousel({
                nav: false,
                dots: true,
                items: 1,
                thumbs: false,
                loop: false,
                margin: 0,
                touchDrag: true,
                mouseDrag: true,
                transition: 'slide'
            });
        }

    }

    return {
        'coo-benefits-carousel': Carousel.init
    };
  }
);
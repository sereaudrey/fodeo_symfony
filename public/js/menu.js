jQuery(document).ready(function($){
    /**
     * @var menuOpened
     * @description
     */
    let menuOpened = localStorage.getItem('menu-opened');

    //Ouvrir le menu
    if(typeof(menuOpened) !== 'undefined' && menuOpened == 'true') {
        $('#menu-wrapper').addClass('full-menu');
        $('#wrapper, #menu-secondary').addClass('menu-opened');
    }

    //Ouvrir et fermer le menu et enregistrer le choix dans localStorage
    $('#menu-option-toggle').click(function(){
        $('#menu-wrapper').toggleClass('full-menu');
        $('#wrapper, #menu-secondary').toggleClass('menu-opened');
        if($('#menu-wrapper').hasClass('full-menu')){
            localStorage.setItem('menu-opened', true);
        } else {
            localStorage.setItem('menu-opened', false);
        }
    });
});
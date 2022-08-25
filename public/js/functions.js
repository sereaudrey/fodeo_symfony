$(document).ready(function () {
    //Rediriger sur la fiche utilisateur correspondant au clic
    $('.clickable-table-list .link').click(function(e){
        window.location.href = $(this).data('href');
    });

    //Gestion addFlash 
    setTimeout(function() {$('.message-success').hide()}, 4000);
    setTimeout(function() {$('.message-danger').hide()}, 4000);
});
$(document).ready(function () {
    //Rediriger sur la fiche utilisateur correspondant au clic
    $('.clickable-table-list .link').click(function(e){
        window.location.href = $(this).data('href');
    });

    //Gestion addFlash 
    setTimeout(function() {$('.message-success').hide()}, 4000);
    setTimeout(function() {$('.message-danger').hide()}, 4000);

    //Ajouter contenu catalogue
    $('#BtnAddMovie').click(function(e) {
        e.preventDefault();
        //On compresse les données en une seule ligne
        let dataMovie = $('.add-movie').serialize();
   
        //Envoi des données
        try {
        $.ajax({
            type: "POST",
            url: $('#urlAddMovie').val(),
            dataType: "json",
            data: dataMovie,
            success:
            //si pas d'erreur dans le form on redirige vers la page du punchout créé
            function(data) {
                if(data['success'] == true) {
                window.location.href = 'affiche?id='+data['lastID'];
                } else {
                location.reload();
                }
            }
        });
        } catch {
        Response.setStatus(400);
        Response.getMessage();
        }
    });

    //Supprimer un film
    $('#BtnDelMovie').click(function(e) {
        //On récupère l'url 
        url = $(this).attr('urlSupp');
        if($('#MessageSupp').css('display') == 'none') {
          $('#content_affiche_film').css('opacity', '0.3');
          $('#MessageSupp').css('display', 'inline-block');
        }
    
      //Si on clique sur annuler la suppression
      $('#AnnulSuppression').click(function() {
        $('#MessageSupp').css('display', 'none');
        location.reload(true);
      });
    
      //Si on confirme la suppression 
      $('#ConfirmerSuppression').click(function() {
        //On insère la bonne url dans le href du bouton confirmer la suppression
        $('.lien-url-supp').attr('href', url);
      });
    });
});
$(document).ready(function () {
    /*Modifier les données utilisateur*/
    $(".edit-user").click(function (e) {
        e.stopPropagation(); 
        //On affiche les input et cache les label
        $(".edit-input-user").css('display', 'block');
        $(".edit-label-user").css('display', 'none');
        //On cache le btn edit et affiche enregistrer et annuler
        $(".data-cancel-user, .data-save-user").css('display', 'inline-block');
        $(".edit-user").css('display', 'none');
    })

    /* Annuler la modification */
    $(".data-cancel-user").click(function (e) {
        e.stopPropagation(); 
        //On cache les input et affiche les label
        $(".edit-input-user").css('display', 'none');
        $(".edit-label-user").css('display', 'block');
        //On cache affiche btn edit et cache enregistrer et annuler
        $(".data-cancel-user, .data-save-user").css('display', 'none');
        $(".edit-user").css('display', 'block');
    })

    /* Sauvegarder les données modifiées */
    $(".data-save-user").click(function (e) {
        e.preventDefault();

        //Compresse les données en une seule ligne
        let dataUser = $("#formEditUser").serialize();

        //Envoi des données
        try {
            $.ajax({
                type: "POST",
                url: $('#modifUser').val(),
                dataType: "json",
                data: dataUser,
            })
            .always(function() {
                //On cache les input et affiche les label
                $(".edit-input-user").css('display', 'none');
                $(".edit-label-user").css('display', 'block');
                //On cache affiche btn edit et cache enregistrer et annuler
                $(".data-cancel-user, .data-save-user").css('display', 'none');
                $(".edit-user").css('display', 'block');

                //On rafraichit la page
                window.location.reload(true);
            });
        } catch {
            Response.setStatus(400);
            Response.getMessage();
        }
    })

    /*Supprimer un utilisateur*/
    //Si on clique sur supprimer le punchout on affiche le message de confirmation
    $('#delete-user-button').click(function() {
        //On récupère l'url selon OCI ou CXML avec l'id
        url = $(this).attr('urlSupp');
        if($('.message-confirm-supp').css('display') == 'none') {
          $('#users-container').css('opacity', '0.3');
          $('.message-confirm-supp').css('display', 'inline-block');
        }
      })
    
      //Si on clique sur annuler la suppression
      $('#AnnulSuppression').click(function() {
        $('#MessageSupp').css('display', 'none');
        location.reload(true);
      })
    
      //Si on confirme la suppression 
      $('#ConfirmerSuppression').click(function() {
        //On insère la bonne url dans le href du bouton confirmer la suppression
        $('.lien-url-supp').attr('href', url);
      });
});
    
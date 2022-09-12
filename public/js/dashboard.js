$(document).ready(function () {
    //afficher les inputs au click du btn modifier profil
    $('.modifCompteUser').click(function(e) {
        $(".content-infos-edit").show();
        $(".content-infos").hide();
    })

    //Annuler les modifs
    $(".annulmodifCompteUser").click(function(e) {
        //On rafraîchit la page 
        window.location.reload(true);    
    })

    //afficher la modif du mdp modifier profil
    $('.modifCompteMdpUser').click(function(e) {
        $(".content-infos-edit").hide();
        $(".content-infos").hide();
        $(".content-infos-mdp").show();
    })

    //Enregistrer les modifs 
    $('.validmodifCompteUser').click(function(e) {
        e.preventDefault();
        //Compresse les données en une seule ligne
        let data = $("#modifDonneesProfil").serialize();
        let avatar = $("#avatar").prop('files')[0];
        if(avatar == undefined) {
            avatar = "iconUser.png"
        } else {
            avatar = avatar['name'];
        }

        //Envoi des données
        try{
            $.ajax({
                type: "POST",
                url : $('#urlModifDonneesProfil').val(),
                dataType: 'json',
                data: data+'&avatar='+avatar
            })
            .always(function() {
                $(".content-infos-edit").hide();
                $(".content-infos").show();

                //On rafraîchit la page 
                window.location.reload(true); 
            })
        } catch {
            Response.setStatus(400);
            Response.getMessage();
        }
    });
});
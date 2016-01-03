$(document).ready(function(){

    /**
     * Au clique sur l'icone de la colonne status on marque le todo completed or open
     */
    $("td[id^='status-']").click(function(){

        var imgSrc = $('img', this).attr('src');

        var status = 1; // change status open to completed
        if ( imgSrc.indexOf('completed') !== -1 ) {
            status = 0; // change status to completed
        }

        //get todo id
        var id = $(this).attr('id').substr(7);

        var url = baseUrl + '/todo/update/'+id;
        var data = { completed: status };


        $.ajax({
            url: url,
            data: data,
            type: 'PUT',
            success: function(result) {
                location.reload();
            }
        });

    });

});
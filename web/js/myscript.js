

$("div.alert").delay(3000).slideUp();

function confirmDelete(msg){
	if(window.confirm(msg)){
		return true;
	}
	return false;
};

   $(document).ready(function() {
        $('#dataTables-example').DataTable({
                responsive: true
        });
    });






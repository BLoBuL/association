function addFormField() {
var id = document.getElementById("idNextDestination").value;
var DestinationSelect = $("#id_dest\\[1\\]").clone().attr("id","id_dest["+id+"]").attr("name","id_dest["+id+"]");
var DestinationList = $("<ul><li class='editer_id_dest["+id+"]'></li>").append(DestinationSelect);
var newRow = $("<div class='formo' id='row" + id + "'></div>")
	.append(DestinationList);
newRow.append($("<li class='editer_montant_dest["+id+"]'><input name='montant_dest["+id+"]' value='0' type='text' id='montant_dest["+id+"]' /></li><button type='button' class='destButton' onClick='removeFormField(\"#row" + id + "\"); return false;'>-</button></ul>"));

newRow.appendTo($("#divTxtDestination"));

id = (id - 1) + 2;
document.getElementById("idNextDestination").value = id;
}

function removeFormField(id) {
$(id).remove();
}

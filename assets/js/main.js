var oldPasswordType = $('#oldPasswordType').val();
$('#ariform').submit(function( event ) {
	if($('input[name="name"]').val().length === 0) {
		alert(_("User Name Can Not Be Blank!"));
		event.preventDefault();
		$('#name').focus();
		return false;
	}
	if($('input[name="password"]').val().length === 0) {
		alert(_("User Password Can Not Be Blank!"));
		event.preventDefault();
		$('#password').focus();
		return false;
	}
	if (($('input[name="password"]').val() == '******') && ($('input[name="password_type"]:checked').val() != oldPasswordType)) {
		alert(_("You Cant Change Password Type Without Changing the Password"));
		event.preventDefault();
		$('#password').focus();
		return false;
	}
	if(jQuery.inArray( $('input[name="name"]').val(), users) >= 0 || $('input[name="name"]').val() == freepbxuser) {
		alert(_("User Name already exists!"));
		event.preventDefault();
		$('#name').focus();
		return false;
	}
});

$("#ariusergrid-side").on("click-row.bs.table", function(row, $element) {
	window.location = "?display=arimanager&view=form&user="+$element.id;
});

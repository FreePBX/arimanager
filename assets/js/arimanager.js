function roFormatter(value, row, idx)
{
	return sprintf('<i class="fa %s fa-lg" aria-hidden="true"></i>', value == "1" ? 'fa-check-square-o' : 'fa-square-o');
}

function linkFormatter(value, row, idx)
{
	var html = '';
	html += sprintf('<a href="#" data-toggle="modal" data-target="#userForm" data-id="%s"><i class="fa fa-pencil"></i></a>', value);
	html += '&nbsp;';
	html += sprintf('<a href="#" data-id="%s" data-name="%s" id="del" data-idx="%s"><i class="fa fa-trash"></i></a>', value, row['name'], idx);
	return html;
}

function showButtonReloadFreePBX()
{
	$("#button_reload").show();
}

function getTableGrid()
{
    return $('#ariusergrid');
}

$(document).on('click', '[id="del"]', function (e)
{
    e.preventDefault();

	var id   = $(this).data('id');
    var name = $(this).data('name');

	if (id === "" || id === undefined || id === null)
	{
		fpbxToast(_("ID not detected!"), '', 'error');
		return;
	}

	fpbxConfirm(
		sprintf(_("Are you sure you want to delete (%s)?"), name),
		_("Yes"), _("No"),
		function () {
			var post_data = {
				module: 'arimanager',
				command: 'delete',
				id: id,
			};
			$.post(window.FreePBX.ajaxurl, post_data)
			.done(function (data)
			{
				if (data.status == true)
				{
                    getTableGrid().bootstrapTable('refresh', { silent: true });
				}
				fpbxToast(data.message, '', data.status == true ? 'success' : 'error');
                if (data.needreload)
                {
                    showButtonReloadFreePBX();
                }
			})
			.fail(function(jqXHR, textStatus, errorThrown)
			{
				fpbxToast(textStatus + ' - ' + errorThrown, '', 'error');
			});
		}
	);
});

$('#userForm').on('hidden.bs.modal', function ()
{
    $("#idUser").val("");
    $("#passwordUser").val("");
});

$('#userForm').on('shown.bs.modal', function ()
{
    // Force focus to generate password strength level bar.
    let elementNow = $(document.activeElement);
    $("#passwordUser").focus();
    $(elementNow).focus();
});

$('#userForm').on('show.bs.modal', function (e)
{
	var id = $(e.relatedTarget).data('id');
	var showModal = true;
	
	$('.element-container').removeClass('has-error');
	$(".input-warn").remove();

	var name        = "";
    var dataGet     = null;

    var title 	 = "";
	var btn_send = "";

	var name_readonly = false;

	if (id == null || id == undefined || id == "")
	{
        id       = "-1";
		title 	 = _("Add Asterisk REST Interface User");
		btn_send = _("Create New");

		name_readonly = false;
	}
	else
	{
		title 	 = sprintf(_("Edit Asterisk REST Interface User (%s)"), id);
		btn_send = _("Save Changes");

		name_readonly = true;
	}

    $.ajax({
        type: "POST",
        url: window.FreePBX.ajaxurl,
        data: {
            module	: 'arimanager',
            command	: 'get',
            id		: id,
        },
        async: false,
        success: function(response)
        {
            if (response.status)
            {
                name        = response.data.name;
                dataGet     = response.data;
            }
            else
            {
                fpbxToast(response.message, '', 'error');
                showModal = false;
            }
        },
        error: function(xhr, status, error)
        {
            fpbxToast(sprintf(_('Error: %s'), error), '', 'error');
            showModal = false;
        }
    });

	if (showModal)
	{
		$this = this;

        // Config Buttons
		$("#submitForm").text(btn_send);
		$("#submitForm").prop("disabled", false);

        // Config Title
		$(this).find('.modal-title').text(title);
	
        // Set Values
		$("#idUser").val(id);

        $.each(dataGet, function(key, val)
        {
        	let idInput = sprintf("#%sUser", key);
            switch(key)
            {
                case 'password_format':
					$(idInput + "Crypt").prop('checked', val == "crypt" ? true : false );
					$(idInput + "Plain").prop('checked', val == "plain" ? true : false );
					break;

				case 'read_only':
					$(idInput + "No").prop('checked', val == "0" ? true : false );
					$(idInput + "Yes").prop('checked', val == "1" ? true : false );
                    break;

                default:
                    $(idInput).val(val);
                    break;
            }
        });
	}

	if (!showModal)
	{
        // Abort window opening
		e.preventDefault();
	}
});

$('#submitForm').on('click', function () {
	$this = this;

    var theForm = document.editManager;
    theForm.nameUser.focus();

 	var id = theForm.idUser.value;
    if (id === '' || id === null || id === undefined || id == "-1")
	{
		var typeUpdate = "new";
	}
	else
	{
		var typeUpdate = "edit";
	}

	if ((theForm.nameUser.value.search(/\s/) >= 0) || (theForm.nameUser.value.length == 0))
    {
        return warnInvalid(theForm.nameUser, _('User Name Can Not Be Blank!'));
    }

	if (theForm.passwordUser.value.length == 0)
    {
        return warnInvalid(theForm.passwordUser, _('User Password Can Not Be Blank!'));
    }

 	$(this).prop("disabled", true);
	$($this).text( typeUpdate == "edit" ? _("Updating..."): _("Adding..."));

    var formDataArray = $(theForm).serializeArray();
    var formDataObject = {};
    formDataArray.forEach(function(input)
    {
        formDataObject[input.name] = input.value;
    });
    
	var post_data = {
		module: 'arimanager',
		command: 'update',
        type: typeUpdate,
        id: id,
        formdata: formDataObject,
	};

	$.post(window.FreePBX.ajaxurl, post_data)
  	.done(function(data)
	{
 		if (data.status == true)
		{
            getTableGrid().bootstrapTable('refresh', { silent: true });
 			$("#userForm").modal('hide');
 		}
        fpbxToast(data.message, '', data.status == true ? 'success' : 'error');
        if (data.needreload)
        {
            showButtonReloadFreePBX();
        }
  	})
  	.fail(function(jqXHR, textStatus, errorThrown)
	{
		fpbxToast(textStatus + ' - ' + errorThrown, '', 'error');
  	})
	.always(function()
	{
		$($this).text( typeUpdate == "edit" ? _("Save Changes"): _("Create New"));
		$($this).prop("disabled", false);
	});
});

function CHFormatter(v,row){ return row['channel_ids'].length;}
function BRFormatter(v,row){ return row['bridge_ids'].length;}
function EPFormatter(v,row){ return row['endpoint_ids'].length;}
function DEVFormatter(v,row){ return row['device_names'].length;}
function moreDetails(v,row){
	var html = _('Channels:')+"<br/>"+row.channel_ids.join("<br/>");
	html += '<hr/>';
	html += _('Bridges:')+"<br/>"+row.bridge_ids.join("<br/>");
	html += '<hr/>';
	html += _('Endpoints:')+"<br/>"+row.endpoint_ids.join("<br/>");
	html += '<hr/>';
	html += _('Devices:')+"<br/>"+row.device_names.join("<br/>");
	return html;
}
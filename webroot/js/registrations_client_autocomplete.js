jQuery.fn.AutocompleteClient = function(options)
{
	var default_options = {
		clientCheckedIconUrl: "/lil_crm/img/ico_contact_check.gif",
		clientAutoCompleteUrl: "",

		messageStartTyping: "Start typing to search for a client",
        messageNoClientsFound: "No clients found.",

        addPersonCaption: "Add Person",
        addPersonDialogTitle: "Add Person",

        editPersonDialogTitle: "Edit Person",

        addContactDialogUrl: "",
        editContactDialogUrl: ""
	};
    var $this = this;
    var modalClientDialog = null;

	this.fillClientData = function(client) {
        //$("#client-kind", $this).val('T');

        $("#client", $this).val(client.surname + ' ' + client.name);

        $("#client-id", $this).val(client.id);
        $("#client-no", $this).val(client.no);
	    $("#client-name", $this).val(client.name);
	    $("#client-surname", $this).val(client.surname);

	    $("#client-street", $this).val(client.street);
	    $("#client-city", $this).val(client.city);
	    $("#client-zip", $this).val(client.zip);
        $("#client-country_code", $this).val(client.country_code);
        //$("#client-address-country_code-select", $this).val(client.country_code);
        //$('#client-address-country_code option[value="'+client.country_code+'"]', $this).prop('disabled', false);

	    $("#client-dob", $this).val(client.dob);
	    $("#client-plob", $this).val(client.plob);
        //$("#client-nationality", $this).val(client.nationality);

        $this.toggleValidClientButtons(true);
    }

	this.clearClientFields = function() {
        $("input[id^='client-'").val("");
        //$("#client-address-country_code-select", $this).val("");

        //$("#image-checked", $this).hide();
        $this.toggleValidClientButtons(false);
    }

    this.toggleValidClientButtons = function(selected)
    {
        if (selected) {
            $("#client-btn-search", $this).hide();
            $("#client-btn-edit", $this).show();
        } else {
            $("#client-btn-search", $this).show();
            $("#client-btn-edit", $this).hide();
        }
    }


	this.setAutocompleteClientField = function(target) {
	    $("#client").autocompleteheader({
	    	target: target,
			autoFocus: false,
			minLength: 0,
            source: options.clientAutoCompleteUrl,
            addPersonCaption: options.addPersonCaption,
			search: function() {
				var title = $("#client", $this).val();
				$this.clearClientFields();
				$("#client", $this).val(title);

			},
			response: function(event, ui) {
				if (ui.content.length === 0) {
					var noResultsMessage = $(this).val().length == 0 ? options.messageStartTyping : options.messageNoClientsFound;
					var noResult = {value: '', label: noResultsMessage, systemMessage: true};
           			ui.content.push(noResult);
				}
			},
			select: function(event, ui) {
				if (ui.item && ui.item.value != "") {
				    $this.fillClientData(ui.item);
				    event.preventDefault();
                    //$("#image-checked", $this).show();
                    $this.toggleValidClientButtons(true);
				}
			},
		})
		.keyup(function() {
			if ($(this).val() === "") {
				$this.clearClientFields();
			}
        })
        .focus(function() {
            if (!$("#client-id", $this).val()) {
                $(this).autocompleteheader("search");
            }
        })
		.autocompleteheader( "instance" )._renderItem = function( ul, item ) {
		    var appendString = item.label;
		    if (typeof item.street != "undefined") {
                appendString = "<span class='ac_client_label'>" + item.label + "</span>" +
                    "<span class='ac_client_address'>" +
                        item.street + ", " +
                        item.zip + " " + item.city + ", " + item.country +
                    "</span>";
		    }
			return $( "<li>" )
				.append(appendString)
				.appendTo(ul);
		};

	}

	this.addCheckIconAfterClientField = function(target) {
		var clientCheck = $('<img />', {
	        id: 'image-checked',
	        src: options.clientCheckedIconUrl,
	        style: 'display: none'
	    });
		//$('#client-name', $this).after(clientCheck);
		if ($('#client-id', $this).val()) {
            $('#image-checked', $this).show();
        }
    }

    this.addClientDialog = function(contactKind, target) {
        $("#client").autocompleteheader("close");
		modalClientDialog = popup({
			title: options.addPersonDialogTitle,
			url: options.addContactDialogUrl.replace("__kind__", contactKind),
			w: 780,
			h: 'auto',
			onClose: function(e) {
				return false;
			},
			onData: function(client) {
				$this.fillClientData(client);
                $("#dialog-form").dialog("close");
                modalClientDialog = null;
				return false;
			}
        });
        return false;
    }

    this.editClientDialog = function(e) {
        modalClientDialog = popup({
			title: options.editPersonDialogTitle,
			url: options.editContactDialogUrl.replace("__id__", $("#client-id", $this).val()),
			w: 780,
			h: 'auto',
			onClose: function(e) {
				return false;
			},
			onData: function(client) {
				$this.fillClientData(client);
                $("#dialog-form").dialog("close");
                modalClientDialog = null;
				return false;
			}
        });
        e.preventDefault();
        return false;
    }

    $.widget( "custom.autocompleteheader", $.ui.autocomplete, {
    	options: {
            target: "issuer",
            addPersonCaption: "Add Person"
    	},
    	_create: function() {
			this._super();
			this.widget().menu("option", "items", "> :not(.autocomplete-custom-header)");
		},
		_renderItem: function( ul, item ) {
			var li = $("<li>")
				.attr("data-value", item.value)
				.append(item.label);

			if (typeof item.systemMessage != "undefined") {
				$(li).attr("class", "ui-state-disabled");
			}

			return $(li).appendTo(ul);
		},
		_renderMenu: function (ul, items) {
	        var self = this;
  		    $.each(items, function (index, item) {
	            self._renderItemData(ul, item);
	            if (index == 0) {
					var addPersonLink = $("<a>")
	            		.attr("class", "autocomplete-custom-header-button autocomplete-custom-header-button-100")
	            		.click(function() { $this.addClientDialog("T", self.options.target) })
						.append(self.options.addPersonCaption);

	            	var li = $("<li>")
						.attr("class", "autocomplete-custom-header")
						.append(addPersonLink);

					ul.prepend(li);
				}
	        });
	    }
    });

	// initialization
    options = jQuery().extend(true, {}, default_options, options);
    this.addCheckIconAfterClientField();

    $this.toggleValidClientButtons($("#client-id", $this).val());
    $this.setAutocompleteClientField();

    $("#client-btn-edit", $this).on("click", $this.editClientDialog);
    $("#client-btn-search", $this).prop("disabled", true);

    //$("input[readonly][id^='client-'").addClass("locked");
    //$("select[readonly][id^='client-'").addClass("locked");
    //$("select[readonly][id^='client-'] option").prop("disabled", true);
}

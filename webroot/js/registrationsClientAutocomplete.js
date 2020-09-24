jQuery.fn.AutocompleteClient = function (options)
{
    var default_options = {
        clientCheckedIconUrl: "/lil_crm/img/ico_contact_check.gif",
        clientAutoCompleteUrl: "",

        cbSameClientId: "#invoice-client-buyer-toggle",
        mode: "received",

        addContactDialogUrl: "",
        addCompanyDialogTitle: "Add a Company",
        addPersonDialogTitle: "Add a Person",

        messageStartTyping: "Start typing to search for a client",
        messageNoClientsFound: "No clients found."
    };
    var $this = this;

    var modalTemplate = [
        '<div class="modal">',
        '   <div class="modal-content">',
        '   <h4>Modal Header</h4>',
        '   <p></p>',
        '   </div>',
        '</div>'
    ];

    this.fillClientData = function (client) {
        $("#client", $this).val(client.surname + ' ' + client.name);

        $("#client-id", $this).val(client.id);
        $("#client-no", $this).val(client.no);
	    $("#client-name", $this).val(client.name);
        $("#client-surname", $this).val(client.surname);

        $("#client-dob", $this).val(client.dob);
        $("#client-plob", $this).val(client.plob);
        //$("#client-nationality", $this).val(client.nationality);

        if (client.primary_address) {
            $("#client-street", $this).val(client.primary_address.street);
            $("#client-city", $this).val(client.primary_address.city);
            $("#client-zip", $this).val(client.primary_address.zip);
            $("#client-country_code", $this).val(client.primary_address.country_code);
        }

        $this.toggleValidClientButtons(true);
        M.updateTextFields()
    }

    this.clearClientFields = function() {
        $("input[id^='client-'").val("");
        $this.toggleValidClientButtons(false);
    }

    this.selectClient = function (item) {
        console.log(item);
        $this.fillClientData(item);
        $this.toggleValidClientButtons(true);
    }

    this.setAutocompleteTitleField = function () {
        var elem = document.querySelector("#client");

        if (elem) {
            var instance = M.AutocompleteAjax.init(elem, {
                source: options.clientAutoCompleteUrl,
                minLength: 0,
                onSearch: function () {
                    var title = $("#client", $this).val();
				    $this.clearClientFields();
				    $("#client", $this).val(title);
                },
                onSelect: function (item) {
                    if (item && item.value != "") {
                        $this.fillClientData(item.client);
                        $this.toggleValidClientButtons(true);
                    }
                },
                onOpenEnd: function (el) {
                    var li = $(
                        "<li style=\"line-height: inherit; min-height: 0;\">" +
                        "<button href=\"#\" class=\"\" style=\"width: 50%; min-height: 30px; float: left; \" id=\"AutocompleteAddPerson\">" +
                            options.addPersonDialogTitle +
                        "</button>" +
                        "<button href=\"#\" class=\"\" style=\"width: 50%; min-height: 30px; float: left;\" id=\"AutocompleteAddCompany\">" +
                            options.addCompanyDialogTitle +
                        "</button>" +
                        "</li>"
                    );

                    $(el).prepend(li);

                    $(el).css({"padding-top": "30px", "min-width": "300px"});
                    $(el).on('scroll', function () {
                        $(li).css({'top': $(el).scrollTop()}); });
                    $(li).css({'position': 'absolute', 'top': 0, 'background-color:': '#ff0000'});

                    $("#AutocompleteAddPerson", el).modalPopup({
                        url: options.addContactDialogUrl.replace("__kind__", "T"),
                        title: options.addPersonDialogTitle,
                        processSubmit: true,
                        onBeforeRequest: function () {
                            instance.close();
                        },
                        onJson: function (item) {
                            $this.selectClient(item);
                        }
                    });

                    $("#AutocompleteAddCompany", el).modalPopup({
                        url: options.addContactDialogUrl.replace("__kind__", "C"),
                        title: options.addCompanyDialogTitle,
                        processSubmit: true,
                        onBeforeRequest: function () {
                            instance.close();
                        },
                        onJson: function (item) {
                            $this.selectClient(item);
                        }
                    });
                }
            });

            $(elem)
                .on("keyup", function () {
                    if ($(this).val() === "") {
                        $this.clearClientFields();
                    }
                })
                .on("focus", function () {
                    if (!$("#client-id", $this).val()) {
                        instance.open();
                    }
                });
        }
    }

    this.toggleValidClientButtons = function(selected)
    {
        if (selected) {
            $("#btn-client-edit", $this).removeClass("disabled");
            $("#btn-client-selected", $this).removeClass("disabled");
        } else {
            $("#btn-client-edit", $this).addClass("disabled");
            $("#btn-client-selected", $this).addClass("disabled");
        }
    }

    // initialization
    options = jQuery().extend(true, {}, default_options, options);

    $this.popup = $(modalTemplate.join("\n")).appendTo(document.body);
    $this.popup.modal();
    $this.popupInstance = M.Modal.getInstance($this.popup);

    $this.toggleValidClientButtons($("#client-id", $this).val());
    $this.setAutocompleteTitleField();

    $("#btn-client-edit", $this).modalPopup({
        title: options.editPersonDialogTitle,
        url: options.editContactDialogUrl.replace("__id__", $("#client-id", $this).val()),
        processSubmit: true,
        onBeforeRequest: function () {
            return options.editContactDialogUrl.replace("__id__", $("#client-id", $this).val());
        },
        onJson: function (item) {
            $this.selectClient(item);
        }
    });
    $("#btn-client-selected", $this).prop("disabled", true);

}

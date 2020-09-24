jQuery.fn.ReservationsOverview = function(p_options)
{
	var default_options = {
        processSubmit: false,
        onJson: null,
        onBeforeRequest: null,
        onOpen: null,
        onClose: null,
        onResize: null
	};
	var $this       = this;
	var options     = [];

	var mouseStarted = false;
	var mouseStartX = 0;
	var rowTds = null;
	var dayStart = "";
	var dayEnd = "";
    var roomId = null;

    var popup       = null;
    var instance    = null;
    var oldHeight   = null;

    var template = [
        '<div class="modal">',
        '   <div class="modal-content">',
        '   <a href="#" class="btn btn-small modal-close" style="float: right">x</a>',
        '   <h4>Modal Header</h4>',
        '   <p></p>',
        '   </div>',
        '</div>'
    ];


	this.startSelection = function(e)
	{
		if (e.which == 1) { // left
			mouseStarted = true;
			mouseStartX = e.pageX + $("#main").scrollLeft();
			rowTds = $(this).parent("tr").children("td:not(:first)");

			// extract room_id form first column
			var roomClass = $(this).parent("tr").children("td:first").prop("class").match(/room_[\w-]*\b/);
			roomId = roomClass[0].substr(5);

			$this.doSelection(e);

			$(document).on("mouseup", $this.endSelection);
			$(document).on("mousemove", $this.doSelection);
			$(document).on("keyup", $this.onKeyUp);
		}
	}
	this.endSelection = function(e)
	{
		$this.releaseHandlers();

		if (dayStart !== "" && dayEnd !== "") {
            var targetUrl = $this.options.url
            .replace("__room__", roomId)
            .replace("__start__", dayStart > dayEnd ? dayEnd : dayStart)
            .replace("__end__", dayEnd > dayStart ? dayEnd: dayStart);

            //window.location.href = targetUrl;
            //return false;

            $this.onClick(targetUrl);

    		//popup({
    		//	title: options.title,
    		//	url: targetUrl,
    		//	onClose: $this.clearSelection,
    		//	h: 'auto',
    		//	w: '510px'
            //});

    		return false;
		}
    }

    this.onClick = function(targetUrl) {
        let url = targetUrl;

        var jqxhr = $.ajax(url)
            .done(function(html) {
                $("p", $this.popup).html(html);
                $("h4", $this.popup).html($this.options.title);

                //if ($this.options.processSubmit) {
                //    $("form", $this.popup).submit($this.popupFormSubmit);
                //}

                // update text fields for label placement
                M.updateTextFields();

                $this.instance.open();
            })
            .fail(function() {
                alert("Request Failed");
            });


        return false;
    };

	this.doSelection = function(e)
	{
		var x = $(rowTds).first().offset().left;

		var posX = null;
		var scrollX = $("#main").scrollLeft();
		var w = null;

		var stopSelection = false;

		dayStart = "";

		var tds = $(rowTds).get();
		// when dragging right to left, reverse order so the selection can be stopped
        if (mouseStartX > (e.pageX+scrollX)) tds = tds.reverse();

		$(tds).each(function() {
			posX = $(this).offset().left + scrollX;
			w = $(this).outerWidth(true);

			// first part - selection by drag right || second part - selection by drag left
			if ((posX + w > mouseStartX && posX < (e.pageX+scrollX)) || (posX < mouseStartX && (posX + w) > (e.pageX+scrollX))) {
			    if (!stopSelection) {
                    if ($(this).hasClass("reserved") || $(this).hasClass("registered") || $(this).hasClass("main")) {
                        if (dayStart !== "") stopSelection = true;
                    } else {
        				$(this).addClass("highlight");

        				// extract dates to dayStart and dayEnd
        				var dayClass = $(this).prop("class").match(/day_[\w-]*\b/);

        				if (dayStart === "") dayStart = dayClass[0].substr(4);
        				dayEnd = dayClass[0].substr(4);
    				}
				}
			} else {
				$(this).removeClass("highlight");
			}
		});
	}
	this.clearSelection = function()
	{
		rowTds.each(function() {
			$(this).removeClass("highlight");
		});
		dayStart = "";
		dayEnd = "";
	}
	this.onKeyUp = function(e)
	{
        if (e.keyCode == 27) {
            $this.releaseHandlers();
            $this.clearSelection();
            e.preventDefault();
        }
	}
	this.releaseHandlers = function()
	{
	    mouseStarted = false;
		$(document).off("mouseup", $this.endSelection);
		$(document).off("mousemove", $this.doSelection);
		$(document).off("keyup", $this.onKeyUp);
    }

    this.checkResize = function(e)
    {
        let popupHeight = $($this.popup).height();
        if ($this.oldHeight != popupHeight) {
        }
        $this.oldHeight = popupHeight;
    }



	// initialization
	$this.options = jQuery().extend(true, {}, default_options, p_options);

    $(this).prop('unselectable', 'on')
		.css('user-select', 'none');

	$("td.day", $this)
		.mousedown($this.startSelection)
		.prop('unselectable', 'on')
		.css('user-select', 'none')
        .on('selectstart', false);

    $this.popup = $(template.join("\n")).appendTo(document.body);
    $(".modal-close", $this.popup).on("click", function(e) {
        $this.instance.close();
    });

    $(window).on("resize", function(e) {
        $this.checkResize(e);
    });

    $this.instance = M.Modal.init($this.popup.get(0), {
        dismissible: true,
        onCloseEnd: function() {
            $this.clearSelection()
        }
    });
}

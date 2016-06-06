/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */

jQuery.extend({
	nette: {
		updateSnippet: function (id, html) {
			$("#" + id).html(html);
		},

		success: function (payload) {
			// redirect
			if (payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}

			// snippets
			if (payload.snippets) {
				for (var i in payload.snippets) {
					jQuery.nette.updateSnippet(i, payload.snippets[i]);
				}
			}

			$('.main-ajax-spinner').removeClass('visible');
		}
	}
});

jQuery.ajaxSetup({
	success: jQuery.nette.success,
	dataType: "json",
    beforeSend: function() {
    	$('.main-ajax-spinner').addClass('visible');
    },
});

$('body').on('submit', 'form.ajax', function( event ) {
	$.ajax({
	    type: "POST",
	    url: $(this).attr("action"),
	    data: $(this).serialize(),
	    success: function(data) {                   
			$.nette.success(data);
	    }
	});
	event.preventDefault();
});
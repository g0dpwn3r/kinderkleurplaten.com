
/*
Copyright 2014 - 2026  Marcel Pol  (email: marcel@timelord.nl)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/*
 * JavaScript for Gwolle Guestbook Frontend.
 */



/*
 * Run this function after changes in the guestbook.
 * It will hook events at each entry, for situations where there have been entries added.
 */
function gwolle_gb_frontend_callback_function() {

	if ( typeof gwolle_gb_readmore === 'function' ) {
		gwolle_gb_readmore();
	}
	if ( typeof gwolle_gb_readless === 'function' ) {
		gwolle_gb_readless();
	}
	if ( typeof gwolle_gb_metabox_handle === 'function' ) {
		gwolle_gb_metabox_handle();
	}
	if ( typeof gwolle_gb_reset_used_characters === 'function' ) {
		gwolle_gb_reset_used_characters();
	}

	// For add-on.
	if ( typeof gwolle_gb_addon_admin_reply === 'function' ) {
		gwolle_gb_addon_admin_reply();
	}
	if ( typeof gwolle_gb_addon_delete === 'function' ) {
		gwolle_gb_addon_delete();
	}
	if ( typeof gwolle_gb_addon_entry_edit === 'function' ) {
		gwolle_gb_addon_entry_edit();
	}
	if ( typeof gwolle_gb_addon_like === 'function' ) {
		gwolle_gb_addon_like();
	}
	if ( typeof gwolle_gb_addon_rateit === 'function' ) {
		gwolle_gb_addon_rateit();
	}
	if ( typeof gwolle_gb_addon_report === 'function' ) {
		gwolle_gb_addon_report();
	}

}
document.addEventListener('DOMContentLoaded', function () {
	gwolle_gb_frontend_callback_function();
});


/*
 * Click the button and the form becomes visible.
 */
document.addEventListener('DOMContentLoaded', function () {

	document.querySelectorAll( 'div.gwolle-gb-write-button input' ).forEach( button => {
		button.addEventListener( 'click', function (e) {
			const main_div = button.closest( 'div.gwolle-gb' );
			const write_button = main_div.querySelector( 'div.gwolle-gb-write-button' );
			const form = main_div.querySelector( 'form.gwolle-gb-write' );

			write_button.style.height = '0px';
			write_button.classList.add( 'gwolle-gb-hide' );
			write_button.style.transition = 'none';
			write_button.setAttribute( 'aria-expanded', 'true' );

			form.style.height = '0px';
			form.classList.remove( 'gwolle-gb-hide' );
			form.style.transition = 'height 0.9s linear';
			form.style.height = form.scrollHeight + 'px';
			setTimeout(() => {
				form.style.height = 'auto';
				form.style.transition = 'none';
			}, 900);

			e.preventDefault();
		});
	});

	document.querySelectorAll( 'button.gb-notice-dismiss' ).forEach( button => {
		button.addEventListener( 'click', function (e) {
			const main_div = button.closest( 'div.gwolle-gb' );
			const write_button = main_div.querySelector( 'div.gwolle-gb-write-button' );
			const form = main_div.querySelector( 'form.gwolle-gb-write' );

			form.style.height = '0px';
			form.classList.add( 'gwolle-gb-hide' );
			form.style.transition = 'none';
			write_button.setAttribute( 'aria-expanded', 'false' );

			write_button.style.height = '0px';
			write_button.classList.remove( 'gwolle-gb-hide' );
			write_button.style.transition = 'height 0.3s linear';
			write_button.style.height = write_button.scrollHeight + 'px';
			setTimeout(() => {
				write_button.style.height = 'auto';
				write_button.style.transition = 'none';
			}, 300);

			e.preventDefault();
		});
	});

});


/*
 * Click the readmore and the full content of that entry becomes visible.
 */
function gwolle_gb_readmore() {

	const links = document.querySelectorAll( '.gb-entry-content .gwolle-gb-readmore' );
	links.forEach( link => {
		link.removeEventListener( 'click', gwolle_gb_readmore_open );
		link.addEventListener( 'click', gwolle_gb_readmore_open );
	});

	const links_a = document.querySelectorAll( '.gb-entry-admin_reply .gwolle-gb-readmore-admin_reply' );
	links_a.forEach( link => {
		link.removeEventListener( 'click', gwolle_gb_readmore_open_admin_reply );
		link.addEventListener( 'click', gwolle_gb_readmore_open_admin_reply );
	});

}
function gwolle_gb_readmore_open( event ) {

	const content = event.currentTarget.closest( 'div.gb-entry-content' );
	const excerpt = content.querySelector( 'div.gb-entry-excerpt' );
	const full_content = content.querySelector( 'div.gb-entry-full-content' );

	excerpt.classList.add( 'gwolle-gb-hide' );

	full_content.style.height = '0px';
	full_content.classList.remove( 'gwolle-gb-hide' );
	full_content.style.transition = 'height 0.3s linear';
	full_content.style.height = full_content.scrollHeight + 'px';
	setTimeout(() => {
		full_content.style.height = 'auto';
		full_content.style.transition = 'none';
	}, 300);

	event.preventDefault();

}
function gwolle_gb_readmore_open_admin_reply( event ) {

	const content = event.currentTarget.closest( 'div.gb-entry-admin_reply' );
	const excerpt = content.querySelector( 'div.gb-admin_reply-excerpt' );
	const full_content = content.querySelector( 'div.gb-admin_reply-full-content' );

	excerpt.classList.add( 'gwolle-gb-hide' );

	full_content.style.height = '0px';
	full_content.classList.remove( 'gwolle-gb-hide' );
	full_content.style.transition = 'height 0.3s linear';
	full_content.style.height = full_content.scrollHeight + 'px';
	setTimeout(() => {
		full_content.style.height = 'auto';
		full_content.style.transition = 'none';
	}, 300);

	event.preventDefault();

}
/* And collapse that again. */
function gwolle_gb_readless() {

	const links = document.querySelectorAll( '.gb-entry-content .gwolle-gb-readless' );
	links.forEach( link => {
		link.removeEventListener( 'click', gwolle_gb_readless_open );
		link.addEventListener( 'click', gwolle_gb_readless_open );
	});

	const links_a = document.querySelectorAll( '.gb-entry-admin_reply .gwolle-gb-readless-admin_reply' );
	links_a.forEach( link => {
		link.removeEventListener( 'click', gwolle_gb_readless_open_admin_reply );
		link.addEventListener( 'click', gwolle_gb_readless_open_admin_reply );
	});

}
function gwolle_gb_readless_open( event ) {

	const content = event.currentTarget.closest( 'div.gb-entry-content' );
	const excerpt = content.querySelector( 'div.gb-entry-excerpt' );
	const full_content = content.querySelector( 'div.gb-entry-full-content' );

	full_content.classList.add( 'gwolle-gb-hide' );

	excerpt.classList.remove( 'gwolle-gb-hide' );

	event.preventDefault();

}
function gwolle_gb_readless_open_admin_reply( event ) {

	const content = event.currentTarget.closest( 'div.gb-entry-admin_reply' );
	const excerpt = content.querySelector( 'div.gb-admin_reply-excerpt' );
	const full_content = content.querySelector( 'div.gb-admin_reply-full-content' );

	full_content.classList.add( 'gwolle-gb-hide' );

	excerpt.classList.remove( 'gwolle-gb-hide' );

	event.preventDefault();

}

/*
 * Metabox, toggle on and off.
 */
function gwolle_gb_metabox_handle() {

	const handles = document.querySelectorAll('button.gb-metabox-handle');

	handles.forEach( handle => {
		handle.removeEventListener( 'click', gwolle_gb_metabox_toggle );
		handle.addEventListener( 'click', gwolle_gb_metabox_toggle );
	});

	document.body.addEventListener( 'keyup', function(e) {
		if ( e.key === 'Escape' ) {
			// reset all
			document.querySelectorAll('div.gb-metabox').forEach( metabox => {
				metabox.style.opacity = 0;
				metabox.style.visibility = 'hidden';
			});
		}
	});

}
function gwolle_gb_metabox_toggle( event ) {

	const article = event.currentTarget.closest( 'article' );
	const metabox = article.querySelector( 'div.gb-metabox' );
	const metabox_handle = article.querySelector( 'button.gb-metabox-handle' );
	if (metabox.style.visibility === 'hidden' || getComputedStyle(metabox).visibility === 'hidden') {
		// reset all
		document.querySelectorAll('div.gb-metabox').forEach( metabox => {
			metabox.style.opacity = 0;
			metabox.style.visibility = 'hidden';
		});
		document.querySelectorAll('button.gb-metabox-handle').forEach( metabox_handle => {
			metabox_handle.setAttribute( 'aria-expanded', 'false' );
			//metabox_handle.style.outlineStyle = 'none';
		});
		metabox.style.transition = 'opacity 0.4s linear';
		metabox.style.opacity = 1;
		metabox.style.visibility = 'visible';
		metabox_handle.setAttribute( 'aria-expanded', 'true' );
		//metabox_handle.style.outlineStyle = 'solid';
	} else {
		metabox.style.opacity = 0;
		metabox.style.visibility = 'hidden';
		metabox_handle.setAttribute( 'aria-expanded', 'false' );
		//metabox_handle.style.outlineStyle = 'none';
	}
	event.preventDefault();

}


/*
 * Infinite Scroll. Get more pages when you are at the bottom.
 * This function does not support multiple lists on one page.
 */
var gwolle_gb_scroll_on = true; // The end has not been reached yet. We still get entries back.
var gwolle_gb_scroll_busy = false; // Handle async well. Only one request at a time.

document.addEventListener("DOMContentLoaded", () => {

	const gwolle_gb_read = document.querySelector( '.gwolle-gb-read' );

	if ( gwolle_gb_read && gwolle_gb_read.classList.contains( 'gwolle-gb-infinite' ) ) {
		var gwolle_gb_scroll_count = 2; // We already have page 1 listed.

		var gwolle_gb_load_message = '<div class="gb-entry gwolle-gb-load-message">' + gwolle_gb_frontend_script.load_message + '</div>';
		gwolle_gb_read.insertAdjacentHTML( 'beforeend', gwolle_gb_load_message ); // append the loading message.

		window.addEventListener( 'scroll', () => {
			const scrollTop = window.scrollY || document.documentElement.scrollTop; // use window.scrollY for compatibility.
			const scrollBottom = document.documentElement.scrollHeight - window.innerHeight - 10; // have 10px diff for sensitivity.

			if ( scrollTop > scrollBottom && window.gwolle_gb_scroll_on === true && window.gwolle_gb_scroll_busy === false ) {
				gwolle_gb_scroll_busy = true;
				gwolle_gb_load_page( gwolle_gb_scroll_count );
				gwolle_gb_scroll_count++;
				gwolle_gb_scroll_busy = false;
			}
		});
	}

});
function gwolle_gb_load_page(page) {

	const load_message = document.querySelectorAll('.gwolle-gb-load-message');
	load_message.forEach( function( el ) {
		el.style.display = 'block';
	});

	const gwolle_gb_read = document.querySelector(".gwolle-gb-read");
	const book_id = gwolle_gb_read ? gwolle_gb_read.getAttribute( 'data-book_id' ) : '';

	const gwolle_gb_end_message = '<div class="gb-entry gwolle-gb-end-message">' + gwolle_gb_frontend_script.end_message + '</div>';

	const formData = new FormData();
	formData.append( 'action', 'gwolle_gb_infinite_scroll' );
	formData.append( 'pageNum', page );
	formData.append( 'permalink', window.location.href );
	formData.append( 'book_id', book_id );

	fetch( gwolle_gb_frontend_script.ajax_url, {
		method: 'POST',
		body: formData,
	})
	.then(response => response.text())
	.then(responseText => {

		load_message.forEach( function( el ) {
			el.style.display = 'none';
		});

		if (responseText === 'false') {
			if ( gwolle_gb_read ) {
				gwolle_gb_read.insertAdjacentHTML( 'beforeend', gwolle_gb_end_message );
			}
			gwolle_gb_scroll_on = false;
		} else {
			if ( gwolle_gb_read ) {
				gwolle_gb_read.insertAdjacentHTML( 'beforeend', responseText );
			}
		}

		if (typeof gwolle_gb_frontend_callback_function === 'function') {
			// Run callback for after ajax event. Used for metabox-handle for new entries among other things.
			gwolle_gb_frontend_callback_function();
		}

	//})
	//.catch( error => {
		// handle the error
	});

	return true;

}


/*
 * Mangle data for the honeypot.
 */
document.addEventListener("DOMContentLoaded", () => {

	document.querySelectorAll('form.gwolle-gb-write')?.forEach( function(form) {

		var honeypot  = gwolle_gb_frontend_script.honeypot;
		var honeypot2 = gwolle_gb_frontend_script.honeypot2;

		var honeypot_val  = parseInt( form.querySelector(`input.${honeypot}`)?.value, 10 );
		var honeypot2_val = parseInt( form.querySelector(`input.${honeypot2}`)?.value, 10 );

		if ( ! isNaN( honeypot_val ) && (typeof honeypot_val != "undefined") && (typeof honeypot2_val != "undefined") ) {
			if ( honeypot_val > 0 ) {
				form.querySelector(`input.${honeypot2}`).value = honeypot_val;
				form.querySelector(`input.${honeypot}`).value = '';
			}
		}

	});

});


/*
 * Mangle data for the form timeout.
 */
document.addEventListener("DOMContentLoaded", () => {

	document.querySelectorAll('form.gwolle-gb-write')?.forEach( function(form) {

		var timeout  = gwolle_gb_frontend_script.timeout;
		var timeout2 = gwolle_gb_frontend_script.timeout2;

		var timer  = parseInt( form.querySelector(`input.${timeout}`)?.value, 10 );
		var timer2 = parseInt( form.querySelector(`input.${timeout2}`)?.value, 10 );

		if ( ! isNaN( timer ) && ! isNaN( timer2 ) && (typeof timer != "undefined") && (typeof timer2 != "undefined") ) {

			var timer  = timer - 1;
			var timer2 = timer2 + 1;

			form.querySelector(`input.${timeout}`).value = timer;
			form.querySelector(`input.${timeout2}`).value = timer2;

		}

	});

});


// Use an object, arrays are only indexed by integers. This var is kept for compatibility with add-on 1.0.0 till 1.1.1.
var gwolle_gb_ajax_data = {
	permalink: window.location.href,
	action: 'gwolle_gb_form_ajax'
};


/*
 * AJAX Submit for Gwolle Guestbook Frontend.
 */
document.addEventListener('DOMContentLoaded', function() {

	const submit_buttons = document.querySelectorAll( '.gwolle_gb_form_ajax input.gwolle_gb_submit' );

	submit_buttons.forEach( function (button) {
		button.addEventListener( 'click', function (event) {

			const main_div = button.closest( 'div.gwolle-gb' );
			const ajax_icon = main_div.querySelector( '.gwolle_gb_submit_ajax_icon' );
			if (ajax_icon) {
				ajax_icon.style.display = 'inline';
			}

			const form = main_div.querySelector( 'form.gwolle-gb-write' );
			const formData = new FormData(); // Use an object, arrays are only indexed by integers.

			formData.append( 'permalink', window.location.href );
			formData.append( 'action', 'gwolle_gb_form_ajax' );

			const inputs = form.querySelectorAll( 'input' );
			inputs.forEach(function (input) {
				const type = input.type;
				const name = input.name;
				const val = input.value;

				if (type === 'checkbox') {
					if (input.checked) {
						formData.append( name, 'on' ); // Mimick standard $_POST value.
					}
				} else if (type === 'radio') {
					if (input.checked) {
						formData.append( name, val );
					}
				} else {
					formData.append( name, val );
				}
			});

			const textareas = form.querySelectorAll( 'textarea' );
			textareas.forEach(function (textarea) {
				formData.append( textarea.name, textarea.value );
			});

			const selects = form.querySelectorAll( 'select' );
			selects.forEach(function (select) {
				formData.append( select.name, select.value );
			});

			fetch( gwolle_gb_frontend_script.ajax_url, {
				method: 'POST',
				body: formData
			})
			.then(response => response.text())
			.then(responseText => {

				if ( gwolle_gb_is_json( responseText ) ) {
					data = JSON.parse( responseText );

					if ( ( typeof data['saved'] === 'boolean' || typeof data['saved'] === 'number' )
						&& typeof data['gwolle_gb_messages'] === 'string'
						&& typeof data['gwolle_gb_errors'] === 'boolean'
						&& typeof data['gwolle_gb_error_fields'] === 'object' ) { // Too strict in testing?

						var saved                  = data['saved'];
						var gwolle_gb_messages     = data['gwolle_gb_messages'];
						var gwolle_gb_errors       = data['gwolle_gb_errors'];
						var gwolle_gb_error_fields = data['gwolle_gb_error_fields'];

						const form_inputs = form.querySelectorAll('.gwolle_gb_form_ajax input');
						form_inputs.forEach( function (input) {
							input.classList.remove( 'error' );
						});
						const form_selects = form.querySelectorAll('.gwolle_gb_form_ajax select');
						form_selects.forEach( function (select) {
							select.classList.remove( 'error' );
						});
						const form_textareas = form.querySelectorAll('.gwolle_gb_form_ajax textarea');
						form_textareas.forEach( function (textarea) {
							textarea.classList.remove( 'error' );
						});
						const form_div_inputs = form.querySelectorAll('.gwolle_gb_form_ajax div.input'); // for type != text, like radio.
						form_div_inputs.forEach( function (div_input) {
							div_input.classList.remove( 'error' );
						});

						// we have all the data we expect.
						if ( typeof data['saved'] === 'number' ) {

							// Show returned messages.
							const messages_bottom_container = main_div.querySelectorAll( '.gwolle-gb-messages-bottom-container' );
							messages_bottom_container.forEach( function (messages) {
								messages.innerHTML = '';
							});
							const messages_top_container = main_div.querySelectorAll( '.gwolle-gb-messages-top-container' );
							messages_top_container.forEach( function (messages) {
								messages.innerHTML = '<div class="gwolle-gb-messages">' + data['gwolle_gb_messages'] + '</div>';
							});
							const gwolle_gb_messages = main_div.querySelectorAll( '.gwolle-gb-messages' );
							gwolle_gb_messages.forEach( function (messages) {
								messages.classList.remove( 'error' );
							});

							// Remove form from view.
							document.querySelectorAll( 'button.gb-notice-dismiss' ).forEach( button => {
								const main_div = button.closest( 'div.gwolle-gb' );
								const write_button = main_div.querySelector( 'div.gwolle-gb-write-button' );
								const form = main_div.querySelector( 'form.gwolle-gb-write' );

								form.style.height = '0px';
								form.classList.add( 'gwolle-gb-hide' );
								form.style.transition = 'none';
								write_button.setAttribute( 'aria-expanded', 'false' );

								write_button.style.height = '0px';
								write_button.classList.remove( 'gwolle-gb-hide' );
								write_button.style.transition = 'height 0.3s linear';
								write_button.style.height = write_button.scrollHeight + 'px';
								setTimeout(() => {
									write_button.style.height = 'auto';
									write_button.style.transition = 'none';
								}, 300);
							});

							// Prepend entry to the entry list if desired.
							if ( typeof data['entry'] === 'string' ) {
								const gwolle_gb_reads = main_div.querySelectorAll( '.gwolle-gb-read' );
								gwolle_gb_reads.forEach( function (gwolle_gb_read) {
									gwolle_gb_read.insertAdjacentHTML( 'afterbegin', data['entry'] );
								});
							}

							// Scroll to messages div. Add 80px to offset for themes with fixed headers.
							const container = document.querySelector('.gwolle-gb-messages-top-container');
							if (container) {
								const offset = container.getBoundingClientRect().top + window.scrollY - 80;
								window.scrollTo({
									top: offset,
									behavior: 'smooth'
								});
							}

							// Reset content textarea.
							const form_textareas = main_div.querySelectorAll( '.gwolle_gb_form_ajax textarea' );
							form_textareas.forEach( function (textarea) {
								textarea.value = '';
							});

							if (ajax_icon) {
								ajax_icon.style.display = 'none';
							}

							if (typeof gwolle_gb_frontend_callback_function === 'function') {
								// Run callback for after ajax event. Used for metabox-handle for new entries among other things.
								gwolle_gb_frontend_callback_function();
							}

						} else {
							// Not saved, show errors.

							// Show returned messages.
							const messages_top_container = main_div.querySelectorAll( '.gwolle-gb-messages-top-container' );
							messages_top_container.forEach( function (messages) {
								messages.innerHTML = '';
							});
							const messages_bottom_container = main_div.querySelectorAll( '.gwolle-gb-messages-bottom-container' );
							messages_bottom_container.forEach( function (messages) {
								messages.innerHTML = '<div class="gwolle-gb-messages error">' + data['gwolle_gb_messages'] + '</div>';
							});

							// Add error class to failed input fields.
							gwolle_gb_error_fields.forEach(function(value) {
								const textareas = main_div.querySelectorAll('textarea.' + value);
								const inputs = main_div.querySelectorAll('input.' + value);
								const selects = main_div.querySelectorAll('select.' + value);

								textareas.forEach(el => el.classList.add( 'error' ));

								inputs.forEach(el => {
									el.classList.add( 'error' );
									if (el.type === 'radio') {
										const wrapper = el.closest( 'div.input' );
										if (wrapper) {
											wrapper.classList.add( 'error' );
										}
									}
								});

								selects.forEach(el => {
									const wrapper = el.closest( 'div.input' );
									if (wrapper) {
										wrapper.classList.add( 'error' );
									}
								});
							});

						}
					} else {

						var unexpected_error = 'Gwolle Error: Something unexpected happened. (not the data that is expected)';

						if (typeof console != "undefined") {
							console.log( unexpected_error );
						}

						const messages_top_container = main_div.querySelectorAll( '.gwolle-gb-messages-top-container' );
						messages_top_container.forEach( function (messages) {
							messages.innerHTML = '';
						});
						const messages_bottom_container = main_div.querySelectorAll( '.gwolle-gb-messages-bottom-container' );
						messages_bottom_container.forEach( function (messages) {
							messages.innerHTML = '<div class="gwolle-gb-messages error">' + unexpected_error + '</div>';
						});

					}

				} else {

					var unexpected_error = 'Gwolle Error: Something unexpected happened. (not json data)';

					if (typeof console != "undefined") {
						console.log( 'Gwolle Error: Something unexpected happened. (not json data)' );
					}

					const messages_top_container = main_div.querySelectorAll( '.gwolle-gb-messages-top-container' );
					messages_top_container.forEach( function (messages) {
						messages.innerHTML = '';
					});
					const messages_bottom_container = main_div.querySelectorAll( '.gwolle-gb-messages-bottom-container' );
					messages_bottom_container.forEach( function (messages) {
						messages.innerHTML = '<div class="gwolle-gb-messages error">' + unexpected_error + '</div>';
					});

				}
			})

			event.preventDefault();

			if (ajax_icon) {
				ajax_icon.style.display = 'none';
			}

		});

	});

});


/*
 * Maxlength for text in textarea content.
 */
document.addEventListener('DOMContentLoaded', function () {

	const textareas = document.querySelectorAll('form.gwolle-gb-write textarea.maxlength');

	textareas.forEach(function (textarea) {
		textarea.addEventListener('keyup', function (event) {
			const div_input = event.target.closest('div.input');
			let content = event.target.value.trim();

			let length;
			if (typeof Array.from === 'function') {
				// New browsers with support for ES6 and multibyte characters like emoji.
				length = Array.from(content).length;
			} else {
				// Old browsers: Count emoji as double characters.
				length = content.length;
			}

			const span = div_input.querySelector('span.gb-used-characters');
			if (span) {
				span.textContent = length;
			}

			event.preventDefault();
		});
	});

});
function gwolle_gb_reset_used_characters() {

	const spans = document.querySelectorAll( 'div.input span.gb-used-characters' );
	spans.forEach( function (span) {
		span.textContent = 0;
	});

}


function gwolle_gb_is_json( string ) {

	try {
		JSON.parse( string );
	} catch (e) {
		return false;
	}

	return true;

}


/*
 * Abstract helper function for MarkItUp.
 * Works with jQuery and in the future without.
 *
 * @param string target element that is the textarea as target.
 * @param string bbcode the html inside square brackets that need to be added.
 *
 * @since 4.10.0
 *
 * Append image to the content field for the upload form.
 * Example variables:
 * var target = jQuery( 'textarea.gwolle_gb_content' );
 * var bbcode = '\r\n[img]' + image_url + '[/img]\r\n';
 *
 */
function gwolle_gb_markitup_replace( target, bbcode ) {

	if ( typeof jQuery.markItUp === 'function' ) {
		jQuery.markItUp( { target:target, replaceWith:bbcode } );
	}

}


/*
 * JavaScript for Gwolle Guestbook Add-On Frontend.
 */


/*
 * Upload media in form.
 *
 * With help from:
 * https://stackoverflow.com/questions/5392344/sending-multipart-formdata-with-jquery-ajax
 *
 * @since 2.3.0
 *
 * @uses markitup library, should be enabled in settings (bbcode).
 */
document.addEventListener('DOMContentLoaded', function() {

	const upload_buttons = document.querySelectorAll( 'div.gwolle-gb input.gwolle-gb-addon-upload-button' );
	upload_buttons.forEach( upload_button => {
		upload_button.addEventListener( 'click', function( event ) {

			const target = event.target;
			const main_div = target.closest( 'div.gwolle-gb' );

			const message_div = main_div.querySelector( 'div.gwolle-gb-addon-upload-message' );
			if ( message_div ) {
				message_div.innerHTML = '';
			}

			if ( ! window.File || ! window.Blob) {
				message_div.innerHTML = '<div class="gwolle-gb-messages error">The File APIs are not fully supported in this browser.</div>';
				return;
			}

			var parent = this.parentNode;
			var input = parent.querySelector('.gwolle-gb-write input[type="file"]');
			if ( ! input ) {
				message_div.innerHTML = '<div class="gwolle-gb-messages error">It seems there is no file input element.</div>';
				return;
			} else if ( ! input.files ) {
				message_div.innerHTML = '<div class="gwolle-gb-messages error">This browser does not seem to support the `files` property of file inputs.</div>';
				return;
			} else if ( ! input.files[0] ) {
				var no_file_chosen = gwolle_gb_frontend_script.no_file_chosen;
				message_div.innerHTML = '<div class="gwolle-gb-messages error">' + no_file_chosen + '</div>';
				return;
			}

			// Set Ajax icon on visible
			const ajax_icon = main_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
			if (ajax_icon) {
				ajax_icon.style.display = 'inline';
			}

			const fieldname_nonce = gwolle_gb_frontend_script.nonce;
			const input_nonce = main_div.querySelector( 'input.' + fieldname_nonce );
			const nonce = input_nonce.value;

			const file = input.files[0];

			file instanceof File; // true
			file instanceof Blob; // true

			const formData = new FormData();
			formData.append( 'action', 'gwolle_gb_addon_upload_media' );
			formData.append( 'nonce', nonce );
			formData.append( 'gwolle-gb-addon-upload-media', file );

			fetch( gwolle_gb_frontend_script.ajax_url, {
				method: 'POST',
				body: formData,
				permalink: window.location.href,
				processData: false,
				contentType: false, // set to false so we have boundaries in the form as is standard.
			})
			.then(response => response.text())
			.then(responseText => {

				// console.table( responseText );

				if ( gwolle_gb_is_json( responseText ) ) {
					data = JSON.parse( responseText );

					if ( ( typeof data['image_url'] === 'string' ) && typeof data['gwolle_gb_messages'] === 'string' ) {

						var image_url          = data['image_url'];
						var gwolle_gb_messages = data['gwolle_gb_messages'];
						var image_url_length   = image_url.length;

						if ( image_url_length > 0 ) {

							// We have an image url.

							// Append image to the content field for the upload form.
							var target = main_div.querySelector( 'textarea.gwolle_gb_content' );
							var bbcode = '\r\n[img]' + image_url + '[/img]\r\n';

							if ( typeof gwolle_gb_markitup_replace === 'function' ) { // gwolle v4.10.0 and later
								gwolle_gb_markitup_replace( target, bbcode );
								message_div.innerHTML = '<div class="gwolle-gb-messages">' + gwolle_gb_messages + '</div>';
								message_div.classList.remove( 'error' );
							} else if ( typeof jQuery.markItUp === 'function' ) { // gwolle v4.10.0 and earlier
								jQuery.markItUp( { target:target, replaceWith:bbcode } );
								message_div.innerHTML = '<div class="gwolle-gb-messages">' + gwolle_gb_messages + '</div>';
								message_div.classList.remove( 'error' );
							} else {
								message_div.innerHTML = '<div class="gwolle-gb-messages">Error, something failed. Please contact the admin of the website. Most probably the guestbook add-on is not compatible with the main plugin.</div>';
								message_div.classList.add( 'error' );
							}

						} else {

							// No image was saved...
							message_div.innerHTML = '<div class="gwolle-gb-messages error">' + gwolle_gb_messages + '</div>';
							message_div.classList.add( 'error' );

						}
					} else {
						message_div.innerHTML = '<div class="gwolle-gb-messages">Gwolle Error: Something unexpected happened. (not the data that is expected)</div>';
						message_div.classList.add( 'error' );
					}

				} else {
					message_div.innerHTML = '<div class="gwolle-gb-messages">Gwolle Error: Something unexpected happened. (not the data that is expected)</div>';
					message_div.classList.add( 'error' );
				}

				if (ajax_icon) {
					ajax_icon.style.display = 'none';
				}

			});
		});
	});
});


/*
 * Preview in the form.
 */
document.addEventListener('DOMContentLoaded', function() {

	const preview_buttons = document.querySelectorAll( 'div.gwolle_gb_submit input.gwolle_gb_preview' );

	preview_buttons.forEach( function (button) {
		button.addEventListener( 'click', function (event) {

			const main_div = button.closest( 'div.gwolle-gb' );
			const ajax_icon = main_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
			if (ajax_icon) {
				ajax_icon.style.display = 'inline';
			}

			const form = main_div.querySelector( 'form.gwolle-gb-write' );
			const formData = new FormData(); // Use an object, arrays are only indexed by integers.

			const fieldname_nonce = gwolle_gb_frontend_script.nonce;
			const input_nonce = form.querySelector( 'input.' + fieldname_nonce );
			const nonce = input_nonce.value;

			formData.append( 'permalink', window.location.href );
			formData.append( 'action', 'gwolle_gb_preview' );
			formData.append( 'nonce', nonce );

			const inputs = form.querySelectorAll( 'input' );
			inputs.forEach(function (input) {
				const type = input.type;
				const name = input.name;
				const val = input.value;

				if (type === 'checkbox') {
					if (input.checked) {
						formData.append( name, 'on' ); // Mimick standard $_POST value.
					}
				} else if (type === 'radio') {
					if (input.checked) {
						formData.append( name, val );
					}
				} else {
					formData.append( name, val );
				}
			});

			const textareas = form.querySelectorAll( 'textarea' );
			textareas.forEach(function (textarea) {
				formData.append( textarea.name, textarea.value );
			});

			const selects = form.querySelectorAll( 'select' );
			selects.forEach(function (select) {
				formData.append( select.name, select.value );
			});

			fetch( gwolle_gb_frontend_script.ajax_url, {
				method: 'POST',
				body: formData,
			})
			.then(response => response.text())
			.then(responseText => {

				// Prepend entry to the entry list if desired.
				if ( typeof responseText === 'string' ) {
					const gwolle_gb_reads = main_div.querySelectorAll( '.gwolle-gb-read' );
					gwolle_gb_reads.forEach( function (gwolle_gb_read) {
						gwolle_gb_read.insertAdjacentHTML( 'afterbegin', responseText );
					});
				}

				if (typeof gwolle_gb_frontend_callback_function === 'function') {
					// Run callback for after ajax event. Used for metabox-handle for new entries among other things.
					gwolle_gb_frontend_callback_function();
				}

			});

			if (ajax_icon) {
				ajax_icon.style.display = 'none';
			}

			event.preventDefault();
			return false;

		});

	});

});


/*
 * Admin Reply function in metabox.
 */
function gwolle_gb_addon_admin_reply() {

	const admin_reply_links = document.querySelectorAll( 'div.gb-metabox-line a.gwolle_gb_admin_reply' );

	admin_reply_links.forEach( admin_reply_link => {
		admin_reply_link.removeEventListener( 'click', gwolle_gb_addon_admin_reply_event );
		admin_reply_link.addEventListener( 'click', gwolle_gb_addon_admin_reply_event );
	});

}
function gwolle_gb_addon_admin_reply_event( event ) {

	const target = event.target;
	const entry_id = target.getAttribute( 'data-entry-id' );

	const textarea  = '<?php echo $textarea; ?>';
	const entry_div = target.closest( 'div.gb-entry' );
	const main_div = target.closest( 'div.gwolle-gb' );
	const input_nonce = main_div.querySelector( 'input.gwolle_gb_addon_frontend_list_nonce' );
	const nonce = input_nonce.value;

	// Remove old textareas.
	const textareas = entry_div.querySelectorAll( 'div#admin_reply_ajax' );
	textareas.forEach( textarea => {
		textarea.remove();
	});

	// Copy textarea.
	const contents = entry_div.querySelectorAll( '.gb-entry-content' );
	const prefab_textarea = document.getElementById( 'admin_reply_ajax_prefab_textarea' );
	const admin_reply_container = document.getElementById( 'admin-reply-container-' + entry_id );
	admin_reply_container.innerHTML = prefab_textarea.innerHTML;

	// Add event to just created cancel button.
	const admin_reply_cancels = entry_div.querySelectorAll( 'input#gwolle_gb_admin_reply_cancel' );
	admin_reply_cancels.forEach( admin_reply_cancel => {
		admin_reply_cancel.addEventListener( 'click', function( event ) {

			// Hide Ajax icon and textarea again.
			const ajax_icon = entry_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
			if ( ajax_icon ) {
				ajax_icon.style.display = 'none';
			}

			const old_admin_reply_ajaxs = entry_div.querySelectorAll( 'div#admin_reply_ajax' );
			old_admin_reply_ajaxs.forEach( old_admin_reply_ajax => {
				old_admin_reply_ajax.remove();
			});

			event.preventDefault();
			return false;
		});
	});

	const content_textareas = entry_div.querySelectorAll( 'div#admin_reply_ajax textarea' );
	content_textareas.forEach( content_textarea => {
		content_textarea.focus();
	});

	const admin_reply_submits = entry_div.querySelectorAll( 'input#gwolle_gb_admin_reply_submit' );
	admin_reply_submits.forEach( admin_reply_submit => {
		admin_reply_submit.addEventListener( 'click', function( event ) {

			const ajax_icon = entry_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
			if (ajax_icon) {
				ajax_icon.style.display = 'inline';
			}

			const formData = new FormData(); // Use an object, arrays are only indexed by integers.

			const gwolle_gb_admin_reply_text = document.querySelector( '#gwolle_gb_admin_reply_text' );
			const admin_reply_text = gwolle_gb_admin_reply_text.value;

			formData.append( 'permalink', window.location.href );
			formData.append( 'action', 'gwolle_gb_admin_reply' );
			formData.append( 'nonce', nonce );
			formData.append( 'id', entry_id );
			formData.append( 'admin_reply', admin_reply_text );

			fetch( gwolle_gb_frontend_script.ajax_url, {
				method: 'POST',
				body: formData
			})
			.then(response => response.text())
			.then(responseText => {

				const gwolle_gb_messages = entry_div.querySelectorAll( 'div#admin_reply_ajax .gwolle-gb-messages' );
				gwolle_gb_messages.forEach( gwolle_gb_message => {
					gwolle_gb_message.remove();
				});

				responseText = responseText.trim();
				var response_slice = responseText.slice(0, 5);
				var response_error = 'error';
				if ( response_slice == response_error ) {

					responseText = '<div class="gwolle-gb-messages error">' + responseText + '</div>';

					const old_admin_reply_ajaxs = entry_div.querySelectorAll( 'div#admin_reply_ajax' );
					old_admin_reply_ajaxs.forEach( old_admin_reply_ajax => {
						old_admin_reply_ajax.insertAdjacentHTML( 'beforeend', responseText );
					});

				} else {

					const entry_contents = main_div.querySelectorAll( '.gb-entry_' + entry_id + ' .gb-entry-content' );
					entry_contents.forEach( entry_content => {
						entry_content.insertAdjacentHTML( 'beforeend', responseText );
					});

					// Hide Ajax icon and remove textarea.
					const ajax_icon = entry_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
					if (ajax_icon) {
						ajax_icon.style.display = 'none';
					}
					const old_admin_reply_ajaxs = entry_div.querySelectorAll( 'div#admin_reply_ajax' );
					old_admin_reply_ajaxs.forEach( old_admin_reply_ajax => {
						old_admin_reply_ajax.remove();
					});

					// Remove metabox line for admin-reply.
					const metabox_lines = entry_div.querySelectorAll( '.gb-entry_' + entry_id + ' .gb-metabox-line-admin-reply' );
					metabox_lines.forEach( metabox_line => {
						metabox_line.remove();
					});

				}

			});

			if (ajax_icon) {
				ajax_icon.style.display = 'none';
			}

			event.preventDefault();
			return false;

		});

	});

	event.preventDefault();
	return false;

}


/*
 * Entry Edit function in metabox.
 */
function gwolle_gb_addon_entry_edit() {

	const edit_links = document.querySelectorAll( 'div.gb-metabox-line a.gwolle-gb-entry-edit' );

	edit_links.forEach( edit_link => {
		edit_link.removeEventListener( 'click', gwolle_gb_addon_entry_edit_event );
		edit_link.addEventListener( 'click', gwolle_gb_addon_entry_edit_event );
	});

}
function gwolle_gb_addon_entry_edit_event( event ) {

	const target = event.target;
	const entry_div = target.closest( 'div.gb-entry' );
	const entry_id = target.getAttribute( 'data-entry-id' );
	const main_div = target.closest( 'div.gwolle-gb' );
	const input_nonce = main_div.querySelector( 'input.gwolle_gb_addon_frontend_list_nonce' );
	const nonce = input_nonce.value;

	// Remove old textareas from entries.
	const textareas = entry_div.querySelectorAll( 'div#gwolle-gb-entry-edit-ajax' );
	textareas.forEach( textarea => {
		textarea.remove();
	});

	// Copy textarea.
	const prefab_textarea = document.getElementById( 'gwolle-gb-entry-edit-prefab' );
	const entry_edit_container = document.getElementById( 'gwolle-gb-entry-edit-container-' + entry_id );
	entry_edit_container.innerHTML = prefab_textarea.innerHTML;

	// Copy context from hidden textareas.
	const raw_content = entry_div.querySelector( '.gwolle-gb-entry-edit-content-raw' );
	if ( raw_content ) {
		const raw_content_text = raw_content.innerText;
		const edit_contents = entry_div.querySelectorAll( 'textarea.gwolle-gb-entry-edit-content' );
		edit_contents.forEach( edit_content => {
			edit_content.innerText = raw_content_text;
		});
	}
	const raw_author_name = entry_div.querySelector( '.gwolle-gb-entry-edit-author-name-raw' );
	if ( raw_author_name ) {
		const raw_author_name_text = raw_author_name.innerText;
		const edit_author_names = entry_div.querySelectorAll( 'input.gwolle-gb-entry-edit-author-name' );
		edit_author_names.forEach( edit_author_name => {
			edit_author_name.value = raw_author_name_text;
		});
	}
	const raw_origin = entry_div.querySelector( '.gwolle-gb-entry-edit-origin-raw' );
	if ( raw_origin ) { // only available when origin/city is enabled in Settings > View-tab.
		const raw_origin_text = raw_origin.innerText;
		const edit_origins = entry_div.querySelectorAll( 'input.gwolle-gb-entry-edit-origin' );
		edit_origins.forEach( edit_origin => {
			edit_origin.value = raw_origin_text;
		});
	}

	// Add event to just created cancel button.
	const edit_cancels = entry_div.querySelectorAll( 'input.gwolle-gb-entry-edit-cancel' );
	edit_cancels.forEach( edit_cancel => {
		edit_cancel.addEventListener( 'click', function( event ) {

			// Hide Ajax icon and textarea again.
			const ajax_icon = entry_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
			if (ajax_icon) {
				ajax_icon.style.display = 'none';
			}

			const textareas = entry_div.querySelectorAll( 'div#gwolle-gb-entry-edit-ajax' );
			textareas.forEach( textarea => {
				textarea.remove();
			});

			event.preventDefault();
			return false;

		});
	});

	// Set focus to content textarea for edit.
	const content_textareas = entry_div.querySelectorAll( 'div#gwolle-gb-entry-edit-ajax textarea.gwolle-gb-entry-edit-content' );
	content_textareas.forEach( content_textarea => {
		content_textarea.focus();
	});

	const submit_buttons = entry_div.querySelectorAll( '#gwolle-gb-entry-edit-ajax input.gwolle-gb-entry-edit-submit' );
	submit_buttons.forEach( submit_button => {
		submit_button.addEventListener( 'click', function( event ) {

			const ajax_icon = entry_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
			if (ajax_icon) {
				ajax_icon.style.display = 'inline';
			}

			const formData = new FormData(); // Use an object, arrays are only indexed by integers.

			const edit_content_value = entry_div.querySelector( 'textarea.gwolle-gb-entry-edit-content' );
			formData.append( 'content', edit_content_value.value );
			const edit_author_name_value = entry_div.querySelector( 'input.gwolle-gb-entry-edit-author-name' );
			formData.append( 'author_name', edit_author_name_value.value );
			const edit_origin_value = entry_div.querySelector( 'input.gwolle-gb-entry-edit-origin' );
			formData.append( 'origin', edit_origin_value.value );

			formData.append( 'permalink', window.location.href );
			formData.append( 'action', 'gwolle_gb_entry_edit' );
			formData.append( 'nonce', nonce );
			formData.append( 'id', entry_id );

			fetch( gwolle_gb_frontend_script.ajax_url, {
				method: 'POST',
				body: formData
			})
			.then(response => response.text())
			.then(responseText => {

				const gwolle_gb_messages = entry_div.querySelectorAll( 'div#gwolle-gb-entry-edit-ajax .gwolle-gb-messages' );
				gwolle_gb_messages.forEach( gwolle_gb_message => {
					gwolle_gb_message.remove();
				});

				responseText = responseText.trim();

				var response_slice = responseText.slice(0, 5);
				var response_error = 'error';

				if ( response_slice == response_error ) {

					errorText = '<div class="gwolle-gb-messages error">error</div>';

					// Hide Ajax icon and remove textarea.
					const ajax_icon = entry_div.querySelector( '.gwolle-gb-submit-ajax-icon' );
					if (ajax_icon) {
						ajax_icon.style.display = 'none';
					}
					const textareas = entry_div.querySelectorAll( 'div#gwolle-gb-entry-edit-ajax' );
					textareas.forEach( textarea => {
						textarea.insertAdjacentHTML( 'beforeend', errorText );
					});

				} else {

					const data = JSON.parse( responseText );
					const entry_content_html = data['entry_content'];
					const raw_content_html   = data['raw_content'];
					const author_name_html   = data['author_name'];
					const origin_html        = data['origin'];

					const entry_content = entry_div.querySelector( '.gb-entry_' + entry_id + ' .gb-entry-content' );
					if ( entry_content ) {
						entry_content.innerHTML = entry_content_html;
					}

					const entry_raw_content = entry_div.querySelector( '.gb-entry_' + entry_id + ' .gwolle-gb-entry-edit-content-raw' );
					if ( entry_raw_content ) {
						entry_raw_content.innerHTML = raw_content_html;
					}

					const entry_author_name = entry_div.querySelector( '.gb-entry_' + entry_id + ' .gb-author-name' );
					if ( entry_author_name ) {
						entry_author_name.innerHTML = author_name_html;
					}

					const entry_author_name_raw = entry_div.querySelector( '.gb-entry_' + entry_id + ' .gwolle-gb-entry-edit-author-name-raw' );
					if ( entry_author_name_raw ) {
						entry_author_name_raw.innerHTML = author_name_html;
					}

					const entry_origin = entry_div.querySelector( '.gb-entry_' + entry_id + ' .gb-author-origin' );
					if ( entry_origin ) {
						entry_origin.innerHTML = origin_html;
					}

					const entry_origin_raw = entry_div.querySelector( '.gb-entry_' + entry_id + ' .gwolle-gb-entry-edit-origin-raw' );
					if ( entry_origin_raw ) {
						entry_origin_raw.innerHTML = origin_html;
					}

					// Hide Ajax icon and remove textarea.
					const ajax_icon = entry_div.querySelector( '.gwolle_gb_addon_entry_edit_icon' );
					if (ajax_icon) {
						ajax_icon.style.display = 'none';
					}
					const textareas = entry_div.querySelectorAll( 'div#gwolle-gb-entry-edit-ajax' );
					textareas.forEach( textarea => {
						textarea.remove();
					});

				}

			});

			event.preventDefault();
			return false;

		});

	});

	event.preventDefault();
	return false;

}


/*
 * Delete function in metabox.
 * Only for logged in users.
 */
function gwolle_gb_addon_delete() {

	const delete_links = document.querySelectorAll( 'div.gb-metabox-line a.gwolle_gb_delete_link' );

	delete_links.forEach( delete_link => {
		delete_link.removeEventListener( 'click', gwolle_gb_addon_delete_event );
		delete_link.addEventListener( 'click', gwolle_gb_addon_delete_event );
	});

}
function gwolle_gb_addon_delete_event( event ) {

	const target = event.target;
	const entry_id = target.getAttribute( 'data-entry-id' );

	const main_div = target.closest( 'div.gwolle-gb' );
	const input_nonce = main_div.querySelector( 'input.gwolle_gb_addon_frontend_list_nonce' );
	const nonce = input_nonce.value;

	const formData = new FormData();
	formData.append( 'action', 'gwolle_gb_delete' );
	formData.append( 'id', entry_id );
	formData.append( 'setter', 'trash' );
	formData.append( 'nonce', nonce );

	// Show loading indicator
	const ajax_line = document.querySelector( '.gb-entry_' + entry_id + ' div.gb-metabox-line.gb-metabox-line-ajax' );
	if (ajax_line) {
		ajax_line.style.display = 'block';
	}

	fetch( gwolle_gb_frontend_script.ajax_url, {
		method: 'POST',
		body: formData,
	})
	.then(response => response.text())
	.then(response => {
		response = response.trim();
		if ( response === 'trash' ) {
			const entry = document.querySelector( '.gb-entry_' + entry_id );
			if (entry) {
				entry.style.transition = 'all 0.5s ease';
				entry.style.opacity = 0;
				setTimeout(() => {
					entry.style.display = 'none';
				}, 500);
			}
		}
		if (ajax_line) {
			ajax_line.style.display = 'none';
		}
	});

	event.preventDefault();
	return false;

}


/*
 * Likes in metabox.
 */

// Lock, for likes and unlikes alike.
var gwolle_gb_addon_like_busy = false;

function gwolle_gb_addon_like() {

	const like_links = document.querySelectorAll( 'div.gwolle-gb a.gwolle-gb-like-link' );

	like_links.forEach( like_link => {
		like_link.removeEventListener( 'click', gwolle_gb_addon_like_event );
		like_link.addEventListener( 'click', gwolle_gb_addon_like_event );
	});

	const unlike_links = document.querySelectorAll( 'div.gwolle-gb a.gwolle-gb-unlike-link' );

	unlike_links.forEach( unlike_link => {
		unlike_link.removeEventListener( 'click', gwolle_gb_addon_unlike_event );
		unlike_link.addEventListener( 'click', gwolle_gb_addon_unlike_event );
	});

}
function gwolle_gb_addon_like_event( event ) {

	if ( gwolle_gb_addon_like_busy === true ) {
		return;
	}
	gwolle_gb_addon_like_busy = true;

	const target = event.target;
	const entry_id = this.getAttribute( 'data-entry-id' );

	const main_div = target.closest( 'div.gwolle-gb' );
	const input_nonce = main_div.querySelector( 'input.gwolle_gb_addon_frontend_list_nonce' );
	const nonce = input_nonce.value;

	// Set up data to send
	const formData = new FormData();
	formData.append( 'action', 'gwolle_gb_like' );
	formData.append( 'nonce', nonce );
	formData.append( 'id', entry_id );
	formData.append( 'setter', 'like' );

	fetch( gwolle_gb_frontend_script.ajax_url, {
		method: 'POST',
		body: formData,
	})
	.then(response => response.text())
	.then(responseText => {

		if ( gwolle_gb_is_json( responseText ) ) {
			data = JSON.parse( responseText );

			if ( ( data['success'] === true )
				&& typeof data['likes'] === 'number'
				&& typeof data['unlikes'] === 'number'
				&& typeof data['class_likes'] === 'string'
				&& typeof data['class_unlikes'] === 'string') {

				const likes         = data['likes'];
				const unlikes       = data['unlikes'];
				const class_likes   = data['class_likes'];
				const class_unlikes = data['class_unlikes'];

				const entries = document.querySelectorAll( '.gb-entry_' + entry_id );
				entries.forEach( function(entry) {

					const a_like_links = entry.querySelectorAll( 'a.gwolle-gb-like-link' );
					a_like_links.forEach( function(a_like_link) {
						a_like_link.classList.remove( 'gb-already-liked' ); // remove old data
						if ( class_likes.length > 0 ) {
							a_like_link.classList.add( class_likes );
						}
					});

					const a_unlike_links = entry.querySelectorAll( 'a.gwolle-gb-unlike-link' );
					a_unlike_links.forEach( function(a_unlike_link) {
						a_unlike_link.classList.remove( 'gb-already-unliked' ); // remove old data
						if ( class_unlikes.length > 0 ) {
							a_unlike_link.classList.add( class_unlikes );
						}
					});

					const a_likes = entry.querySelectorAll( 'span.gb-likes' );
					a_likes.forEach( function(a_like) {
						a_like.innerHTML = likes;
					});

					const a_unlikes = entry.querySelectorAll( 'span.gb-unlikes' );
					a_unlikes.forEach( function(a_unlike) {
						a_unlike.innerHTML = unlikes;
					});

				});

			}

		}

	});

	gwolle_gb_addon_like_busy = false;

	event.preventDefault();
	return false;

}
function gwolle_gb_addon_unlike_event( event ) {

	if ( gwolle_gb_addon_like_busy === true ) {
		return;
	}
	gwolle_gb_addon_like_busy = true;

	const target = event.target;
	const entry_id = this.getAttribute( 'data-entry-id' );

	const main_div = target.closest( 'div.gwolle-gb' );
	const input_nonce = main_div.querySelector( 'input.gwolle_gb_addon_frontend_list_nonce' );
	const nonce = input_nonce.value;

	// Set up data to send
	const formData = new FormData();
	formData.append( 'action', 'gwolle_gb_unlike' );
	formData.append( 'nonce', nonce );
	formData.append( 'id', entry_id );
	formData.append( 'setter', 'unlike' );

	fetch( gwolle_gb_frontend_script.ajax_url, {
		method: 'POST',
		body: formData,
	})
	.then(response => response.text())
	.then(responseText => {

		if ( gwolle_gb_is_json( responseText ) ) {
			data = JSON.parse( responseText );

			if ( ( data['success'] === true )
				&& typeof data['likes'] === 'number'
				&& typeof data['unlikes'] === 'number'
				&& typeof data['class_likes'] === 'string'
				&& typeof data['class_unlikes'] === 'string') {

				const likes         = data['likes'];
				const unlikes       = data['unlikes'];
				const class_likes   = data['class_likes'];
				const class_unlikes = data['class_unlikes'];

				const entries = document.querySelectorAll( '.gb-entry_' + entry_id );
				entries.forEach( function(entry) {

					const a_like_links = entry.querySelectorAll( 'a.gwolle-gb-like-link' );
					a_like_links.forEach( function(a_like_link) {
						a_like_link.classList.remove( 'gb-already-liked' ); // remove old data
						if ( class_likes.length > 0 ) {
							a_like_link.classList.add( class_likes );
						}
					});

					const a_unlike_links = entry.querySelectorAll( 'a.gwolle-gb-unlike-link' );
					a_unlike_links.forEach( function(a_unlike_link) {
						a_unlike_link.classList.remove( 'gb-already-unliked' ); // remove old data
						if ( class_unlikes.length > 0 ) {
							a_unlike_link.classList.add( class_unlikes );
						}
					});

					const a_likes = entry.querySelectorAll( 'span.gb-likes' );
					a_likes.forEach( function(a_like) {
						a_like.innerHTML = likes;
					});

					const a_unlikes = entry.querySelectorAll( 'span.gb-unlikes' );
					a_unlikes.forEach( function(a_unlike) {
						a_unlike.innerHTML = unlikes;
					});

				});

			}

		}

	});

	gwolle_gb_addon_like_busy = false;

	event.preventDefault();
	return false;

}


/*
 * Static var if event is busy still.
 * @param  int  entry_id the ID of the entry where a report is being made for.
 * @param  int  call 0=getter, 1=setter true, 2=setter false.
 * @return bool is this entry busy.
 *
 * @since 2.0.0
 */
function gwolle_gb_addon_report_busy( entry_id, call ) {

	if ( typeof gwolle_gb_addon_report_busy == 'undefined' ) {
		gwolle_gb_addon_report_busy = {};
	}
	if ( typeof gwolle_gb_addon_report_busy.entry_id == 'undefined' ) {
		gwolle_gb_addon_report_busy.entry_id = false;
	}

	if ( call == 0 ) {
		return gwolle_gb_addon_report_busy.entry_id;
	} else if ( call == 1 ) {
		gwolle_gb_addon_report_busy.entry_id = true;
	} else if ( call == 2 ) {
		gwolle_gb_addon_report_busy.entry_id = false;
	}

	return gwolle_gb_addon_report_busy.entry_id;

}

function gwolle_gb_addon_report() {

	const report_links = document.querySelectorAll( 'div.gb-metabox-line a.gwolle-gb-report-abuse' );

	report_links.forEach( report_link => {
		report_link.removeEventListener( 'click', gwolle_gb_addon_report_event );
		report_link.addEventListener( 'click', gwolle_gb_addon_report_event );
	});

}
function gwolle_gb_addon_report_event( event ) {

	const target = event.target;
	const entry_id = target.getAttribute( 'data-entry-id' );

	const main_div = target.closest( 'div.gwolle-gb' );
	const input_nonce = main_div.querySelector( 'input.gwolle_gb_addon_frontend_list_nonce' );
	const nonce = input_nonce.value;

	const formData = new FormData();
	formData.append( 'action', 'gwolle_gb_report' );
	formData.append( 'nonce', nonce );
	formData.append( 'entry_id', entry_id );

	// Set Ajax icon on visible
	const ajax_line = document.querySelector( '.gb-entry_' + entry_id + ' div.gb-metabox-line.gb-metabox-line-ajax' );
	if (ajax_line) {
		ajax_line.style.display = 'block';
	}

	// Only one report for each entry at the same time.
	var busy = gwolle_gb_addon_report_busy( entry_id, 0 );
	if ( busy ) {
		event.preventDefault();
		return false;
	}
	busy = gwolle_gb_addon_report_busy( entry_id, 1 );

	fetch( gwolle_gb_frontend_script.ajax_url, {
		method: 'POST',
		body: formData,
	})
	.then(response => response.text())
	.then(response => {
		response = response.trim();
		if ( response === 'reported' ) { // We got what we wanted
			const entry = document.querySelector( '.gb-entry_' + entry_id );
			if ( entry ) {
				// Show reported message.
				const report_lines = document.querySelectorAll( '.gb-entry_' + entry_id + ' div.gb-metabox-line-report-abuse' );
				report_lines.forEach( function (report_line) {
					report_line.innerHTML = gwolle_gb_frontend_script.message_reported;
				});
			}
		} else {
			// Show error message.
			const report_lines = document.querySelectorAll( '.gb-entry_' + entry_id + ' div.gb-metabox-line-report-abuse' );
			report_lines.forEach( function (report_line) {
				report_line.innerHTML = gwolle_gb_frontend_script.message_else;
			});
		}
		// Hide Ajax icon again and set busy to false.
		if (ajax_line) {
			ajax_line.style.display = 'none';
		}
		busy = gwolle_gb_addon_report_busy( entry_id, 2 );
	});

	event.preventDefault();
	return false;

}







/*
 * Automatic Refresh. Get new entries that might have been added after loading the page.
 * This function does not support multiple lists on one page.
 */
var gwolle_gb_refresh_busy = false; // Handle async well. Only one request at a time.

document.addEventListener('DOMContentLoaded', function() {

	const gwolle_gb_read = document.querySelector( '.gwolle-gb-read' );

	if ( gwolle_gb_read && gwolle_gb_read.classList.contains( 'gwolle-gb-entries-list' ) ) {

		var book_id = parseInt( gwolle_gb_read.getAttribute( "data-book_id" ) );
		var page_num = parseInt( gwolle_gb_read.getAttribute( "data-page_id" ) );
		if ( page_num !== 1 ) {
			// console.log( 'page_num: ' + page_num );
			return;
		}

		var refresh_interval = parseInt( gwolle_gb_frontend_script.refresh_interval );
		setInterval(function() {
			gwolle_gb_addon_automatic_refresh();
		}, refresh_interval ); // 3 minutes interval by default, see gwolle-gb-addon-hooks.php.

	}

	function gwolle_gb_addon_automatic_refresh() {

		if ( gwolle_gb_refresh_busy ) {
			return; // previous instance still running, or something happened. Skip this instance for now.
		}

		gwolle_gb_refresh_busy = true;
		const gwolle_gb_first = document.querySelector( '.gwolle-gb-first' );
		var latest_entry_id = parseInt( gwolle_gb_first.getAttribute( "data-entry_id" ) );

		const formData = new FormData();
		formData.append( 'action', 'gwolle_gb_addon_refresh' );
		formData.append( 'latest_entry_id', latest_entry_id );
		formData.append( 'permalink', window.location.href );
		formData.append( 'book_id', book_id );
		formData.append( 'pageNum', page_num );

		fetch( gwolle_gb_frontend_script.ajax_url, {
			method: 'POST',
			body: formData,
		})
		.then(response => response.text())
		.then(response => {
			response = response.trim();

			if ( response !== 'false' /* no new entries. */ && response !== 'error' ) {

				gwolle_gb_first.insertAdjacentHTML( 'beforebegin', response );
				gwolle_gb_first.classList.remove( 'gwolle-gb-first' );

				/*
				 * Run callback for after ajax event. Used for metabox-handle for new entries.
				 */
				if ( typeof gwolle_gb_frontend_callback_function === 'function' ) {
					gwolle_gb_frontend_callback_function();
				}

			}

		});

		gwolle_gb_refresh_busy = false;

		return true;
	}

});



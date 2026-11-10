document.addEventListener('DOMContentLoaded', function() {
	initPrintButtons();
	initCookieBanner();
	initAutoHideHeader();
});

/* ── Print ───────────────────────────────────────── */

function initPrintButtons() {
	document.querySelectorAll('.kk-print-button').forEach(function(button) {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			var imageUrl = this.getAttribute('data-print-url');
			if (imageUrl) { printColoringPage(imageUrl); }
		});
	});
}

function printColoringPage(imageUrl) {
	var existingFrame = document.getElementById('kk-print-frame');
	if (existingFrame) { existingFrame.remove(); }

	var printFrame = document.createElement('iframe');
	printFrame.id = 'kk-print-frame';
	printFrame.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:0;height:0;border:0;visibility:hidden;';
	document.body.appendChild(printFrame);

	var printContent = '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8">' +
		'<title>Kleurplaat Afdrukken</title>' +
		'<style>@page{margin:0;size:auto}body{margin:0;padding:20mm;background:#fff;display:flex;justify-content:center;align-items:center;min-height:100vh}img{max-width:100%;max-height:100vh;width:auto;height:auto}</style>' +
		'</head><body><img src="' + imageUrl + '" onload="window.print()"></body></html>';

	printFrame.contentDocument.open();
	printFrame.contentDocument.write(printContent);
	printFrame.contentDocument.close();

	setTimeout(function() {
		if (printFrame.contentWindow) {
			printFrame.contentWindow.focus();
			printFrame.contentWindow.print();
		}
	}, 100);
	setTimeout(function() {
		var f = document.getElementById('kk-print-frame');
		if (f) { f.remove(); }
	}, 5000);
}

/* ── Cookie Banner ───────────────────────────────── */

function initCookieBanner() {
	var banner = document.getElementById('kk-cookie-banner');
	if (!banner) { return; }

	var consent = readCookie('kk_cookie_consent');
	if (consent === 'accepted' || consent === 'declined') {
		return;
	}

	setTimeout(function() { banner.classList.add('kk-cookie-banner--visible'); }, 400);

	var acceptBtn = document.getElementById('kk-cookie-accept');
	var declineBtn = document.getElementById('kk-cookie-decline');

	if (acceptBtn) {
		acceptBtn.addEventListener('click', function() {
			setCookie('kk_cookie_consent', 'accepted', 365);
			banner.classList.remove('kk-cookie-banner--visible');
			setTimeout(function() { banner.style.display = 'none'; }, 400);
		});
	}
	if (declineBtn) {
		declineBtn.addEventListener('click', function() {
			setCookie('kk_cookie_consent', 'declined', 365);
			banner.classList.remove('kk-cookie-banner--visible');
			setTimeout(function() { banner.style.display = 'none'; }, 400);
		});
	}
}

function setCookie(name, value, days) {
	var d = new Date();
	d.setTime(d.getTime() + days * 86400000);
	document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
}

function readCookie(name) {
	var parts = document.cookie.split(';');
	for (var i = 0; i < parts.length; i++) {
		var c = parts[i].trim();
		if (c.indexOf(name + '=') === 0) {
			return decodeURIComponent(c.substring(name.length + 1));
		}
	}
	return '';
}

/* ── Auto-hide Header ────────────────────────────── */

function initAutoHideHeader() {
	var header = document.querySelector('.site-header');
	if (!header) { return; }

	var lastY = 0;
	var threshold = 80;
	var hidden = false;
	var ticking = false;

	window.addEventListener('scroll', onScroll, { passive: true });
	document.addEventListener('mousemove', function(e) {
		if (e.clientY < 50 && hidden) { showHeader(); }
	});

	function onScroll() {
		if (!ticking) {
			window.requestAnimationFrame(function() {
				var y = window.pageYOffset || document.documentElement.scrollTop;

				if (y <= 10) {
					showHeader();
				} else if (y > lastY && y > threshold) {
					if (!hidden) { hideHeader(); }
				} else if (y < lastY) {
					if (hidden) { showHeader(); }
				}

				lastY = y;
				ticking = false;
			});
			ticking = true;
		}
	}

	function hideHeader() {
		header.classList.add('kk-header--hidden');
		hidden = true;
	}

	function showHeader() {
		header.classList.remove('kk-header--hidden');
		hidden = false;
	}
}
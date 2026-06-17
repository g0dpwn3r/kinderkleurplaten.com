/**
 * Kinderkleurplaten Wereld - Print Functionality
 * Pure vanilla JavaScript for optimized image printing
 */
document.addEventListener('DOMContentLoaded', function() {
	// Initialize print buttons
	initPrintButtons();
});

/**
 * Initialize all print buttons for individual coloring page printing
 */
function initPrintButtons() {
	document.querySelectorAll('.kk-print-button').forEach(function(button) {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			var imageUrl = this.getAttribute('data-print-url');
			if (imageUrl) {
				printColoringPage(imageUrl);
			}
		});
	});
}

/**
 * Print individual coloring page using hidden iframe
 * Only the coloring image is printed, no website chrome
 */
function printColoringPage(imageUrl) {
	// Remove existing print iframe if present
	var existingFrame = document.getElementById('kk-print-frame');
	if (existingFrame) {
		existingFrame.remove();
	}

	// Create hidden iframe for printing
	var printFrame = document.createElement('iframe');
	printFrame.id = 'kk-print-frame';
	printFrame.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:0;height:0;border:0;visibility:hidden;';

	document.body.appendChild(printFrame);

	// Build print document with only the image
	var printContent = '<!DOCTYPE html>' +
		'<html lang="nl">' +
		'<head>' +
		'<meta charset="UTF-8">' +
		'<title>Kleurplaat Afdrukken - Kinderkleurplaten Wereld</title>' +
		'<style>' +
		'@page { margin: 0; size: auto; }' +
		'body { margin: 0; padding: 20mm; background: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; }' +
		'img { max-width: 100%; max-height: 100vh; width: auto; height: auto; box-sizing: border-box; }' +
		'</style>' +
		'</head>' +
		'<body>' +
		'<img src="' + imageUrl + '" onload="window.print()">' +
		'</body>' +
		'</html>';

	printFrame.contentDocument.open();
	printFrame.contentDocument.write(printContent);
	printFrame.contentDocument.close();

	// Fallback: trigger print after a short delay in case onload doesn't fire
	setTimeout(function() {
		if (printFrame.contentWindow) {
			printFrame.contentWindow.focus();
			printFrame.contentWindow.print();
		}
	}, 100);

	// Clean up after print dialog closes or after delay
	setTimeout(function() {
		if (document.getElementById('kk-print-frame')) {
			document.getElementById('kk-print-frame').remove();
		}
	}, 5000);
}
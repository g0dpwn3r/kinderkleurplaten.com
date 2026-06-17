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
	document.querySelectorAll('[data-print]').forEach(function(button) {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			var imageUrl = this.getAttribute('data-print') || this.closest('article').querySelector('img').src;
			printColoringPage(imageUrl);
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
	printFrame.style.position = 'fixed';
	printFrame.style.left = '-9999px';
	printFrame.style.top = '-9999px';
	printFrame.style.width = '0';
	printFrame.style.height = '0';
	printFrame.style.border = '0';
	printFrame.style.visibility = 'hidden';

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
		'<img src="' + imageUrl + '" alt="Kleurplaat om te printen">' +
		'</body>' +
		'</html>';

	printFrame.contentDocument.open();
	printFrame.contentDocument.write(printContent);
	printFrame.contentDocument.close();

	// Wait for iframe to load then print
	printFrame.onload = function() {
		printFrame.contentWindow.focus();
		printFrame.contentWindow.print();
	};

	// Clean up after print dialog closes
	window.addEventListener('afterprint', function cleanup() {
		if (existingFrame) {
			existingFrame.remove();
		}
		window.removeEventListener('afterprint', cleanup);
	}, { once: true });
}

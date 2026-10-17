document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.content-censor-reset-form').forEach((form) => {
		form.addEventListener('submit', (event) => {
			if (!window.confirm(form.dataset.confirm)) event.preventDefault();
		});
	});
	const button = document.getElementById('content-censor-copy-summary');
	const summary = document.getElementById('content-censor-support-summary');
	button?.addEventListener('click', async () => {
		try {
			await navigator.clipboard.writeText(summary.value);
			document.getElementById('content-censor-copy-status').textContent = button.dataset.copied;
		} catch {
			summary.focus();
			summary.select();
			document.getElementById('content-censor-copy-status').textContent = button.dataset.fallback;
		}
	});
});

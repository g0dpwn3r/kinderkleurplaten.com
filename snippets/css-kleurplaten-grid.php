/*
 * WPCode Snippet: CSS for Coloring Pages Grid
 * 
 * This CSS file styles the homepage grid and individual coloring page cards.
 * It provides responsive layouts, hover effects, and print-friendly styles.
 * 
 * Installation: Import via WPCode plugin or place in theme's snippet folder
 */

/* Homepage Grid Container */
.kk-homepage-grid {
	display: grid;
	gap: 1.5rem;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	margin: 2rem 0;
}

/* Responsive Columns - 4 columns (default) */
.kk-homepage-grid--columns-4 {
	grid-template-columns: repeat(4, minmax(0, 1fr));
}

.kk-homepage-grid--columns-3 {
	grid-template-columns: repeat(3, minmax(0, 1fr));
}

.kk-homepage-grid--columns-2 {
	grid-template-columns: repeat(2, minmax(0, 1fr));
}

/* Individual Card Styles */
.kk-homepage-card {
	display: flex;
	flex-direction: column;
	height: 100%;
	padding: 1.25rem;
	border: 1px solid var(--kk-border, rgba(36, 48, 66, 0.12));
	border-radius: var(--kk-radius, 28px);
	background: rgba(255, 255, 255, 0.86);
	box-shadow: 0 10px 28px rgba(36, 48, 66, 0.08);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kk-homepage-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 15px 35px rgba(36, 48, 66, 0.12);
}

.kk-homepage-card__image {
	display: block;
	margin-bottom: 1rem;
	border-radius: 18px;
	overflow: hidden;
}

.kk-homepage-card__image img {
	display: block;
	width: 100%;
	height: auto;
	border: 1px solid var(--kk-border, rgba(36, 48, 66, 0.12));
	background: var(--kk-white, #ffffff);
	transition: transform 0.2s ease;
}

.kk-homepage-card__image img:hover {
	transform: scale(1.03);
}

.kk-homepage-card__content {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.kk-homepage-card__title {
	margin: 0 0 0.5rem;
	font-size: 1.25rem;
	font-weight: 800;
	letter-spacing: -0.03em;
}

.kk-homepage-card__title a {
	color: var(--kk-ink, #243042);
	text-decoration: none;
}

.kk-homepage-card__title a:hover {
	color: var(--kk-pink-dark, #e84d83);
}

.kk-homepage-card__excerpt {
	flex: 1;
	color: var(--kk-muted, #667085);
	font-size: 0.95rem;
	margin-bottom: 1rem;
	line-height: 1.5;
}

.kk-homepage-card__download {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.6rem 1rem;
	border-radius: 999px;
	background: var(--kk-green, #72efb2);
	color: #053c2a;
	font-weight: 800;
	text-decoration: none;
	font-size: 0.9rem;
	transition: background 0.2s ease, transform 0.2s ease;
}

.kk-homepage-card__download:hover {
	background: #5de0a3;
	transform: translateY(-2px);
}

/* Mobile Responsiveness */
@media (max-width: 1024px) {
	.kk-homepage-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
	
	.kk-homepage-grid--columns-4,
	.kk-homepage-grid--columns-3 {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 640px) {
	.kk-homepage-grid {
		grid-template-columns: 1fr;
		gap: 1.25rem;
	}
	
	.kk-homepage-grid--columns-4,
	.kk-homepage-grid--columns-3,
	.kk-homepage-grid--columns-2 {
		grid-template-columns: 1fr;
	}
	
	.kk-homepage-card {
		padding: 1.1rem;
		border-radius: 20px;
	}
	
	.kk-homepage-card__title {
		font-size: 1.15rem;
	}
	
	.kk-homepage-card__download {
		min-height: 48px;
		padding: 0.75rem 1.25rem;
	}
}

@media (max-width: 480px) {
	.kk-homepage-grid {
		grid-template-columns: 1fr;
	}
	
	.kk-homepage-card {
		flex-direction: row;
		gap: 1rem;
		text-align: left;
	}
	
	.kk-homepage-card__image {
		width: 100px;
		flex-shrink: 0;
		margin-bottom: 0;
	}
	
	.kk-homepage-card__title {
		font-size: 1rem;
	}
	
	.kk-homepage-card__download {
		font-size: 0.85rem;
		padding: 0.5rem 0.85rem;
	}
}

/* High Contrast Mode Support */
@media (prefers-contrast: high) {
	.kk-homepage-card {
		border: 2px solid var(--kk-ink, #243042);
	}
	
	.kk-homepage-card__image img {
		border: 2px solid var(--kk-ink, #243042);
	}
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
	.kk-homepage-card,
	.kk-homepage-card__download,
	.kk-homepage-card__image img {
		transition: none;
	}
	
	.kk-homepage-card:hover {
		transform: none;
	}
}
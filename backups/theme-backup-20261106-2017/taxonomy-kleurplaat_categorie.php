<?php
/**
 * Template: taxonomy-kleurplaat_categorie.php
 *
 * WordPress template hierarchy: dit bestand wordt automatisch gebruikt
 * voor archive-pagina's van de custom taxonomy 'kleurplaat_categorie',
 * vóór archive.php.
 *
 * Toont:
 *  1. SEO-meta (meta description + JSON-LD schema) geladen via wp_head.
 *  2. Banner met dynamische thumbnail (featured image van de eerste
 *     kleurplaat in deze categorie).
 *  3. Beschrijving, leerdoelen ("Wat kun je leren?") en FAQ.
 *  4. Grid van alle kleurplaten binnen de categorie.
 *  5. WordPress paginatie.
 */

get_template_part('snippets/category-descriptions');

$term = get_queried_object();
$term_name = ($term && isset($term->name)) ? $term->name : '';

/* SEO-meta voorbereiden zodat de meta description voor get_header() klaar staat */
$seo_meta = ! empty($term_name) && function_exists('kk_get_term_seo_meta')
	? kk_get_term_seo_meta($term_name)
	: null;

if (! empty($seo_meta['meta_description'])) {
	add_action('wp_head', function () use ($seo_meta) {
		echo '<meta name="description" content="' . esc_attr($seo_meta['meta_description']) . '">' . "\n";
	}, 9);
}

$category_description = ($term && function_exists('kk_get_category_description'))
	? kk_get_category_description($term_name)
	: '';

/* JSON-LD: CollectionPage schema */
$collection_schema = null;
if (! empty($seo_meta)) {
	$collection_schema = array(
		'@context'       => 'https://schema.org',
		'@type'          => $seo_meta['schema_type'] ?? 'CollectionPage',
		'name'           => $term_name,
		'description'    => $seo_meta['meta_description'] ?? '',
		'keywords'       => $seo_meta['focus_keyword'] ?? '',
		'url'            => $term ? get_term_link($term) : '',
		'numberOfItems'  => ($term && isset($term->count)) ? (int) $term->count : 0,
		'audience'       => array(
			'@type'        => 'PeopleAudience',
			'suggestedAge' => $seo_meta['age_range'] ?? '3-7 jaar',
		),
	);
}

/* FAQ-data: zichtbaar op de pagina én als gestructureerde data */
$faq_items = array();
if (! empty($seo_meta)) {
	$faq_items[] = array(
		'question' => __('Voor welke leeftijd zijn deze kleurplaten geschikt?', 'kinderkleurplaten'),
		'answer'   => sprintf(
			__('Deze kleurplaten zijn geschikt voor kinderen van %s.', 'kinderkleurplaten'),
			$seo_meta['age_range'] ?? '3-7 jaar'
		),
	);
	if (! empty($seo_meta['learning_goals'])) {
		$faq_items[] = array(
			'question' => __('Wat kun je leren bij dit onderwerp?', 'kinderkleurplaten'),
			'answer'   => sprintf(
				__('Bij dit thema leer je onder meer: %s.', 'kinderkleurplaten'),
				implode(', ', $seo_meta['learning_goals'])
			),
		);
	}
	$faq_items[] = array(
		'question' => __('Hoe werkt het met de gratis kleurplaten?', 'kinderkleurplaten'),
		'answer'   => __('Open een kleurplaat, kleur de blanco lijnen in met potloden of pennen en print de pagina zodat je hem meedergeeft of opbergt.', 'kinderkleurplaten'),
	);
}

get_header();
?>

<main id="primary" class="site-main" aria-label="<?php esc_attr_e('Categorie kleurplaten', 'kinderkleurplaten'); ?>">
	<div class="container">
		<?php
		if (function_exists('kinderkleurplaten_breadcrumbs')) {
			kinderkleurplaten_breadcrumbs();
		}
		?>

		<header class="section-header" aria-label="<?php esc_attr_e('Categorie-introductie', 'kinderkleurplaten'); ?>">
			<div>
				<p class="eyebrow"><?php esc_html_e('Categorie', 'kinderkleurplaten'); ?></p>
				<h1 class="archive-title"><?php echo esc_html($term ? $term->name : single_term_title('', false)); ?></h1>

				<?php if (! empty($category_description)) : ?>
					<div class="archive-description">
						<p id="archive-description"><?php echo esc_html($category_description); ?></p>
					</div>
				<?php endif; ?>

				<?php
				/* Aantal kleurplaten in deze categorie */
				if ($term && isset($term->count)) {
					printf(
						'<p class="archive-count"><strong>%s</strong></p>',
						esc_html(sprintf(
							_n('%s kleurplaat', '%s kleurplaten', $term->count, 'kinderkleurplaten'),
							number_format_i18n($term->count)
						))
					);
				}
				?>
			</div>

			<?php
			/**
			 * Banner: featured image van de eerste kleurplaat in deze
			 * categorie. function_exists()-guard + lege-check voorkomen
			 * fatal errors.
			 */
			$banner = false;
			if (function_exists('kinderkleurplaten_find_colouring_image')) {
				$banner = kinderkleurplaten_find_colouring_image(); // geen arg → archive context
			}
			if ($banner && ! empty($banner['url'])) :
			?>
				<div class="archive-banner" aria-hidden="true">
					<img src="<?php echo esc_url($banner['url']); ?>"
						 alt="<?php echo ! empty($banner['alt']) ? esc_attr($banner['alt']) : esc_attr($term ? $term->name : ''); ?>"
						 loading="lazy" width="420" height="420">
				</div>
			<?php endif; ?>
		</header>

		<hr aria-hidden="true" style="border:none;border-top:2px solid var(--kk-border);margin:2rem 0 2.5rem;">

		<?php if (! empty($seo_meta['learning_goals'])) : ?>
			<section class="section kk-learning-goals" aria-labelledby="kk-learning-title">
				<h2 id="kk-learning-title" class="section-title"><?php esc_html_e('Wat kun je leren?', 'kinderkleurplaten'); ?></h2>
				<p class="section-lead"><?php esc_html_e('Met de kleurplaten uit deze categorie oefen je ondersteunende vaardigheden.', 'kinderkleurplaten'); ?></p>
				<ul class="kk-learning-list">
					<?php foreach ($seo_meta['learning_goals'] as $goal) : ?>
						<li class="kk-learning-item"><?php echo esc_html($goal); ?></li>
					<?php endforeach; ?>
				</ul>
			</section>

			<hr aria-hidden="true" style="border:none;border-top:2px solid var(--kk-border);margin:2rem 0 2.5rem;">
		<?php endif; ?>

		<?php if (have_posts()) : ?>
			<section class="section" aria-label="<?php esc_attr_e('Alle kleurplaten', 'kinderkleurplaten'); ?>">
				<div class="posts-grid">
					<?php while (have_posts()) : the_post(); ?>

						<?php
						$post_image = false;
						if (function_exists('kinderkleurplaten_find_colouring_image')) {
							$post_image = kinderkleurplaten_find_colouring_image(get_the_ID());
						}
						?>

						<article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?> aria-labelledby="post-<?php the_ID(); ?>-title">
							<?php if (has_post_thumbnail()) : ?>
								<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
									<?php the_post_thumbnail('medium', array('loading' => 'lazy', 'alt' => the_title_attribute(array('echo' => false)))); ?>
								</a>
							<?php elseif ($post_image && ! empty($post_image['url'])) : ?>
								<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
									<img src="<?php echo esc_url($post_image['url']); ?>"
										 alt="<?php echo ! empty($post_image['alt']) ? esc_attr($post_image['alt']) : esc_attr(get_the_title()); ?>"
										 loading="lazy" width="300" height="300">
								</a>
							<?php endif; ?>

							<h3 id="post-<?php the_ID(); ?>-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

							<?php if (function_exists('kinderkleurplaten_excerpt')) : ?>
								<p><?php echo esc_html(kinderkleurplaten_excerpt(18)); ?></p>
							<?php else : ?>
								<p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
							<?php endif; ?>
						</article>

					<?php endwhile; ?>
				</div>

				<nav class="pagination" aria-label="<?php esc_attr_e('Paginanavigatie kleurplaten', 'kinderkleurplaten'); ?>">
					<?php
					the_posts_pagination(array(
						'prev_text' => __('&laquo; Vorige', 'kinderkleurplaten'),
						'next_text' => __('Volgende &raquo;', 'kinderkleurplaten'),
					));
					?>
				</nav>
			</section>

		<?php else : ?>

			<section class="section" aria-label="<?php esc_attr_e('Geen resultaten', 'kinderkleurplaten'); ?>">
				<article class="entry">
					<header class="entry-header">
						<h1 class="entry-title"><?php esc_html_e('Geen kleurplaten gevonden', 'kinderkleurplaten'); ?></h1>
					</header>
					<div class="entry-content">
						<p><?php esc_html_e('Er zijn nog geen kleurplaten in deze categorie.', 'kinderkleurplaten'); ?></p>
					</div>
				</article>
			</section>

		<?php endif; ?>

		<?php if (! empty($faq_items)) : ?>
			<hr aria-hidden="true" style="border:none;border-top:2px solid var(--kk-border);margin:2rem 0 2.5rem;">

			<section class="section kk-faq" aria-labelledby="kk-faq-title">
				<h2 id="kk-faq-title" class="section-title"><?php esc_html_e('Veelgestelde vragen', 'kinderkleurplaten'); ?></h2>
				<dl class="kk-faq-list">
					<?php foreach ($faq_items as $item) : ?>
						<dt class="kk-faq-question"><?php echo esc_html($item['question']); ?></dt>
						<dd class="kk-faq-answer"><?php echo esc_html($item['answer']); ?></dd>
					<?php endforeach; ?>
				</dl>
			</section>
		<?php endif; ?>
	</div>
</main>

<?php if (! empty($collection_schema)) : ?>
	<script type="application/ld+json">
	<?php echo wp_json_encode($collection_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
	</script>
<?php endif;

if (! empty($faq_items)) :
	$faq_schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'FAQPage',
		'mainEntity' => array(),
	);
	foreach ($faq_items as $item) {
		$faq_schema['mainEntity'][] = array(
			'@type'           => 'Question',
			'name'            => $item['question'],
			'acceptedAnswer'  => array(
				'@type' => 'Answer',
				'text'  => $item['answer'],
			),
		);
	}
	?>
	<script type="application/ld+json">
	<?php echo wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
	</script>
<?php endif; ?>

<?php get_footer(); ?>

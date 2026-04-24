<?php
/**
 * Template helpers.
 *
 * @package Energiesozietaet
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Render a page head (dark band, eyebrow + title).
 */
function es_page_head( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'eyebrow'   => '',
		'title'     => '',
		'lead'      => '',
		'crumbs'    => array(), // [ [label, url|null] ]
	) );
	?>
	<section class="es-page-head">
		<div class="es-wrap">
			<?php if ( ! empty( $args['crumbs'] ) ) : ?>
				<div class="es-page-head__crumbs">
					<?php
					$parts = array();
					foreach ( $args['crumbs'] as $c ) {
						if ( ! empty( $c[1] ) ) {
							$parts[] = '<a href="' . esc_url( $c[1] ) . '">' . esc_html( $c[0] ) . '</a>';
						} else {
							$parts[] = esc_html( $c[0] );
						}
					}
					echo implode( ' <span aria-hidden="true">›</span> ', $parts );
					?>
				</div>
			<?php endif; ?>

			<?php if ( $args['eyebrow'] ) : ?>
				<span class="es-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span>
			<?php endif; ?>

			<h1 class="es-reveal is-visible"><?php echo wp_kses_post( $args['title'] ); ?></h1>

			<?php if ( $args['lead'] ) : ?>
				<p class="es-page-head__lead"><?php echo wp_kses_post( $args['lead'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Return the ACF-like meta helper.
 */
function es_meta( $key, $post_id = null ) {
	return get_post_meta( $post_id ? $post_id : get_the_ID(), $key, true );
}

/**
 * Render post navigation "Zurück zur Übersicht".
 */
function es_back_link( $url, $label ) {
	printf(
		'<a class="es-btn es-btn--link" href="%s">← %s</a>',
		esc_url( $url ),
		esc_html( $label )
	);
}

/**
 * Render a figure with rounded media.
 */
function es_image( $attachment_id, $size = 'large', $class = '' ) {
	if ( ! $attachment_id ) { return; }
	echo wp_get_attachment_image( $attachment_id, $size, false, array( 'class' => $class, 'loading' => 'lazy' ) );
}

/**
 * Beratungsfeld label helper.
 */
function es_beratungsfeld_label( $slug ) {
	$map = array(
		'rechtsberatung'        => 'Rechtsberatung',
		'steuerberatung'        => 'Steuerberatung',
		'unternehmensberatung'  => 'Unternehmensberatung',
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : ucfirst( $slug );
}

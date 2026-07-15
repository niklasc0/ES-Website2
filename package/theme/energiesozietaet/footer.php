<?php
/**
 * Site footer.
 * Inhalte kommen aus den Footer-Einstellungen (Design → Footer). Spalten
 * mit leerer Überschrift werden ausgeblendet – die übrigen rücken zusammen.
 *
 * @package Energiesozietaet
 */
// ah5: keine globale Footer-CTA – die Startseite bringt ihre eigene CTA-Sektion
// aus der Blueprint mit; Unterseiten haben keine. Vermeidet Doppelung.
$es_no_cta = true;

if ( class_exists( 'ESC_Footer_Settings' ) ) {
	$opts = ESC_Footer_Settings::get();
	$parse_lines = array( 'ESC_Footer_Settings', 'parse_lines' );
	$parse_links = array( 'ESC_Footer_Settings', 'parse_links' );
} else {
	$opts = array();
	$parse_lines = function( $s ) { return array_values( array_filter( array_map( 'trim', preg_split( '/\r?\n/', (string) $s ) ) ) ); };
	$parse_links = function( $s ) {
		$out = array();
		foreach ( preg_split( '/\r?\n/', (string) $s ) as $ln ) {
			$ln = trim( $ln ); if ( '' === $ln ) continue;
			$parts = array_map( 'trim', explode( '|', $ln, 2 ) );
			$url   = $parts[1] ?? '';
			if ( $url && ! preg_match( '#^(https?://|/|\#|mailto:|tel:)#i', $url ) ) {
				$out[] = array( 'label' => $parts[0] . ' · ' . $url, 'url' => '' );
				continue;
			}
			$out[] = array( 'label' => $parts[0], 'url' => $url );
		}
		return $out;
	};
}
$g = function( $k, $default = '' ) use ( $opts ) { return $opts[ $k ] ?? $default; };
$copyright = str_replace( '{year}', date_i18n( 'Y' ), (string) $g( 'copyright', '© {year} Energiesozietät GmbH' ) );

// Aktive Spalten ermitteln (Heading nicht leer)
$columns = array();
for ( $i = 1; $i <= 2; $i++ ) {
	$h = trim( (string) $g( "col{$i}_heading" ) );
	if ( $h === '' ) { continue; }
	$columns[] = array(
		'heading' => $h,
		'links'   => call_user_func( $parse_links, $g( "col{$i}_lines" ) ),
	);
}
$col_count = count( $columns );
// Rechtliches (Spalte 3) speist ausschließlich die zentrierte Copyright-Leiste.
$legal_links = call_user_func( $parse_links, $g( 'col3_lines' ) );
?>
</main><!-- /#es-main -->

<footer class="es-footer" role="contentinfo">
	<?php if ( ! $es_no_cta ) : ?>
	<div class="es-footer__cta">
		<div class="es-wrap es-footer__cta-grid">
			<div>
				<div class="es-eyebrow es-eyebrow--accent"><?php echo esc_html( $g( 'cta_eyebrow', 'Kontakt' ) ); ?></div>
				<h2>
					<?php echo esc_html( $g( 'cta_title', 'Haben wir Ihr Interesse geweckt?' ) ); ?>
					<?php if ( $g( 'cta_subtitle' ) ) : ?>
						<br/><span class="es-footer__cta-sub"><?php echo wp_kses_post( $g( 'cta_subtitle' ) ); ?></span>
					<?php endif; ?>
				</h2>
			</div>
			<div class="es-footer__cta-actions">
				<?php if ( $g( 'cta_btn1_label' ) ) : ?>
					<a class="es-btn es-btn--paper" href="<?php echo esc_url( $g( 'cta_btn1_url', '#' ) ); ?>"><?php echo esc_html( $g( 'cta_btn1_label' ) ); ?> →</a>
				<?php endif; ?>
				<?php if ( $g( 'cta_btn2_label' ) ) : ?>
					<a class="es-btn es-btn--ghost-paper" href="<?php echo esc_url( $g( 'cta_btn2_url', '#' ) ); ?>"><?php echo esc_html( $g( 'cta_btn2_label' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="es-footer__main">
		<div class="es-wrap">
			<div class="es-footer__grid es-footer__grid--cols-<?php echo (int) $col_count; ?>">
				<div class="es-footer__brand">
					<div class="es-footer__brand-name"><?php echo esc_html( $g( 'brand_name', 'Energiesozietät GmbH' ) ); ?></div>
					<div class="es-footer__brand-sub"><?php echo esc_html( $g( 'brand_sub', 'Recht · Steuern · Beratung' ) ); ?></div>
					<p class="es-footer__brand-claim"><?php echo wp_kses_post( $g( 'brand_claim' ) ); ?></p>
					<?php $badges = call_user_func( $parse_lines, $g( 'badges' ) ); if ( $badges ) : ?>
						<div class="es-footer__badges">
							<?php foreach ( $badges as $b ) : ?><span class="es-footer__badge"><?php echo esc_html( $b ); ?></span><?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php foreach ( $columns as $idx => $c ) : ?>
					<div class="es-footer__col es-footer__col--<?php echo (int) ( $idx + 1 ); ?>">
						<h4><?php echo esc_html( $c['heading'] ); ?></h4>
						<ul>
							<?php foreach ( $c['links'] as $link ) : ?>
								<li>
									<?php if ( $link['url'] ) : ?>
										<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $link['label'] ); ?>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="es-footer__bar">
				<span><?php echo esc_html( $copyright ); ?></span>
				<ul>
					<?php
					foreach ( $legal_links as $link ) {
						if ( $link['url'] ) {
							echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['label'] ) . '</a></li>';
						} else {
							echo '<li>' . esc_html( $link['label'] ) . '</li>';
						}
					}
					?>
				</ul>
				<span aria-hidden="true"></span>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

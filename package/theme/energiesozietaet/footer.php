<?php
/**
 * Site footer.
 * Inhalte kommen aus den Footer-Einstellungen (Einstellungen → Footer
 * (Energiesozietät)). Fallback: Klassen bleiben rein dekorativ, wenn das
 * Plugin fehlt.
 *
 * @package Energiesozietaet
 */
$es_no_cta = is_page( array( 'kontakt', 'impressum', 'datenschutzerklaerung' ) ) || is_404();

// Optionen vom Plugin laden (mit Defaults falls Plugin inaktiv)
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
			$out[] = array( 'label' => $parts[0], 'url' => $parts[1] ?? '' );
		}
		return $out;
	};
}
$g = function( $k, $default = '' ) use ( $opts ) { return $opts[ $k ] ?? $default; };
$copyright = str_replace( '{year}', date_i18n( 'Y' ), (string) $g( 'copyright', '© {year} Energiesozietät GmbH' ) );
?>
</main><!-- /#es-main -->

<footer class="es-footer" role="contentinfo">
	<?php if ( ! $es_no_cta ) : ?>
	<div class="es-footer__cta">
		<div class="es-wrap es-footer__cta-grid">
			<div>
				<div class="es-eyebrow es-eyebrow--accent"><?php echo esc_html( $g( 'cta_eyebrow', 'Kontakt' ) ); ?></div>
				<h2>
					<?php echo esc_html( $g( 'cta_title', 'Sprechen Sie mit uns.' ) ); ?>
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
			<div class="es-footer__grid">
				<div>
					<div class="es-footer__brand-name"><?php echo esc_html( $g( 'brand_name', 'Energiesozietät GmbH' ) ); ?></div>
					<div class="es-footer__brand-sub"><?php echo esc_html( $g( 'brand_sub', 'Recht · Steuern · Beratung' ) ); ?></div>
					<p class="es-footer__brand-claim"><?php echo wp_kses_post( $g( 'brand_claim' ) ); ?></p>
					<?php $badges = call_user_func( $parse_lines, $g( 'badges' ) ); if ( $badges ) : ?>
						<div class="es-footer__badges">
							<?php foreach ( $badges as $b ) : ?><span class="es-footer__badge"><?php echo esc_html( $b ); ?></span><?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="es-footer__col">
					<h4><?php echo esc_html( $g( 'col_anschrift_heading', 'Anschrift' ) ); ?></h4>
					<ul>
						<?php foreach ( call_user_func( $parse_lines, $g( 'col_anschrift_lines' ) ) as $ln ) : ?>
							<li><?php echo esc_html( $ln ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="es-footer__col">
					<h4><?php echo esc_html( $g( 'col_kontakt_heading', 'Kontakt' ) ); ?></h4>
					<ul>
						<?php foreach ( call_user_func( $parse_links, $g( 'col_kontakt_lines' ) ) as $link ) : ?>
							<li>
								<?php if ( $link['url'] ) : ?><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a><?php else : ?><?php echo esc_html( $link['label'] ); ?><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="es-footer__col">
					<h4><?php echo esc_html( $g( 'col_legal_heading', 'Rechtliches' ) ); ?></h4>
					<ul>
						<?php foreach ( call_user_func( $parse_links, $g( 'col_legal_lines' ) ) as $link ) : ?>
							<li>
								<?php if ( $link['url'] ) : ?><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a><?php else : ?><?php echo esc_html( $link['label'] ); ?><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<div class="es-footer__bar">
				<span><?php echo esc_html( $copyright ); ?></span>
				<ul>
					<?php foreach ( call_user_func( $parse_links, $g( 'col_legal_lines' ) ) as $link ) : ?>
						<?php if ( $link['url'] ) : ?><li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li><?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

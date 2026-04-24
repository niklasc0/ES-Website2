<?php
/**
 * Site footer (G2 + G3 per mockup).
 *
 * G3 · Footer-CTA (dark) als Pre-Footer-Bar (nur anzeigen, wenn die Seite nicht
 * selbst bereits mit einer Dark-CTA endet — gesteuert via Body-Class es-no-cta).
 * G2 · 3-Spalten-Footer mit Brand · Büro · Kontakt · Rechtliches.
 *
 * @package Energiesozietaet
 */
$es_no_cta = is_page( array( 'kontakt', 'impressum', 'datenschutzerklaerung' ) ) || is_404();
?>
</main><!-- /#es-main -->

<footer class="es-footer" role="contentinfo">
	<?php if ( ! $es_no_cta ) : ?>
	<div class="es-footer__cta">
		<div class="es-wrap es-footer__cta-grid">
			<div>
				<div class="es-eyebrow es-eyebrow--accent"><?php esc_html_e( 'Kontakt', 'energiesozietaet' ); ?></div>
				<h2>
					<?php esc_html_e( 'Sprechen Sie mit uns.', 'energiesozietaet' ); ?><br/>
					<span class="es-footer__cta-sub"><?php esc_html_e( 'Unaufgeregt, direkt, fachlich.', 'energiesozietaet' ); ?></span>
				</h2>
			</div>
			<div class="es-footer__cta-actions">
				<a class="es-btn es-btn--paper" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>"><?php esc_html_e( 'Termin vereinbaren', 'energiesozietaet' ); ?> →</a>
				<a class="es-btn es-btn--ghost-paper" href="mailto:info@energiesozietaet.de">info@energiesozietaet.de</a>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="es-footer__main">
		<div class="es-wrap">
			<div class="es-footer__grid">
				<div>
					<div class="es-footer__brand-name">Energiesozietät GmbH</div>
					<div class="es-footer__brand-sub">Recht · Steuern · Beratung</div>
					<p class="es-footer__brand-claim"><?php esc_html_e( 'Beratung mit Leidenschaft — Ergebnisse, die weitertragen.', 'energiesozietaet' ); ?></p>
					<div class="es-footer__badges">
						<span class="es-footer__badge">BVÖD</span>
						<span class="es-footer__badge">Forum Contracting</span>
						<span class="es-footer__badge">VKU</span>
					</div>
				</div>

				<div class="es-footer__col">
					<h4><?php esc_html_e( 'Büro', 'energiesozietaet' ); ?></h4>
					<ul>
						<li>Energiesozietät GmbH</li>
						<li>Roßstraße 92 / Kennedyhaus</li>
						<li>40476 Düsseldorf</li>
					</ul>
				</div>

				<div class="es-footer__col">
					<h4><?php esc_html_e( 'Kontakt', 'energiesozietaet' ); ?></h4>
					<ul>
						<li><a href="tel:+492111592320">+49 211 159232-0</a></li>
						<li><a href="mailto:info@energiesozietaet.de">info@energiesozietaet.de</a></li>
						<li><a href="https://www.linkedin.com/company/energiesozietaet/" rel="noopener" target="_blank">LinkedIn ↗</a></li>
					</ul>
				</div>

				<div class="es-footer__col">
					<h4><?php esc_html_e( 'Rechtliches', 'energiesozietaet' ); ?></h4>
					<?php
					if ( has_nav_menu( 'legal' ) ) {
						wp_nav_menu( array( 'theme_location' => 'legal', 'container' => false, 'depth' => 1 ) );
					} else { ?>
						<ul>
							<li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a></li>
							<li><a href="<?php echo esc_url( home_url( '/datenschutzerklaerung/' ) ); ?>">Datenschutz</a></li>
						</ul>
					<?php } ?>
				</div>
			</div>

			<div class="es-wrap">
				<div class="es-footer__bar">
					<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> Energiesozietät GmbH · <?php esc_html_e( 'Alle Rechte vorbehalten.', 'energiesozietaet' ); ?></span>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>"><?php esc_html_e( 'Impressum', 'energiesozietaet' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/datenschutzerklaerung/' ) ); ?>"><?php esc_html_e( 'Datenschutz', 'energiesozietaet' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

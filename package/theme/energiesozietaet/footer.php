<?php
/**
 * Site footer.
 *
 * @package Energiesozietaet
 */
$address_lines = array(
	'Energiesozietät GmbH',
	'Recht | Steuern | Beratung',
	'Roßstraße 92 | Kennedyhaus',
	'40476 Düsseldorf',
);
?>
</main><!-- /#es-main -->

<footer class="es-footer" role="contentinfo">
	<div class="es-wrap">
		<div class="es-footer__grid">
			<div>
				<h4><?php esc_html_e( 'Kanzlei', 'energiesozietaet' ); ?></h4>
				<p style="font-size:.98rem;line-height:1.7;color:rgba(255,255,255,.74);">
					<?php echo implode( '<br />', array_map( 'esc_html', $address_lines ) ); ?>
				</p>
			</div>
			<div>
				<h4><?php esc_html_e( 'Kontakt', 'energiesozietaet' ); ?></h4>
				<ul>
					<li>Tel.&nbsp;<a href="tel:+492111592320">+49 211-159232-0</a></li>
					<li>Fax&nbsp;+49 211-17956838</li>
					<li><a href="mailto:info@energiesozietaet.de">info@energiesozietaet.de</a></li>
				</ul>
			</div>
			<div>
				<h4><?php esc_html_e( 'Leistungen', 'energiesozietaet' ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1 ) );
				} else { ?>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/rechtsberatung/' ) ); ?>">Rechtsberatung</a></li>
					<li><a href="<?php echo esc_url( home_url( '/steuerberatung/' ) ); ?>">Steuerberatung</a></li>
					<li><a href="<?php echo esc_url( home_url( '/unternehmensberatung/' ) ); ?>">Unternehmensberatung</a></li>
				</ul>
				<?php } ?>
			</div>
			<div>
				<h4><?php esc_html_e( 'Kanzlei', 'energiesozietaet' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/philosophie/' ) ); ?>">Philosophie</a></li>
					<li><a href="<?php echo esc_url( home_url( '/team/' ) ); ?>">Team</a></li>
					<li><a href="<?php echo esc_url( home_url( '/karriere/' ) ); ?>">Karriere</a></li>
					<li><a href="<?php echo esc_url( home_url( '/publikationen/' ) ); ?>">Publikationen</a></li>
					<li><a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">News</a></li>
				</ul>
			</div>
		</div>

		<div class="es-footer__bar">
			<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> Energiesozietät GmbH</span>
			<nav aria-label="<?php esc_attr_e( 'Rechtliches', 'energiesozietaet' ); ?>">
				<?php
				if ( has_nav_menu( 'legal' ) ) {
					wp_nav_menu( array( 'theme_location' => 'legal', 'container' => false, 'depth' => 1, 'menu_class' => 'es-footer__legal' ) );
				} else { ?>
					<ul style="display:flex;gap:24px;list-style:none;padding:0;margin:0;">
						<li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a></li>
						<li><a href="<?php echo esc_url( home_url( '/datenschutzerklaerung/' ) ); ?>">Datenschutz</a></li>
					</ul>
				<?php } ?>
			</nav>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

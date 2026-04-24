/*!
 * Energiesozietät UI — minimal JS for nav toggle + scroll reveal + header condense.
 */
(function () {
	'use strict';

	// 0. Mark html as JS-enabled so reveal CSS becomes active
	document.documentElement.classList.add('js');

	// 1. Mobile nav toggle — Klasse kommt auf den HEADER (passend zum CSS).
	var toggle = document.querySelector('.es-nav-toggle');
	var header = document.getElementById('es-header');
	var nav    = document.getElementById('es-nav');
	if (toggle && header && nav) {
		toggle.addEventListener('click', function (e) {
			e.preventDefault();
			var open = header.classList.toggle('is-nav-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.body.style.overflow = open ? 'hidden' : '';
		});
		// Close when a link is clicked (on mobile)
		nav.addEventListener('click', function (e) {
			if (window.innerWidth > 1024) return;
			var a = e.target.closest('a');
			if (a && !a.closest('.sub-menu')) {
				header.classList.remove('is-nav-open');
				toggle.setAttribute('aria-expanded', 'false');
				document.body.style.overflow = '';
			}
		});
	}

	// 2. Condense header on scroll
	var header = document.getElementById('es-header');
	if (header) {
		var condense = function () {
			header.classList.toggle('is-condensed', window.scrollY > 12);
		};
		condense();
		window.addEventListener('scroll', condense, { passive: true });
	}

	// 3. Scroll reveal via IntersectionObserver — auto-tag standard blocks.
	if ('IntersectionObserver' in window) {
		// Auto-tag common targets that aren't already reveal
		var autoTargets = document.querySelectorAll('.es-card, .es-team-card, .es-section h2, .es-section > .es-wrap > .es-grid, .es-prose > :first-child');
		autoTargets.forEach(function (el) {
			if (!el.classList.contains('es-reveal') && !el.classList.contains('is-visible')) {
				el.classList.add('es-reveal');
			}
		});
		var obs = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('is-in', 'is-visible');
					obs.unobserve(e.target);
				}
			});
		}, { threshold: 0.08, rootMargin: '0px 0px -4% 0px' });
		document.querySelectorAll('.es-reveal').forEach(function (el) { obs.observe(el); });
	} else {
		document.querySelectorAll('.es-reveal').forEach(function (el) { el.classList.add('is-in', 'is-visible'); });
	}

	// 3b. Client-side team filter (no page reload)
	var teamFilter = document.querySelector('.es-team-filter');
	if (teamFilter) {
		var teamGrid = teamFilter.parentElement.querySelector('.esc-grid') || document.querySelector('.esc-grid');
		var countEl = teamFilter.querySelector('.es-team-filter__count');
		var pills = teamFilter.querySelectorAll('.es-team-filter__pill');
		pills.forEach(function (pill) {
			pill.addEventListener('click', function (e) {
				e.preventDefault();
				var url = new URL(pill.href, window.location.origin);
				var feld = url.searchParams.get('feld') || '';
				// Active-Pill-Wechsel
				pills.forEach(function (p) { p.classList.remove('is-active'); });
				pill.classList.add('is-active');
				// URL ohne Reload updaten
				var newUrl = new URL(window.location.href);
				if (feld) { newUrl.searchParams.set('feld', feld); } else { newUrl.searchParams.delete('feld'); }
				window.history.replaceState(null, '', newUrl.toString());
				// Karten filtern
				var cards = teamGrid ? teamGrid.querySelectorAll('.esc-team-card') : [];
				var visible = 0;
				cards.forEach(function (c) {
					var feldOfCard = (c.querySelector('.esc-team-card__feld') || {}).textContent || '';
					// Map Label → slug
					var map = { 'Recht': 'rechtsberatung', 'Steuern': 'steuerberatung', 'Unternehmensberatung': 'unternehmensberatung', 'Büroleitung': 'management' };
					var slugOfCard = map[feldOfCard.trim()] || '';
					var show = !feld || slugOfCard === feld;
					c.style.transition = 'opacity 240ms, transform 240ms';
					if (show) {
						c.style.display = '';
						requestAnimationFrame(function () { c.style.opacity = '1'; c.style.transform = 'translateY(0)'; });
						visible++;
					} else {
						c.style.opacity = '0'; c.style.transform = 'translateY(8px)';
						setTimeout(function () { c.style.display = 'none'; }, 240);
					}
				});
				if (countEl) { countEl.textContent = visible + ' Teammitglieder'; }
			});
		});
	}

	// 4. Smooth anchor scrolling — respects reduced motion
	var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (!prefersReduced) {
		document.addEventListener('click', function (e) {
			var a = e.target.closest('a[href^="#"]');
			if (!a) return;
			var href = a.getAttribute('href');
			if (!href || href === '#') return;
			var target = document.querySelector(href);
			if (!target) return;
			e.preventDefault();
			target.scrollIntoView({ behavior: 'smooth', block: 'start' });
		});
	}
})();

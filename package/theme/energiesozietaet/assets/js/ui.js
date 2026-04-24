/*!
 * Energiesozietät UI — minimal JS for nav toggle + scroll reveal + header condense.
 */
(function () {
	'use strict';

	// 0. Mark html as JS-enabled so reveal CSS becomes active
	document.documentElement.classList.add('js');

	// 1. Mobile nav toggle
	var toggle = document.querySelector('.es-nav-toggle');
	var nav = document.getElementById('es-nav');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.body.style.overflow = open ? 'hidden' : '';
		});
		// Close when a link is clicked (on mobile)
		nav.addEventListener('click', function (e) {
			if (window.innerWidth > 1024) return;
			var a = e.target.closest('a');
			if (a && !a.closest('.sub-menu')) {
				nav.classList.remove('is-open');
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

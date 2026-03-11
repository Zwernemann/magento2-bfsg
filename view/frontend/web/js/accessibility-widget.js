/**
 * BFSG Accessibility Widget
 *
 * Provides a locally hosted, GDPR-compliant frontend overlay for:
 * - High Contrast mode (WCAG 1.4.3)
 * - Font size scaling up to 200% without horizontal scroll (WCAG 1.4.4)
 * - Dyslexia-friendly font (OpenDyslexic fallback via CSS class)
 * - Session timeout warning (WCAG 2.2.1)
 * - Runtime ARIA label injection from admin-defined mappings (WCAG 4.1.2)
 *
 * Settings are persisted in localStorage — no server calls, no cookies.
 */
define([
    'jquery',
    'Zwernemann_BFSG/js/focus-manager',
    'Zwernemann_BFSG/js/session-timeout'
], function ($, focusManager, sessionTimeout) {
    'use strict';

    var STORAGE_KEY = 'bfsg_prefs';

    return function (config) {
        var $widget  = $('#bfsg-widget');
        var $toggle  = $('#bfsg-toggle');
        var $panel   = $('#bfsg-panel');
        var $close   = $panel.find('.bfsg-widget__close');

        if (!$widget.length) {
            return;
        }

        // ── Preferences ──────────────────────────────────────────────────────
        var prefs = loadPrefs();
        applyAllPrefs(prefs);

        function loadPrefs() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
            } catch (e) {
                return {};
            }
        }

        function savePrefs() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
            } catch (e) { /* storage not available */ }
        }

        function applyAllPrefs(p) {
            applyContrast(p.contrast);
            applyFontSize(p.fontSize || 100);
            applyDyslexia(p.dyslexia);
        }

        // ── High Contrast (WCAG 1.4.3) ───────────────────────────────────────
        function applyContrast(active) {
            $('body').toggleClass('bfsg-high-contrast', !!active);
            $('#bfsg-contrast-btn').attr('aria-pressed', active ? 'true' : 'false');
        }

        // ── Font Size (WCAG 1.4.4) ───────────────────────────────────────────
        var FONT_STEP = 10;
        var FONT_MIN  = 80;
        var FONT_MAX  = 160;

        function applyFontSize(pct) {
            pct = Math.min(FONT_MAX, Math.max(FONT_MIN, pct));
            document.documentElement.style.fontSize = pct + '%';
            prefs.fontSize = pct;
        }

        // ── Dyslexia Font ────────────────────────────────────────────────────
        function applyDyslexia(active) {
            $('body').toggleClass('bfsg-dyslexia-font', !!active);
            $('#bfsg-dyslexia-btn').attr('aria-pressed', active ? 'true' : 'false');
        }

        // ── Panel open / close ───────────────────────────────────────────────
        function openPanel() {
            $panel.removeAttr('hidden');
            $toggle.attr('aria-expanded', 'true');
            $toggle.attr('aria-label', $.mage.__('Close Accessibility Options'));
            focusManager.trapFocus($panel[0]);
            $close.trigger('focus');
        }

        function closePanel() {
            $panel.attr('hidden', '');
            $toggle.attr('aria-expanded', 'false');
            $toggle.attr('aria-label', $.mage.__('Open Accessibility Options'));
            focusManager.releaseFocus();
            $toggle.trigger('focus');
        }

        $toggle.on('click', function () {
            if ($panel.attr('hidden') === undefined) {
                closePanel();
            } else {
                openPanel();
            }
        });

        $close.on('click', closePanel);

        // Close on Escape
        $(document).on('keydown.bfsg-panel', function (e) {
            if (e.key === 'Escape' && $panel.attr('hidden') === undefined) {
                closePanel();
            }
        });

        // ── Action buttons ───────────────────────────────────────────────────
        $panel.on('click', '[data-bfsg-action]', function () {
            var action = $(this).data('bfsg-action');

            switch (action) {
                case 'contrast':
                    prefs.contrast = !prefs.contrast;
                    applyContrast(prefs.contrast);
                    announceChange(prefs.contrast
                        ? $.mage.__('High contrast mode enabled')
                        : $.mage.__('High contrast mode disabled'));
                    break;

                case 'font-increase':
                    prefs.fontSize = (prefs.fontSize || 100) + FONT_STEP;
                    applyFontSize(prefs.fontSize);
                    announceChange($.mage.__('Font size increased'));
                    break;

                case 'font-decrease':
                    prefs.fontSize = (prefs.fontSize || 100) - FONT_STEP;
                    applyFontSize(prefs.fontSize);
                    announceChange($.mage.__('Font size decreased'));
                    break;

                case 'font-reset':
                    prefs.fontSize = 100;
                    applyFontSize(100);
                    announceChange($.mage.__('Font size reset'));
                    break;

                case 'dyslexia':
                    prefs.dyslexia = !prefs.dyslexia;
                    applyDyslexia(prefs.dyslexia);
                    announceChange(prefs.dyslexia
                        ? $.mage.__('Dyslexia-friendly font enabled')
                        : $.mage.__('Dyslexia-friendly font disabled'));
                    break;

                case 'reset-all':
                    prefs = {};
                    applyContrast(false);
                    applyFontSize(100);
                    applyDyslexia(false);
                    announceChange($.mage.__('All accessibility settings reset'));
                    break;
            }

            savePrefs();
        });

        // ── Polite screen-reader announcement ────────────────────────────────
        function announceChange(message) {
            var $live = $('#bfsg-aria-live-polite');
            $live.text('');
            // Brief timeout to ensure re-announcement works for repeated messages
            setTimeout(function () { $live.text(message); }, 50);
        }

        // ── Runtime ARIA label injection (WCAG 4.1.2) ────────────────────────
        if (Array.isArray(config.ariaLabels)) {
            config.ariaLabels.forEach(function (entry) {
                if (!entry.selector) { return; }
                var $els = $(entry.selector);
                if ($els.length === 0) { return; }
                if (entry.ariaLabel) {
                    $els.each(function () {
                        if (!$(this).attr('aria-label')) {
                            $(this).attr('aria-label', entry.ariaLabel);
                        }
                    });
                }
                if (entry.ariaDescribedby) {
                    $els.each(function () {
                        if (!$(this).attr('aria-describedby')) {
                            $(this).attr('aria-describedby', entry.ariaDescribedby);
                        }
                    });
                }
            });
        }

        // ── Session timeout warning (WCAG 2.2.1) ─────────────────────────────
        if (config.sessionWarning && config.sessionWarning > 0) {
            sessionTimeout.init({
                warningBeforeExpiry: config.sessionWarning,
                sessionLifetime:     config.sessionLifetime
            });
        }
    };
});

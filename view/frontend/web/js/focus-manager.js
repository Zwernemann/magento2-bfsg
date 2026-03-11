/**
 * BFSG Focus Manager
 *
 * Implements focus trapping for modals/overlays (WCAG 2.1.1 / 2.4.3).
 *
 * When a dialog or overlay opens, Tab and Shift+Tab are constrained
 * to focusable elements within the container, so keyboard users
 * cannot accidentally navigate behind the overlay.
 *
 * Usage:
 *   focusManager.trapFocus(containerElement);
 *   focusManager.releaseFocus();
 */
define(['jquery'], function ($) {
    'use strict';

    var FOCUSABLE_SELECTORS = [
        'a[href]',
        'area[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
        'details > summary'
    ].join(', ');

    var _activeContainer = null;
    var _handleKeydown   = null;

    function getFocusable(container) {
        return $(container).find(FOCUSABLE_SELECTORS).filter(':visible');
    }

    /**
     * Trap Tab focus within `container`.
     *
     * @param {HTMLElement} container
     */
    function trapFocus(container) {
        releaseFocus();

        _activeContainer = container;

        _handleKeydown = function (e) {
            if (e.key !== 'Tab') { return; }

            var $focusable = getFocusable(container);
            if ($focusable.length === 0) { return; }

            var $first = $focusable.first();
            var $last  = $focusable.last();

            if (e.shiftKey) {
                // Shift+Tab: if focus is on first element, wrap to last
                if (document.activeElement === $first[0]) {
                    e.preventDefault();
                    $last.trigger('focus');
                }
            } else {
                // Tab: if focus is on last element, wrap to first
                if (document.activeElement === $last[0]) {
                    e.preventDefault();
                    $first.trigger('focus');
                }
            }
        };

        document.addEventListener('keydown', _handleKeydown, true);
    }

    /**
     * Release the active focus trap.
     */
    function releaseFocus() {
        if (_handleKeydown) {
            document.removeEventListener('keydown', _handleKeydown, true);
            _handleKeydown   = null;
            _activeContainer = null;
        }
    }

    /**
     * Ensure focus-visible outline is always shown for keyboard navigation.
     * Adds a CSS class on body when the user is navigating by keyboard,
     * and removes it when they switch to mouse.
     *
     * This works together with the CSS `.bfsg-keyboard-nav :focus` selector.
     */
    function initFocusVisibility() {
        var usingKeyboard = false;

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Tab' || e.key === 'Enter' || e.key === ' ') {
                if (!usingKeyboard) {
                    usingKeyboard = true;
                    document.body.classList.add('bfsg-keyboard-nav');
                }
            }
        }, true);

        document.addEventListener('mousedown', function () {
            if (usingKeyboard) {
                usingKeyboard = false;
                document.body.classList.remove('bfsg-keyboard-nav');
            }
        }, true);
    }

    // Auto-initialise focus visibility as soon as the module loads
    initFocusVisibility();

    return {
        trapFocus:    trapFocus,
        releaseFocus: releaseFocus
    };
});

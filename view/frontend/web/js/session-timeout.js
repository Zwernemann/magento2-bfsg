/**
 * BFSG Session Timeout Warning (WCAG 2.2.1)
 *
 * Displays an accessible warning dialog before the Magento session expires,
 * giving the user the opportunity to extend their session via keyboard or
 * pointer input.
 *
 * Requirements addressed:
 * - WCAG 2.2.1: Timing Adjustable – users can extend or dismiss the timeout
 * - WCAG 2.4.3: Focus Order – focus is moved to the dialog and returned on close
 * - WCAG 4.1.3: Status Messages – dialog uses role="alertdialog"
 *
 * Configuration:
 *   warningBeforeExpiry {number} Seconds before session end to show warning.
 *                                Defaults to 120 (2 minutes).
 */
define(['jquery', 'Zwernemann_BFSG/js/focus-manager'], function ($, focusManager) {
    'use strict';

    var DEFAULT_WARNING_SECONDS = 120;

    /**
     * Retrieve the Magento customer session lifetime (seconds) from the
     * backend-provided configuration. The `mage-cache-timeout` cookie holds
     * the *page-cache* expiry, not the customer session lifetime, so it must
     * not be used here.
     *
     * @param {number} configuredLifetime  Session lifetime in seconds as
     *                                     provided by the backend block config.
     */
    function getSessionLifetime(configuredLifetime) {
        return configuredLifetime > 0 ? configuredLifetime : 3600;
    }

    /**
     * @param {object} config
     * @param {number} config.warningBeforeExpiry  Seconds before expiry to warn
     * @param {number} config.sessionLifetime      Session lifetime in seconds
     *                                             (from Magento backend config)
     */
    function init(config) {
        var warnBefore       = config.warningBeforeExpiry || DEFAULT_WARNING_SECONDS;
        var sessionLifetime  = parseInt(config.sessionLifetime, 10) || 0;
        var $dialog    = $('#bfsg-session-warning');
        var $extend    = $('#bfsg-session-extend');
        var $logout    = $('#bfsg-session-logout');

        if (!$dialog.length) {
            return;
        }

        var _warningTimer  = null;
        var _expireTimer   = null;
        var _lastActivity  = Date.now();
        var _triggerUrl    = window.BASE_URL + 'customer/account/'; // ping endpoint
        var _logoutUrl     = window.BASE_URL + 'customer/account/logout/';

        // Track last user interaction time
        $(document).on('mousemove.bfsg-session keydown.bfsg-session scroll.bfsg-session', function () {
            _lastActivity = Date.now();
        });

        function scheduleWarning(lifetimeSeconds) {
            clearTimeout(_warningTimer);
            clearTimeout(_expireTimer);

            // If the lifetime is not greater than the warning window the dialog
            // would fire immediately (warningDelay = 0), which is pointless.
            if (lifetimeSeconds <= warnBefore) {
                return;
            }

            var warningDelay = Math.max(0, (lifetimeSeconds - warnBefore) * 1000);
            var expireDelay  = lifetimeSeconds * 1000;

            _warningTimer = setTimeout(showWarning, warningDelay);
            _expireTimer  = setTimeout(onSessionExpired, expireDelay);
        }

        function showWarning() {
            // Use setProperty with 'important' priority so no theme !important rule
            // can keep the overlay visible when it should be hidden.
            $dialog[0].style.setProperty('display', 'flex', 'important');
            $dialog.removeAttr('hidden');
            focusManager.trapFocus($dialog[0]);
            $extend.trigger('focus');

            // Announce to screen reader
            $('#bfsg-aria-live-assertive').text(
                $.mage.__('Warning: Your session is about to expire. Please choose to stay logged in or log out.')
            );
        }

        function hideWarning() {
            // style.setProperty with 'important' beats any CSS rule including
            // theme !important declarations that would otherwise keep the overlay on screen.
            $dialog[0].style.setProperty('display', 'none', 'important');
            $dialog.attr('hidden', '');
            focusManager.releaseFocus();
        }

        function onSessionExpired() {
            hideWarning();
            window.location.href = _logoutUrl;
        }

        $extend.on('click', function () {
            hideWarning();

            // Ping a lightweight authenticated endpoint to reset the session,
            // then reschedule using the known configured lifetime – NOT the
            // page-cache cookie which would cause an immediate re-trigger.
            $.get(_triggerUrl).always(function () {
                scheduleWarning(getSessionLifetime(sessionLifetime));
            });

            $('#bfsg-aria-live-polite').text(
                $.mage.__('Your session has been extended.')
            );
        });

        $logout.on('click', function () {
            window.location.href = _logoutUrl;
        });

        // Close on Escape key
        $(document).on('keydown.bfsg-session-dialog', function (e) {
            if (e.key === 'Escape' && $dialog.attr('hidden') === undefined) {
                $extend.trigger('click');
            }
        });

        // Bootstrap the timer
        scheduleWarning(getSessionLifetime(sessionLifetime));
    }

    return { init: init };
});

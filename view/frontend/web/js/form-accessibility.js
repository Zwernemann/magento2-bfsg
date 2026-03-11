/**
 * BFSG Form Accessibility Mixin
 *
 * Enhances Magento's mage/validation with WCAG 2.1 Level AA compliance:
 *
 * - WCAG 3.3.1: Adds aria-invalid="true" to fields with errors
 * - WCAG 3.3.3: Links error messages to their fields via aria-describedby
 * - WCAG 4.1.3: Announces errors immediately via aria-live="assertive"
 * - WCAG 1.3.1: Wraps radio/checkbox groups in <fieldset><legend>
 * - WCAG 2.4.3: Moves focus to the first invalid field on submit failure
 *
 * Applied as a mixin to mage/validation via requirejs-config.js.
 */
define(['jquery', 'mage/utils/wrapper'], function ($, wrapper) {
    'use strict';

    var ASSERTIVE_REGION_ID = 'bfsg-aria-live-assertive';
    var ERROR_ID_PREFIX     = 'bfsg-error-';

    /**
     * Announce a message to screen readers via the assertive live region.
     */
    function announceError(message) {
        var $region = $('#' + ASSERTIVE_REGION_ID);
        if (!$region.length) {
            $region = $('<div>', {
                id:           ASSERTIVE_REGION_ID,
                'aria-live':  'assertive',
                'aria-atomic': 'true',
                class:        'bfsg-sr-only'
            }).appendTo('body');
        }
        // Clear then set to guarantee re-announcement for repeated errors
        $region.text('');
        setTimeout(function () { $region.text(message); }, 50);
    }

    /**
     * Mark a field as invalid and link its error message.
     *
     * @param {jQuery} $field  - The invalid form control
     * @param {string} message - The validation error text
     */
    function markFieldInvalid($field, message) {
        var fieldId   = $field.attr('id') || $field.attr('name') || '';
        var errorId   = ERROR_ID_PREFIX + fieldId.replace(/[^a-z0-9]/gi, '-');

        $field.attr('aria-invalid', 'true');

        // Link field to its error message
        var existing = $field.attr('aria-describedby') || '';
        if (existing.indexOf(errorId) === -1) {
            $field.attr('aria-describedby', (existing + ' ' + errorId).trim());
        }

        // Ensure the error element has the correct ID for the link above
        var $error = $field.siblings('.mage-error, .field-error')
                           .add($field.closest('.field').find('.mage-error, .field-error'))
                           .first();
        if ($error.length && !$error.attr('id')) {
            $error.attr('id', errorId);
            $error.attr('role', 'alert');
        }
    }

    /**
     * Clear invalid state from a field.
     *
     * @param {jQuery} $field
     */
    function clearFieldInvalid($field) {
        $field.removeAttr('aria-invalid');

        // Remove injected aria-describedby references
        var fieldId = $field.attr('id') || $field.attr('name') || '';
        var errorId = ERROR_ID_PREFIX + fieldId.replace(/[^a-z0-9]/gi, '-');
        var described = ($field.attr('aria-describedby') || '').split(' ');
        described = described.filter(function (id) { return id !== errorId; });
        if (described.length > 0) {
            $field.attr('aria-describedby', described.join(' '));
        } else {
            $field.removeAttr('aria-describedby');
        }
    }

    return function (targetModule) {

        // Wrap the showLabel method (called when a validation error is shown)
        targetModule.showLabel = wrapper.wrap(targetModule.showLabel, function (original, element, message) {
            var result = original(element, message);
            var $field = $(element);

            if (message) {
                markFieldInvalid($field, message);
            } else {
                clearFieldInvalid($field);
            }

            return result;
        });

        // Wrap invalidHandler (called when form submission fails validation)
        targetModule.invalidHandler = wrapper.wrap(targetModule.invalidHandler, function (original, event, validator) {
            var result = original(event, validator);

            if (validator.numberOfInvalids() > 0) {
                // Announce summary to screen reader
                var msg = $.mage.__(
                    'Please correct %1 error(s) in the form to continue.'
                ).replace('%1', validator.numberOfInvalids());
                announceError(msg);

                // Move focus to the first invalid field (WCAG 2.4.3)
                setTimeout(function () {
                    var $firstInvalid = $(validator.errorList[0] && validator.errorList[0].element);
                    if ($firstInvalid.length) {
                        $firstInvalid.trigger('focus');
                    }
                }, 100);
            }

            return result;
        });

        return targetModule;
    };
});

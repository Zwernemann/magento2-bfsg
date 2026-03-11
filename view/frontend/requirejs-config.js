var config = {
    map: {
        '*': {
            'Zwernemann_BFSG/js/accessibility-widget': 'Zwernemann_BFSG/js/accessibility-widget',
            'Zwernemann_BFSG/js/focus-manager':        'Zwernemann_BFSG/js/focus-manager',
            'Zwernemann_BFSG/js/form-accessibility':   'Zwernemann_BFSG/js/form-accessibility',
            'Zwernemann_BFSG/js/session-timeout':      'Zwernemann_BFSG/js/session-timeout'
        }
    },
    config: {
        mixins: {
            // Mixin Magento's UI validation to add ARIA attributes on error
            'mage/validation': {
                'Zwernemann_BFSG/js/form-accessibility': true
            }
        }
    }
};

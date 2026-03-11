<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides typed access to all BFSG module configuration values.
 */
class Config
{
    private const XML_PATH_ENABLED          = 'bfsg/general/enabled';
    private const XML_PATH_WIDGET_ENABLED   = 'bfsg/widget/enabled';
    private const XML_PATH_WIDGET_POSITION  = 'bfsg/widget/position';
    private const XML_PATH_WIDGET_CONTRAST  = 'bfsg/widget/show_contrast';
    private const XML_PATH_WIDGET_FONT_SIZE = 'bfsg/widget/show_font_size';
    private const XML_PATH_WIDGET_DYSLEXIA  = 'bfsg/widget/show_dyslexia';
    private const XML_PATH_SESSION_WARNING  = 'bfsg/session/timeout_warning';
    private const XML_PATH_SESSION_LIFETIME = 'bfsg/session/session_lifetime';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function isEnabled(?string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isWidgetEnabled(?string $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_WIDGET_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function getWidgetPosition(?string $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_WIDGET_POSITION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function showContrastToggle(?string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_WIDGET_CONTRAST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function showFontSizeToggle(?string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_WIDGET_FONT_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function showDyslexiaToggle(?string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_WIDGET_DYSLEXIA,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSessionTimeoutWarning(?string $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_SESSION_WARNING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSessionLifetime(?string $storeId = null): int
    {
        $seconds = (int) $this->scopeConfig->getValue(
            self::XML_PATH_SESSION_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $seconds > 0 ? $seconds : 3600;
    }
}

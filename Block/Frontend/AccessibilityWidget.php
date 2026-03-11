<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Block\Frontend;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Zwernemann\BFSG\Model\Config;
use Zwernemann\BFSG\Model\ResourceModel\AriaLabel\CollectionFactory;

class AccessibilityWidget extends Template
{
    protected $_template = 'Zwernemann_BFSG::widget.phtml';

    public function __construct(
        Context $context,
        private readonly Config            $config,
        private readonly CollectionFactory $ariaLabelCollectionFactory,
        private readonly Json              $json,
        private readonly HttpContext       $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isCustomerLoggedIn(): bool
    {
        return (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    public function isEnabled(): bool
    {
        return $this->config->isWidgetEnabled();
    }

    public function getWidgetPosition(): string
    {
        return $this->config->getWidgetPosition();
    }

    public function showContrastToggle(): bool
    {
        return $this->config->showContrastToggle();
    }

    public function showFontSizeToggle(): bool
    {
        return $this->config->showFontSizeToggle();
    }

    public function showDyslexiaToggle(): bool
    {
        return $this->config->showDyslexiaToggle();
    }

    public function getSessionTimeoutWarning(): int
    {
        return $this->config->getSessionTimeoutWarning();
    }

    public function getSessionLifetime(): int
    {
        return $this->config->getSessionLifetime();
    }

    /**
     * Returns active ARIA label definitions as a JSON string for use in JS.
     */
    public function getAriaLabelsJson(): string
    {
        $collection = $this->ariaLabelCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter(
            'store_id',
            ['in' => [0, (int) $this->_storeManager->getStore()->getId()]]
        );

        $labels = [];
        foreach ($collection as $item) {
            $entry = ['selector' => $item->getElementSelector()];
            if ($item->getAriaLabel()) {
                $entry['ariaLabel'] = $item->getAriaLabel();
            }
            if ($item->getAriaDescribedby()) {
                $entry['ariaDescribedby'] = $item->getAriaDescribedby();
            }
            $labels[] = $entry;
        }

        return $this->json->serialize($labels);
    }

    public function getWidgetConfig(): string
    {
        return $this->json->serialize([
            'position'        => $this->getWidgetPosition(),
            'showContrast'    => $this->showContrastToggle(),
            'showFontSize'    => $this->showFontSizeToggle(),
            'showDyslexia'    => $this->showDyslexiaToggle(),
            'sessionWarning'  => $this->getSessionTimeoutWarning(),
            'sessionLifetime' => $this->getSessionLifetime(),
            'ariaLabels'      => $this->json->unserialize($this->getAriaLabelsJson()),
        ]);
    }
}

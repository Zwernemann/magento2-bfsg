<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Block\Adminhtml\Check;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Index extends Template
{
    protected $_template = 'Zwernemann_BFSG::check/index.phtml';

    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getRunUrl(): string
    {
        return $this->getUrl('bfsg/check/run');
    }

    public function getRunCmsUrl(): string
    {
        return $this->getUrl('bfsg/check/run', ['type' => 'cms']);
    }

    public function getRunProductsUrl(): string
    {
        return $this->getUrl('bfsg/check/run', ['type' => 'products']);
    }
}

<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Block\Adminhtml\AriaLabel\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Zwernemann\BFSG\Model\AriaLabelFactory;
use Zwernemann\BFSG\Model\ResourceModel\AriaLabel as AriaLabelResource;

class Form extends Template
{
    protected $_template = 'Zwernemann_BFSG::arialabel/form.phtml';

    public function __construct(
        Context $context,
        private readonly AriaLabelFactory  $ariaLabelFactory,
        private readonly AriaLabelResource $ariaLabelResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getAriaLabel(): \Zwernemann\BFSG\Model\AriaLabel
    {
        $id    = (int) $this->getRequest()->getParam('entity_id');
        $model = $this->ariaLabelFactory->create();

        if ($id) {
            $this->ariaLabelResource->load($model, $id);
        }

        return $model;
    }

    public function getSaveUrl(): string
    {
        return $this->getUrl('bfsg/arialabel/save');
    }

    public function getBackUrl(): string
    {
        return $this->getUrl('bfsg/arialabel/index');
    }

    public function getElementTypes(): array
    {
        return [
            'button'   => 'Button',
            'input'    => 'Input Field',
            'link'     => 'Link',
            'select'   => 'Select / Dropdown',
            'checkbox' => 'Checkbox',
            'radio'    => 'Radio Button',
            'icon'     => 'Icon / SVG',
            'other'    => 'Other',
        ];
    }
}

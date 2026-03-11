<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Block\Adminhtml\AriaLabel;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Zwernemann\BFSG\Model\ResourceModel\AriaLabel\CollectionFactory;

class Grid extends Template
{
    protected $_template = 'Zwernemann_BFSG::arialabel/grid.phtml';

    public function __construct(
        Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCollection(): \Zwernemann\BFSG\Model\ResourceModel\AriaLabel\Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder('entity_id', 'DESC');
        return $collection;
    }

    public function getNewUrl(): string
    {
        return $this->getUrl('bfsg/arialabel/new');
    }

    public function getEditUrl(int $id): string
    {
        return $this->getUrl('bfsg/arialabel/edit', ['entity_id' => $id]);
    }

    public function getDeleteUrl(int $id): string
    {
        return $this->getUrl('bfsg/arialabel/delete', ['entity_id' => $id]);
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

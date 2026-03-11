<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Model\ResourceModel\AriaLabel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Zwernemann\BFSG\Model\AriaLabel;
use Zwernemann\BFSG\Model\ResourceModel\AriaLabel as AriaLabelResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(AriaLabel::class, AriaLabelResource::class);
    }
}

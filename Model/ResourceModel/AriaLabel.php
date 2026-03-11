<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AriaLabel extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('bfsg_aria_label', 'entity_id');
    }
}

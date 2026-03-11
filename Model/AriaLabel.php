<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * ARIA Label model - stores ARIA label mappings for frontend elements.
 */
class AriaLabel extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(ResourceModel\AriaLabel::class);
    }
}

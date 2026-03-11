<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Position implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'bottom-right', 'label' => __('Bottom Right')],
            ['value' => 'bottom-left',  'label' => __('Bottom Left')],
            ['value' => 'top-right',    'label' => __('Top Right')],
            ['value' => 'top-left',     'label' => __('Top Left')],
        ];
    }
}

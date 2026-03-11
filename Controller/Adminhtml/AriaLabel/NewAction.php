<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Controller\Adminhtml\AriaLabel;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends Action
{
    public const ADMIN_RESOURCE = 'Zwernemann_BFSG::arialabel';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('New ARIA Label'));

        return $resultPage;
    }
}

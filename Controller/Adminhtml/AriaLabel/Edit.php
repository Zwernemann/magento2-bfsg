<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Controller\Adminhtml\AriaLabel;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Zwernemann\BFSG\Model\AriaLabelFactory;

class Edit extends Action
{
    public const ADMIN_RESOURCE = 'Zwernemann_BFSG::arialabel';

    public function __construct(
        Context $context,
        private readonly PageFactory      $resultPageFactory,
        private readonly AriaLabelFactory $ariaLabelFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id    = (int) $this->getRequest()->getParam('entity_id');
        $model = $this->ariaLabelFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This ARIA label no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit ARIA Label') : __('New ARIA Label')
        );

        return $resultPage;
    }
}

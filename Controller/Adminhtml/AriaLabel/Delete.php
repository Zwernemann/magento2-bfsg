<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Controller\Adminhtml\AriaLabel;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Zwernemann\BFSG\Model\AriaLabelFactory;
use Zwernemann\BFSG\Model\ResourceModel\AriaLabel as AriaLabelResource;

class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Zwernemann_BFSG::arialabel';

    public function __construct(
        Context $context,
        private readonly AriaLabelFactory  $ariaLabelFactory,
        private readonly AriaLabelResource $ariaLabelResource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id             = (int) $this->getRequest()->getParam('entity_id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid ARIA label ID.'));
            return $resultRedirect->setPath('*/*/');
        }

        $model = $this->ariaLabelFactory->create();
        $this->ariaLabelResource->load($model, $id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This ARIA label no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->ariaLabelResource->delete($model);
            $this->messageManager->addSuccessMessage(__('ARIA label has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}

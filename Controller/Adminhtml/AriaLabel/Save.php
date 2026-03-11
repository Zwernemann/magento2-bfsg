<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Controller\Adminhtml\AriaLabel;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Zwernemann\BFSG\Model\AriaLabelFactory;
use Zwernemann\BFSG\Model\ResourceModel\AriaLabel as AriaLabelResource;

class Save extends Action
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
        $data           = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id    = isset($data['entity_id']) ? (int) $data['entity_id'] : null;
        $model = $this->ariaLabelFactory->create();

        if ($id) {
            $this->ariaLabelResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This ARIA label no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $model->setData([
            'entity_id'       => $id,
            'element_selector' => $data['element_selector'] ?? '',
            'element_type'    => $data['element_type'] ?? '',
            'aria_label'      => $data['aria_label'] ?? null,
            'aria_describedby' => $data['aria_describedby'] ?? null,
            'store_id'        => (int) ($data['store_id'] ?? 0),
            'is_active'       => (int) ($data['is_active'] ?? 1),
        ]);

        try {
            $this->ariaLabelResource->save($model);
            $this->messageManager->addSuccessMessage(__('ARIA label has been saved.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath(
                '*/*/edit',
                ['entity_id' => $this->getRequest()->getParam('entity_id')]
            );
        }
    }
}

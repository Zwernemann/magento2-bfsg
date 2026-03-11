<?php
declare(strict_types=1);

namespace Zwernemann\BFSG\Controller\Adminhtml\Check;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Zwernemann\BFSG\Model\Checker\ContentChecker;

/**
 * AJAX endpoint that runs the accessibility scan and returns JSON results.
 */
class Run extends Action
{
    public const ADMIN_RESOURCE = 'Zwernemann_BFSG::check';

    public function __construct(
        Context $context,
        private readonly JsonFactory    $resultJsonFactory,
        private readonly ContentChecker $checker
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $type = $this->getRequest()->getParam('type', 'all');

            $data = match ($type) {
                'cms'      => ['cms_pages' => $this->checker->checkCmsPages(), 'products' => []],
                'products' => ['cms_pages' => [], 'products' => $this->checker->checkProducts()],
                default    => $this->checker->checkAll(),
            };

            return $result->setData(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

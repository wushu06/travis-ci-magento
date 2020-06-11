<?php

namespace Elementary\EmployeesManager\Controller\AbstractControllerPlugin;


use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;

class OrderLoader
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var OrderViewAuthorizationInterface
     */
    protected $orderAuthorization;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    public function __construct(
        RequestInterface $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderViewAuthorizationInterface $orderAuthorization,
        Registry $registry,
        \Magento\Framework\UrlInterface $url,
        ForwardFactory $resultForwardFactory,
        RedirectFactory $redirectFactory,
        PageFactory $resultPageFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderAuthorization = $orderAuthorization;
        $this->registry = $registry;
        $this->url = $url;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function aroundExecute(\Magento\Sales\Controller\AbstractController\View $subject, \Closure $proceed)
    {
        $orderId = (int)$this->request->getParam('order_id');

        if (!$orderId) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $order = $this->orderFactory->create()->load($orderId);
        $this->registry->register('current_order', $order);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        return $resultPage;
    }
}

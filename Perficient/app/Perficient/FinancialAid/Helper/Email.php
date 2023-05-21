<?php
namespace Perficient\FinancialAid\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class Email
 * @package Perficient\FinancialAid\Helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;
    protected $logger;
    /**
     * @var Data
     */
    protected $smtpHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Perficient\FinancialAid\Model\Config
     */
    protected $financialAidConfig;

    /**
     * Email constructor.
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param \Perficient\FinancialAid\Model\Config $financialAidConfig
     * @param Mageplaza\Smtp\Helper\Data $smtpHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        \Perficient\FinancialAid\Model\Config $financialAidConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->financialAidConfig = $financialAidConfig;
        $this->logger = $context->getLogger();
        $this->storeManager = $storeManager;
    }

    public function sendEmail($post)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $senderEmail = $this->financialAidConfig->getSender();
        $this->inlineTranslation->suspend();
        $productDescription = $post['description'];
        $Isbn = $post['isbn'];
        $price = $post['price'];
        $customerEmail = $post['email'];
        $customerFirstName = $post['fname'];


        try {

            $sender = [
                'name' => $this->escaper->escapeHtml('Vista Higher Learning Support'),
                'email' => $this->escaper->escapeHtml($senderEmail)
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('financial_aid_email')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' =>  $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'productname'  => $productDescription,
                    'isbn'  => $Isbn,
                    'price' => $price,
                ])
                ->setFromByScope($sender, $storeId)
                ->addTo($customerEmail, $customerFirstName)
                ->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}

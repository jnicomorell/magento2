<?php
namespace Formax\BenefitsCarousel\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Carousel extends Template {

    /**
     * @var string
     */
    private const BASE_PATH = 'benefits_carousel';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfigInterface;

    /**
     * @var string
     */
    private $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * @var array
     */
    protected $systemGroups = \Formax\BenefitsCarousel\Utils\Data::GROUP_LIST;

    /**
     * @var string
     */
    protected const IMAGE_UPLOAD_PATH = \Formax\BenefitsCarousel\Utils\Data::IMAGE_UPLOAD_PATH;

    /**
     * @var string
     */
    protected const ICON_UPLOAD_PATH = \Formax\BenefitsCarousel\Utils\Data::ICON_UPLOAD_PATH;

    /**
     * @var Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var string
     */
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Constructor
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        DataObjectFactory $objectFactory,
        DateTime $date,
        array $data = []
    ) {
        $this->_scopeConfigInterface = $context->getScopeConfig();
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->objectFactory = $objectFactory;
        $this->date = $date;
        parent::__construct($context, $data);
    }

    /**
     * Returns an array of blocks to generate the carousel
     * 
     * @return Magento\Framework\DataObjectFactory
     */
    public function getCarouselData() {
        $data = [];

        // Base config
        $config = $this->objectFactory->create();
        $config->setIsActive( $this->getConfig('general/active') );
        $config->setTitle( $this->getConfig('general/title') );
        $config->setShowButton( $this->getConfig('general/show_button') );
        $config->setButtonText( $this->getConfig('general/button_text') );
        $config->setButtonLink( $this->getConfig('general/button_link') );

        $data['config'] = $config;

        // Images block config
        foreach($this->systemGroups as $group) {
            $groupTag = $group . '/';
            $carouselBox = $this->objectFactory->create();
            $iconBg = $this->getConfig($groupTag . 'icon_bg');
            if( strpos($iconBg, '#') === false ){
                $iconBg = '#' . $iconBg;
            }
            $imageSrc = $this->getMediaUrl( $this->getConfig($groupTag . 'image'), self::IMAGE_UPLOAD_PATH );
            $iconSrc = $this->getMediaUrl( $this->getConfig($groupTag . 'icon'), self::ICON_UPLOAD_PATH );
            
            $carouselBox->setIsActive( $this->getConfig($groupTag . 'active') );
            $carouselBox->setImageSrc( $imageSrc );
            $carouselBox->setShowIcon( $this->getConfig($groupTag . 'show_icon') );
            $carouselBox->setIconSrc( $iconSrc );
            $carouselBox->setIconBackground( $iconBg );
            $carouselBox->setTitle( $this->getConfig($groupTag . 'title') );
            $carouselBox->setDescription( $this->getConfig($groupTag . 'description') );
            $carouselBox->setRibbon( $this->getConfig($groupTag . 'ribbon') );
            $carouselBox->setLink( $this->getConfig($groupTag . 'link') );
            //Dates
            $today = strtotime( $this->date->gmtDate(self::DATE_FORMAT) );
            $startDate = strtotime( $this->getConfig($groupTag . 'start_date') );
            $endDate = strtotime( $this->getConfig($groupTag . 'end_date') );
            $carouselBox->setIsInDate(false);

            if ($today >= $startDate && $today <= $endDate) {
                $carouselBox->setIsInDate(true);
            }
            $data['carousels'][] = $carouselBox;
        }
        return $data;
    }

    /**
     * Get full image path based on key
     *
     * @return string
     */
    private function getMediaUrl($imageName, $path) {
        $currentStore = $this->_storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $baseUrl . $path . '/' . $imageName;
    }

    /**
     * Get system admin config data by key
     * 
     * @param string
     * @return void
     */
    private function getConfig($key) {
        return $this->_scopeConfigInterface->getValue(self::BASE_PATH . '/' . $key, $this->scopeStore);
    }

    /**
     * Get the formatted system admin Ribbon text
     * 
     * @param string
     * @return string
     */
    public function getFormattedRibbon($ribbon) {
        if( !empty($ribbon) ){
            $ribbon = str_replace('|', '<br>', $ribbon);
            $ribbon = str_replace('[', '<strong>', $ribbon);
            $ribbon = str_replace(']', '</strong>', $ribbon);
        }
        return $ribbon;
    }

}
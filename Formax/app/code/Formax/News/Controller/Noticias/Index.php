<?php

namespace Formax\news\Controller\Noticias;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_resultJsonFactory;
    protected $_helper;
    protected $_logger;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
            \Psr\Log\LoggerInterface $logger,
            \Formax\News\Helper\Data $helper
    )
    {
        $this->_resultJsonFactory = $jsonResultFactory;
        $this->_logger = $logger;
        $this->_helper = $helper;
        return parent::__construct($context);
    }

    public function execute()
    {
        // Patrón para usar en expresiones regulares (admite letras acentuadas y espacios):
        $patron_url = "/^[a-zA-Z0-9\:\.\?\=\/\_\-\s]+$/";
        
        if ($this->getRequest()->isAjax())
        {
            try {
                // Obtenemos parametros enviados desde el formulario
                $post = $this->getRequest()->getPostValue();

                // Category
                if ( isset($post['category']) )
                {
                    if( empty($post['category']) )
                        return false;
                    else {
                        // Comprobar que sea numerico
                        if( is_numeric($post['category']) )
                            $category = $post['category'];
                        else
                            return false;
                    }
                } else {
                    return false;
                }

                // Articule URL
                if ( isset($post['articleUrl']) )
                {
                    if( empty($post['articleUrl']) )
                        return false;
                    else {
                        // Comprobar mediante una expresión regular:
                        if( preg_match($patron_url, $post['articleUrl']) )
                            $articleUrl = $post['articleUrl'];
                        else
                            return false;
                    }
                } else {
                    return false;
                }

                // Year
                if ( isset($post['year']) )
                {
                    if( empty($post['year']) )
                        return false;
                    else {
                        // Comprobar que sea numerico
                        if( is_numeric($post['year']) )
                            $year = $post['year'];
                        else
                            return false;
                    }
                } else {
                    return false;
                }

                // Page
                if ( isset($post['page']) )
                {
                    if( empty($post['page']) )
                        return false;
                    else {
                        // Comprobar que sea numerico
                        if( is_numeric($post['page']) )
                            $page = $post['page'];
                        else
                            return false;
                    }
                } else {
                    return false;
                }

                $data = $this->_helper->getAllArticleCollection($category, $year);

                $pageSize=6;
                $count=$data->getSize();
                $pages=ceil($count/$pageSize);
                if($page!=null && !empty($page)){
                    $page=intval(str_replace(' ', '', $page));
                    $page=$page>$pages?$pages:$page;
                    $data->setPageSize($pageSize)->setCurPage($page);
                }else{
                    $data->setPageSize($pageSize)->setCurPage(1);
                }

                $news = $data->getData();
                foreach ($news as $key => $article){
                    $news[$key]["image"]=$this->_helper->getArticleImage($article["image"]);
                    $news[$key]["articleUrl"]=$articleUrl.$article["id"];

                    $news[$key]["title"]=$this->_helper->limitTxt($article["title"], 75);
                    $news[$key]["short_content"]=$this->_helper->limitTxt($article["short_content"], 120);
                    $createdAt=$this->_helper->getformatMoth($article["created_at"]).' '.$this->_helper->getformatDay($article["created_at"]).' del '.$this->_helper->getformatYear($article["created_at"]);
                    $news[$key]["created_at_mob"]=$createdAt;
                    $createdAt=$createdAt.', '.$this->_helper->getformatTime($article["created_at"]);
                    $news[$key]["created_at"]=$createdAt;

                    $news[$key]["categories"]=$this->_helper->getCategoriesByArticle($article["id"])->getData();
                    
                    $news[$key]["class"]="n-".($key%3);
                }
                $news = (object) array('news' => $news, 'page' => $page, 'pageSize'=>$pageSize, 'totalPages'=>$pages, 'totalCount'=>$count);
                if ($news !== null) {
                    $resultJson = $this->_resultJsonFactory->create();
                    return $resultJson->setData($news);
                }
            } catch (\Exception $ex) {
                $this->_logger->error("Error in REST API Return : " . $ex->getMessage());
            }
        }
    }
}

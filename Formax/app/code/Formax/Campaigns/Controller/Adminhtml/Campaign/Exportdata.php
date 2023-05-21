<?php

namespace Formax\Campaigns\Controller\Adminhtml\Campaign;

use Magento\Framework\App\Filesystem\DirectoryList;


class Exportdata extends \Magento\Backend\App\Action
{
    protected $uploaderFactory;

    protected $_campaignFactory; 

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Formax\Campaigns\Model\CampaignFactory $campaignFactory // This is returns Collaction of Data

    ) {
       parent::__construct($context);
       $this->_fileFactory = $fileFactory;
       $this->_campaignFactory = $campaignFactory;
       $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
       parent::__construct($context);
    }

    public function execute()
    {   
        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        //column name dispay in your CSV 

        $columns = [     
        'id',
        'store_id',
        'status',
        'title',
        'description',
        'name',
        'rut',
        'amount',
        'sort_order',
        'type_id'
        ];

            foreach ($columns as $column) 
            {
                $header[] = $column; //storecolumn in Header array
            }

        $stream->writeCsv($header);

        $campaign = $this->_campaignFactory->create();
        $campaign_collection = $campaign->getCollection(); // get Collection of Table data 

        foreach($campaign_collection as $item){

            $itemData = [];

            // column name must same as in your Database Table 

            $itemData[] = $item->getData('id');
            $itemData[] = $item->getData('store_id');
            $itemData[] = $item->getData('status');
            $itemData[] = $item->getData('title');
            $itemData[] = $item->getData('description');
            $itemData[] = $item->getData('name');
            $itemData[] = $item->getData('rut');
            $itemData[] = $item->getData('amount');
            $itemData[] = $item->getData('sort_order');
            $itemData[] = $item->getData('type_id');

            $stream->writeCsv($itemData);

        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'campaign-import-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }


}
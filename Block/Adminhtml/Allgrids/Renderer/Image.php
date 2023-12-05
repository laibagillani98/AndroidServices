<?php
 
namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;
 
use Magento\Framework\DataObject;
 
class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }
 
    public function render(DataObject $row)
    {
        $baseurl =  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if ($row->getReturnImage()) {
            $rowData = $row->getData();
            $rowImg = $rowData['return_image'];
            $imgPath = json_decode($rowImg);
            if($imgPath){
            $imgPathIndex = $imgPath[0];
            $ImageUrl = $baseurl.'/returnimage/'.$imgPathIndex;
            return '<a href="'.$ImageUrl.'" target="_blank"><img src="'.$ImageUrl.'" alt="'.$imgPathIndex.'" width="75" height="75"></a> <button  class="display_all"  data-incId = ' . $rowImg . '> Display All </button>' ;
            }else{
            $ImageUrl = $baseurl.'/returnimage/'.$this->_getValue($row);
            return '<a href="'.$ImageUrl.'" target="_blank"><img src="'.$ImageUrl.'" alt="'.$this->_getValue($row).'" width="75" height="75"></a>';
           }
        }else {
            $ImageUrl = $baseurl.'/orderChecking/'.$this->_getValue($row);
            return '<a href="'.$ImageUrl.'" target="_blank"><img src="'.$ImageUrl.'" alt="'.$this->_getValue($row).'" width="75" height="75"></a>';
      }
    }
}
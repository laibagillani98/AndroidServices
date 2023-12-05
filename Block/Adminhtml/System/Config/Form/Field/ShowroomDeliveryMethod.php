<?php
namespace TM\AndroidServices\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use TM\Comet\Block\Adminhtml\Form\Field\GettingStoreColumn;
use TM\Comet\Block\Adminhtml\Form\Field\Users;
use TM\AndroidServices\Block\Adminhtml\System\Config\Form\Field\YesNo;
/**
 * Class ReceiverEmail
 */
class ShowroomDeliveryMethod extends AbstractFieldArray
{
    /**
     * @var Users
     */
    private $typeWorkstation;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    private $gettingStores;

    protected function _prepareToRender()
    {
      
        $this->addColumn('select_store', [
            'label' => __('Select Store'),
            'renderer' => $this->getStoresFromBlock()
        ]); 
         $this->addColumn('enable_pallex', [
            'label' => __('Enable Pallex'),
            'style' => 'width:100px',
            'renderer' => $this->EnableOption()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $gettingStores = $row->getShowroomUser();
        if ($gettingStores !== null) {
            $options['option_' . $this->EnableOption()->calcOptionHash($gettingStores)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
   
    /**
     * @return YesNo
     * @throws LocalizedException
     */
    private function EnableOption()
    {
        if (!$this->typeWorkstation) {
            $this->typeWorkstation = $this->getLayout()->createBlock(
                YesNo::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->typeWorkstation;
    }

    /**
     * @return GettingStoreColumn
     * @throws LocalizedException
     */
    private function getStoresFromBlock()
    {
        if (!$this->gettingStores) {
            $this->gettingStores = $this->getLayout()->createBlock(
                GettingStoreColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->gettingStores;
    }
}
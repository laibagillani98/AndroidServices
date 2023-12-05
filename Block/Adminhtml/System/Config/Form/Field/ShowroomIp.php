<?php


namespace TM\AndroidServices\Block\Adminhtml\System\Config\Form\Field;


use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use TM\AndroidServices\Block\Adminhtml\System\Config\Form\Field\GetStores;
/**
 * Class Ranges
 */
class ShowroomIp extends AbstractFieldArray
{
    /**
     * @var GetStores
     */
    private $GetStores;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('showroom', [
            'label' => __('Select Showroom'),
            'renderer' => $this->getStorelistRenderer()
        ]);
        $this->addColumn('ip_address', ['label' => __('IP Address'), 'class' => 'required-entry']);
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


        $row->setData('option_extra_attrs', $options);

        $tax = $row->getStoreAssociated();
        if ($tax !== null) {
            $options['option_' . $this->getStorelistRenderer()->calcOptionHash($tax)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
    /**
     * @return GetStores
     * @throws LocalizedException
     */
    private function getStorelistRenderer()
    {
        if (!$this->GetStores) {
            $this->GetStores = $this->getLayout()->createBlock(
                GetStores::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->GetStores;
    }

}
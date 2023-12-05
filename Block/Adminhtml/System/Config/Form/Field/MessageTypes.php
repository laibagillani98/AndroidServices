<?php
namespace TM\AndroidServices\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class MessageTypes extends AbstractFieldArray {

    /**
     * @var bool
     */
    protected $_addAfter = TRUE;

    /**
     * @var
     */
    protected $_addButtonLabel;

    /**
     * Construct
     */
    protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render the columns
     */
    protected function _prepareToRender() {
        $this->addColumn('messagetype', ['label' => __('Message Type')]);
        $this->_addAfter       = FALSE;
        $this->_addButtonLabel = __('Add');
    }
}
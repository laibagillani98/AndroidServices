<?php   
namespace TM\AndroidServices\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Printers extends AbstractFieldArray {

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
        $this->addColumn('workstation', ['label' => __('work station')]);
        $this->addColumn('ip_address', ['label' => __('ip address')]);
        $this->addColumn('port', ['label' => __('port')]);
        $this->_addAfter       = FALSE;
        $this->_addButtonLabel = __('Add');
    }
}
<?php   
namespace TM\AndroidServices\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use TM\Comet\Block\Adminhtml\Form\Field\Workstation;

class CardConfig extends AbstractFieldArray {

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

    /**
     * @var Workstation
     */
    private $typeWorkstation;
    protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render the columns
     */
    protected function _prepareToRender() {
        $this->addColumn('card_user', [
            'label' => __('User'),
            'style' => 'width:100px',
            'renderer' => $this->getWorkstation()
        ]);
        $this->addColumn('card_data', ['label' => __('Card Data')]);
        $this->_addAfter       = FALSE;
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
            $options['option_' . $this->getWorkstation()->calcOptionHash($gettingStores)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return Workstation
     * @throws LocalizedException
     */
    private function getWorkstation()
    {
        if (!$this->typeWorkstation) {
            $this->typeWorkstation = $this->getLayout()->createBlock(
                Workstation::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->typeWorkstation;
    }

}
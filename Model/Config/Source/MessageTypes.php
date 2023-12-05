<?php

namespace TM\AndroidServices\Model\Config\Source;

class MessageTypes implements \Magento\Framework\Option\ArrayInterface
{
    //Here you can __construct Model

    public function toOptionArray()
    {
        return [
            ['value' => 'Message', 'label' => __('Message')],
            ['value' => 'Announcement', 'label' => __('Announcement')]
        ];
    }
}
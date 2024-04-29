<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: Form.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Block\Adminhtml\Add;

use BronzeByte\Review\Block\Adminhtml\Field\Video;

class Form extends \Magento\Review\Block\Adminhtml\Add\Form
{
    /**
     * Prepare review form video
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->getForm();

        if ($form && $fieldset = $form->getElement('add_review_form')) {
            $fieldset->addField(
                Video::FIELD_ID,
                'note',
                [
                    'label' => __('Review Video'),
                    'required' => false,
                    'text' => $this->getLayout()->createBlock(
                        Video::class
                    )->toHtml()
                ]
            );

            $form->setEnctype('multipart/form-data');
        }

        return $this;
    }
}

<?php
/**
 * Author: Shem L. Macapobres
 *
 * @file: Form.php
 * @description: Product review video module and graphql
 */

namespace BronzeByte\Review\Block\Adminhtml\Edit;

use BronzeByte\Review\Block\Adminhtml\Field\Video;

class Form extends \Magento\Review\Block\Adminhtml\Edit\Form
{
    /**
     * Prepare review form video
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->getForm();

        if ($form && $fieldset = $form->getElement('review_details')) {
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

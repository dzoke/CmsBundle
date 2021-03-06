<?php

namespace Opifer\CmsBundle\ValueProvider;

use Opifer\EavBundle\ValueProvider\AbstractValueProvider;
use Opifer\EavBundle\ValueProvider\ValueProviderInterface;
use Symfony\Component\Form\FormBuilderInterface;

class AttachmentValueProvider extends AbstractValueProvider implements ValueProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', [
            'required' => (isset($options['attribute']->getParameters()['required'])) ? $options['attribute']->getParameters()['required'] : false,
            'label' => false
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntity()
    {
        return 'Opifer\CmsBundle\Entity\AttachmentValue';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Attachment';
    }
}

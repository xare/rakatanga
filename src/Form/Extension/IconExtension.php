<?php 
namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IconExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [TextType::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fa'] = $options['fa'] ?? '';
        $view->vars['icon_before'] = $options['icon_before'] ?? '';
        $view->vars['icon_after'] = $options['icon_after'] ?? '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'fa' => null,
            'icon_before' => null,
            'icon_after' => null
        ]);
    }
}
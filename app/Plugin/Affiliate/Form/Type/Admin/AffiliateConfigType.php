<?php

namespace Plugin\Affiliate\Form\Type\Admin;

use Plugin\Affiliate\Entity\AffiliateConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * アフィリエイト設定フォーム（管理）。
 */
class AffiliateConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commission_rate', NumberType::class, [
                'label' => '報酬料率（%）',
                'scale' => 2,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0, 'max' => 100]),
                ],
            ])
            ->add('cookie_lifetime_days', IntegerType::class, [
                'label' => 'クッキー有効期間（日）',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 1, 'max' => 365]),
                ],
            ])
            ->add('confirm_after_days', IntegerType::class, [
                'label' => '成果確定までの猶予日数（日）',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0, 'max' => 365]),
                ],
            ])
            ->add('min_payout_amount', IntegerType::class, [
                'label' => '最低支払額（円）',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AffiliateConfig::class,
        ]);
    }
}

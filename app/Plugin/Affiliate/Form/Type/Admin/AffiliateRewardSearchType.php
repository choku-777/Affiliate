<?php

namespace Plugin\Affiliate\Form\Type\Admin;

use Plugin\Affiliate\Entity\AffiliateReward;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * 成果・報酬一覧の検索フォーム（管理）。
 */
class AffiliateRewardSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('affiliate_id', IntegerType::class, [
                'label' => 'アフィリエイターID',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'ステータス',
                'required' => false,
                'placeholder' => 'すべて',
                'choices' => [
                    '未確定' => AffiliateReward::STATUS_PENDING,
                    '確定' => AffiliateReward::STATUS_CONFIRMED,
                    '取消' => AffiliateReward::STATUS_CANCELLED,
                    '支払済' => AffiliateReward::STATUS_PAID,
                ],
            ])
            ->add('converted_date_start', DateType::class, [
                'label' => '発生日（開始）',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
            ])
            ->add('converted_date_end', DateType::class, [
                'label' => '発生日（終了）',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}

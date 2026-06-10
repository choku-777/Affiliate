<?php

namespace Plugin\Affiliate\Form\Type\Front;

use Plugin\Affiliate\Entity\Affiliate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * アフィリエイター登録フォーム（フロント）。
 */
class AffiliateEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'お名前',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'メールアドレス',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('bank_name', TextType::class, [
                'label' => '銀行名',
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 255])],
            ])
            ->add('bank_branch', TextType::class, [
                'label' => '支店名',
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 255])],
            ])
            ->add('account_type', ChoiceType::class, [
                'label' => '口座種別',
                'required' => false,
                'placeholder' => '選択してください',
                'choices' => [
                    '普通' => '普通',
                    '当座' => '当座',
                ],
            ])
            ->add('account_number', TextType::class, [
                'label' => '口座番号',
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 32])],
            ])
            ->add('account_holder', TextType::class, [
                'label' => '口座名義',
                'required' => false,
                'constraints' => [new Assert\Length(['max' => 255])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Affiliate::class,
        ]);
    }
}

<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Mailjet\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base Form Type for Mailjet Connectors Servers
 */
abstract class AbstractMailjetType extends AbstractType
{
    /**
     * Add Api Key Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addApiKeyField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Mailjet Api Key Option Authentification
            ->add('ApiKey', TextType::class, array(
                'label' => "var.apikey.label",
                //                'help_block' => "var.apikey.desc",
                'required' => true,
                'translation_domain' => "MailjetBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Secret Key Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return self
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addSecretKeyField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Mailjet Secret Key Option Authentification
            ->add('SecretKey', TextType::class, array(
                'label' => "var.secretkey.label",
                //                'help_block' => "var.secretkey.desc",
                'required' => true,
                'translation_domain' => "MailJetBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add List Selector Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     */
    public function addApiListField(FormBuilderInterface $builder, array $options)
    {
        //==============================================================================
        // Check Mailjet Lists are Available
        if (empty($options["data"]["ApiListsIndex"])) {
            return $this;
        }

        $builder
            //==============================================================================
            // Mailjet List Option Selector
            ->add('ApiList', ChoiceType::class, array(
                'label' => "var.list.label",
                //                'help_block' => "var.list.desc",
                'required' => true,
                'translation_domain' => "MailjetBundle",
                'choice_translation_domain' => false,
                'choices' => array_flip($options["data"]["ApiListsIndex"]),
            ))
        ;

        return $this;
    }
}

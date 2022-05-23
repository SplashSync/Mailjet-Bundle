<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Mailjet\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Mailjet Account Edit Form
 */
class EditFormType extends AbstractMailjetType
{
    /**
     * Build Mailjet Edit Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addApiKeyField($builder, $options);
        $this->addSecretKeyField($builder, $options);
        $this->addApiListField($builder, $options);
    }
}

<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle;

use HeimrichHannot\AdvancedMemberBundle\DependencyInjection\HeimrichHannotAdvancedMemberExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotAdvancedMemberBundle extends Bundle
{
    public function getPath()
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension()
    {
        return new HeimrichHannotAdvancedMemberExtension();
    }
}

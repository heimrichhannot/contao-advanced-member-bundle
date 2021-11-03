<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(HeimrichHannotAdvancedMemberExtension::ALIAS);

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('enable_member_alias')
                    ->defaultFalse()
                    ->info('Enable to add an alias field to member entity.')
                ->end()
                ->booleanNode('enable_additional_title_fields')
                    ->defaultFalse()
                    ->info('Enable to add additional title fields to member entity.')
                ->end()
                ->booleanNode('enable_additional_job_fields')
                    ->defaultFalse()
                    ->info('Enable to add additional job fields to member entity.')
                ->end()
                ->booleanNode('enable_image_fields')
                    ->defaultFalse()
                    ->info('Enable to add image fields to member entity.')
                ->end()
                ->booleanNode('enable_social_fields')
                    ->defaultFalse()
                    ->info('Enable to add social fields to member entity.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

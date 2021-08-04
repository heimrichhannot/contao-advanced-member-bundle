<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\EventListener\Contao;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\AdvancedMemberBundle\DataContainer\MemberContainer;

/**
 * @Hook("loadDataContainer", priority=1)
 */
class LoadDataContainerListener
{
    /**
     * @var array
     */
    protected $bundleConfig;

    public function __construct(array $bundleConfig)
    {
        $this->bundleConfig = $bundleConfig;
    }

    public function __invoke(string $table): void
    {
        if ('tl_member' === $table) {
            $this->addAliasField();
        }
    }

    private function addAliasField(): void
    {
        if (!isset($this->bundleConfig['enable_member_alias']) || true !== $this->bundleConfig['enable_member_alias']) {
            return;
        }

        PaletteManipulator::create()
            ->addField('alias', 'personal_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_member');

        $GLOBALS['TL_DCA']['tl_member']['fields']['alias'] = [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'save_callback' => [
                [MemberContainer::class, 'onAliasSaveCallback'],
            ],
            'eval' => ['rgxp' => 'alias', 'doNotCopy' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) BINARY NOT NULL default ''",
        ];
    }
}

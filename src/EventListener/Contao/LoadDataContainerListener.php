<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook("loadDataContainer")
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
        $this->addAliasField();
    }

    private function addAliasField(): void
    {
        if (!isset($this->bundleConfig['enable_member_alias']) || true !== $this->bundleConfig['enable_member_alias']) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_member']['fields']['alias'] = [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'doNotCopy' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) BINARY NOT NULL default ''",
        ];
    }
}

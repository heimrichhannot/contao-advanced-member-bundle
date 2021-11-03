<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\EventListener\Contao;

use Contao\Controller;
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
            $this->addAdditionalTitleFields();
            $this->addAdditionalJobTitles();
            $this->addImageFields();
            $this->addSocialFields();
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

    private function addAdditionalTitleFields(): void
    {
        if (true !== ($this->bundleConfig['enable_additional_title_fields'] ?? false)) {
            return;
        }

        PaletteManipulator::create()
            ->addField('academicTitle', 'firstname', PaletteManipulator::POSITION_BEFORE)
            ->addField('extendedTitle', 'firstname', PaletteManipulator::POSITION_BEFORE)
            ->addField('nobilityTitle', 'firstname', PaletteManipulator::POSITION_BEFORE)
            ->addField('academicDegree', 'firstname', PaletteManipulator::POSITION_BEFORE)
            ->applyToPalette('default', 'tl_member');

        $fields = [
            'academicTitle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['academicTitle'],
                'exclude' => true,
                'filter' => true,
                'sorting' => true,
                'inputType' => 'text',
                'eval' => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
                'sql' => "varchar(128) NOT NULL default ''",
            ],
            'academicDegree' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['academicDegree'],
                'exclude' => true,
                'filter' => true,
                'sorting' => true,
                'inputType' => 'text',
                'eval' => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
                'sql' => "varchar(128) NOT NULL default ''",
            ],
            'extendedTitle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['extendedTitle'],
                'exclude' => true,
                'filter' => true,
                'sorting' => true,
                'inputType' => 'text',
                'eval' => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
                'sql' => "varchar(128) NOT NULL default ''",
            ],
            'nobilityTitle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['nobilityTitle'],
                'exclude' => true,
                'filter' => true,
                'sorting' => true,
                'inputType' => 'text',
                'eval' => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
                'sql' => "varchar(128) NOT NULL default ''",
            ],
        ];

        $GLOBALS['TL_DCA']['tl_member']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_member']['fields'], $fields);
    }

    private function addAdditionalJobTitles(): void
    {
        if (true !== ($this->bundleConfig['enable_additional_job_fields'] ?? false)) {
            return;
        }

        PaletteManipulator::create()
            ->addField('jobTitles', 'firstname', PaletteManipulator::POSITION_BEFORE)
            ->addField('position', 'gender', PaletteManipulator::POSITION_AFTER)
            ->applyToPalette('default', 'tl_member');

        $fields = [
            'jobTitles' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['jobTitles'],
                'exclude' => true,
                'filter' => true,
                'inputType' => 'listWizard',
                'eval' => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
                'sql' => 'blob NULL',
            ],
            'position' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['position'],
                'exclude' => true,
                'filter' => true,
                'sorting' => true,
                'inputType' => 'text',
                'eval' => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
        ];

        if (isset($GLOBALS['BE_FFL']['tagsinput'])) {
            $fields['jobTitles']['inputType'] = 'tagsinput';
            $fields['jobTitles']['options_callback'] = [MemberContainer::class, 'onJobTitlesOptionsCallback'];
            $fields['jobTitles']['eval']['multiple'] = true;
            $fields['jobTitles']['eval']['freeInput'] = true;
        }

        $GLOBALS['TL_DCA']['tl_member']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_member']['fields'], $fields);
    }

    private function addImageFields(): void
    {
        if (true !== ($this->bundleConfig['enable_image_fields'] ?? false)) {
            return;
        }

        Controller::loadDataContainer('tl_content');

        $GLOBALS['TL_DCA']['tl_member']['subpalettes']['addImage'] = 'singleSRC,caption';
        $GLOBALS['TL_DCA']['tl_member']['palettes']['__selector__'][] = 'addImage';

        PaletteManipulator::create()
            ->addLegend('image_legend', 'contact_legend', PaletteManipulator::POSITION_AFTER)
            ->addField('addImage', 'image_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_member');

        $fields = [
            'addImage' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['addImage'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => ['submitOnChange' => true],
                'sql' => "char(1) NOT NULL default ''",
            ],
            'singleSRC' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
                'exclude' => true,
                'inputType' => 'fileTree',
                'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'],
                'load_callback' => [
                    ['tl_content', 'setSingleSrcFlags'],
                ],
                'sql' => 'binary(16) NULL',
            ],
            'caption' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['caption'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => ['maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
        ];

        $GLOBALS['TL_DCA']['tl_member']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_member']['fields'], $fields);
    }

    private function addSocialFields(): void
    {
        if (true !== ($this->bundleConfig['enable_social_fields'] ?? false)) {
            return;
        }

        PaletteManipulator::create()
            ->addField('linkedinProfile', 'contact_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('xingProfile', 'contact_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('facebookProfile', 'contact_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('twitterProfile', 'contact_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_member');

        $fields = [
            'linkedinProfile' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['linkedinProfile'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'save_callback' => [[MemberContainer::class, 'validateUrl']],
                'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'xingProfile' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['xingProfile'],
                'exclude' => true,
                'search' => true,
                'save_callback' => [[MemberContainer::class, 'validateUrl']],
                'inputType' => 'text',
                'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'facebookProfile' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['facebookProfile'],
                'exclude' => true,
                'search' => true,
                'save_callback' => [[MemberContainer::class, 'validateUrl']],
                'inputType' => 'text',
                'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'twitterProfile' => [
                'label' => &$GLOBALS['TL_LANG']['tl_member']['twitterProfile'],
                'exclude' => true,
                'search' => true,
                'save_callback' => [[MemberContainer::class, 'validateUrl']],
                'inputType' => 'text',
                'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
        ];

        $GLOBALS['TL_DCA']['tl_member']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_member']['fields'], $fields);
    }
}

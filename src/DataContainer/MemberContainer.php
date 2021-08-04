<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\MemberModel;
use Doctrine\DBAL\Connection;
use Exception;

class MemberContainer
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var Slug
     */
    protected $slug;

    public function __construct(Connection $connection, Slug $slug)
    {
        $this->connection = $connection;
        $this->slug = $slug;
    }

    /**
     * @Callback(table="tl_member", target="fields.login.save")
     *
     * @param string             $value
     * @param DataContainer|null $dc
     */
    public function onLoginSaveCallback($value, $dc = null)
    {
        if (!$dc || !$dc instanceof DataContainer || !($member = MemberModel::findByPk($dc->id))) {
            return $value;
        }

        /*
         * This is just a temporary implementation until a fix is found for
         * https://github.com/contao/contao/issues/3246
         * (see also https://contao.slack.com/archives/CK4J0KNDB/p1627907859023700)
         *
         * @TODO Check if login value was changed and set huhAdvMemberLocked to zero
         */
        if ('1' === $member->login && 0 != $member->huhAdvMemberLocked) {
            $member->huhAdvMemberLocked = 0;
            $member->save();
        }

        return $value;
    }

    /**
     * alias field save callback.
     *
     * @param string             $value
     * @param DataContainer|null $dc
     *
     * @return mixed|string
     */
    public function onAliasSaveCallback($value, $dc = null)
    {
        $aliasExists = function (string $alias) use ($dc): bool {
            return $this->connection->executeStatement('SELECT id FROM tl_member WHERE alias=? AND id!=?', [$alias, $dc->id]) > 0;
        };

        // Generate alias if there is none
        if (!$value) {
            $parts = array_filter([$dc->activeRecord->academicTitle, $dc->activeRecord->firstname, $dc->activeRecord->nobilityTitle, $dc->activeRecord->lastname]);

            if (empty($parts)) {
                $parts = ($dc->activeRecord->username ? [$dc->activeRecord->username] : [$dc->id]);
            }
            $value = $this->slug->generate(implode('-', $parts), [], $aliasExists, 'member-');
        } elseif (preg_match('/^[1-9]\d*$/', $value)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
        } elseif ($aliasExists($value)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }
}

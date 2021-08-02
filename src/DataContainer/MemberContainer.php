<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\MemberModel;
use Doctrine\DBAL\Connection;

class MemberContainer
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
}

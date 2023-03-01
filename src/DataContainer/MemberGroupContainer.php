<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\DataContainer;

use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

class MemberGroupContainer
{
    private Connection $connection;
    private Slug       $slug;

    public function __construct(Connection $connection, Slug $slug)
    {
        $this->connection = $connection;
        $this->slug = $slug;
    }

    public function onAliasSaveCallback($value, DataContainer $dc = null)
    {
        $aliasExists = function (string $alias) use ($dc): bool {
            return $this->connection->executeStatement('SELECT id FROM tl_member_group WHERE alias=? AND id!=?', [$alias, $dc->id]) > 0;
        };

        // Generate alias if there is none
        if (!$value) {
            $value = $this->slug->generate($dc->activeRecord->name, [], $aliasExists, 'membergroup-');
        } elseif (preg_match('/^[1-9]\d*$/', $value)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
        } elseif ($aliasExists($value)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }
}

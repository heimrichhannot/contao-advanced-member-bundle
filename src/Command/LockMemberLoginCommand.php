<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AdvancedMemberBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LockMemberLoginCommand extends Command
{
    public static $defaultName = 'huh:member:lock-login';
    public static $defaultDescription = 'This command locks or unlocks the member login.';

    /**
     * @var Connection
     */
    protected $connection;

    /** @var bool */
    protected $dryRun = false;
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function configure()
    {
        $this
            ->setDescription(static::$defaultDescription)
            ->setHelp(
                "This command disables (or restore) the login option for all members.\n\n"
                ."The following statement disables the login for all members:\n\n"
                ."<info>php ./vendor/bin/contao-console huh:member:lock-login lock</info>\n\n"
                ."The following statement restores the login for all members where the login was disabled by this command:\n\n"
                ."<info>php ./vendor/bin/contao-console huh:member:lock-login unlock</info>\n\n"
                ."If you want to check how many members will be locked before, you can use the dry-run option:\n\n"
                ."<info>php ./vendor/bin/contao-console huh:member:lock-login lock --dry-run</info>\n\n"
            )
            ->addArgument('action', InputArgument::REQUIRED, 'Choose which action to perform: One of "lock"; "unlock"')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Performs a run without making changes to the database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->io = $io;

        $io->title('Member Lock/Unlock');

        if ($input->hasOption('dry-run') && $input->getOption('dry-run')) {
            $this->dryRun = true;
            $io->note('Dry run enabled, no data will be changed.');
            $io->newLine();
        }

        $action = $input->hasArgument('action') ? $input->getArgument('action') : null;

        if (!$action || !\in_array($action, ['lock', 'unlock'])) {
            $io->error('You forgot added an argument or used an invalid one. Only lock or unlock are allowed as argument.');

            return 1;
        }

        $result = 0;

        switch ($action) {
            case 'lock':
                $this->lockMembers();

                break;

            case 'unlock':
                $this->unlockMember();
        }

        $io->success('Finished Member lock/unlock command');

        return 0;
    }

    private function getLockedMembers(): array
    {
        $result = $this->connection->executeQuery('SELECT id FROM tl_member WHERE huhAdvMemberLocked!=0');

        if (0 === $result->rowCount()) {
            return [];
        }

        return $result->fetchFirstColumn();
    }

    private function lockMembers(): int
    {
        $this->io->section('Lock members');

        $result = $this->connection->executeQuery("SELECT COUNT(id) FROM tl_member WHERE `huhAdvMemberLocked` = 0 AND `login` = '1'");
        $count = $result->fetchOne();

        if ($count < 1) {
            $this->io->note('There are no unlocked members.');

            return 0;
        }

        $this->io->text('Found <bg=yellow;fg=black> '.$count.' </> unlocked members.');
        $this->io->newLine();

        if (!$this->dryRun) {
            $result = $this->connection->executeStatement(
                "UPDATE `tl_member` SET `login`='', `huhAdvMemberLocked`=? WHERE `huhAdvMemberLocked` = 0 AND `login` = '1'",
                [time()]
            );
        } else {
            $result = $count;
        }

        $this->io->text('Locked <bg=green;fg=black> '.$result.' </> members.');

        return 0;
    }

    private function unlockMember(): int
    {
        $this->io->section('Unlock members');

        $members = $this->getLockedMembers();

        if (empty($members)) {
            $this->io->note('There are no locked members.');

            return 0;
        }

        $this->io->text('Found <bg=yellow;fg=black> '.\count($members).' </> locked members.');
        $this->io->newLine();

        $stmt = $this->connection->prepare("UPDATE `tl_member` SET `login`='1', `huhAdvMemberLocked`='0' WHERE `id` = ?");

        $count = 0;

        foreach ($members as $member) {
            if (!$this->dryRun) {
                $result = $stmt->executeStatement([$member]);
            } else {
                $result = 1;
            }

            if ($result < 1) {
                $this->io->note('Could not update member with id '.$member.'!');
            } else {
                ++$count;
            }
        }

        $this->io->text('Unlocked <bg=green;fg=black> '.$count.' </> members.');

        return 0;
    }
}

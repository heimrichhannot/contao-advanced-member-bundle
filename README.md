# Contao Advanced Member Bundle

This bundle enhances the contao member entity.

## Features

- option to add a member alias
- Lock member login command

## Usage

### Install  

1. Install with composer or contao manager

        composer require heimrichhannot/contao-advanced-member-bundle

2. Update database

### Member alias

![](docs/images/screenshot_alias.png)

Set `huh_advanced_member.enable_member_alias` to `true` and update the database, to enable member aliase. 

```yaml
# config/config.yml
huh_advanced_member:
    enable_member_alias:  true
```

### Lock member logins

To lock or unlock the login for all members, use the `huh:member:lock-login` command.

```
Usage:
  huh:member:lock-login [options] [--] <action>

Arguments:
  action                Choose which action to perform: One of "lock"; "unlock"

Options:
      --dry-run         Performs a run without making changes to the database.
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command disables (or restore) the login option for all members.
  
  The following statement disables the login for all members:
  
  php ./vendor/bin/contao-console huh:member:lock-login lock
  
  The following statement restores the login for all members where the login was disabled by this command:
  
  php ./vendor/bin/contao-console huh:member:lock-login unlock
  
  If you want to check how many members will be locked before, you can use the dry-run option:
  
  php ./vendor/bin/contao-console huh:member:lock-login lock --dry-run
```

## Configuration reference

```yaml
# Default configuration for extension with alias: "huh_advanced_member"
huh_advanced_member:

    # Enable to add an alias field to member entity.
    enable_member_alias:  false
```
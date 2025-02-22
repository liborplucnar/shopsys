# Backward Compatibility Promise

Smooth and safe upgrades of your e-commerce project are very important to us.
At the same time, we need to improve Shopsys Platform for you by adding functionality, enhancing or simplifying current functions, and fixing bugs.
After reading this promise, you'll understand backward compatibility, what changes you can expect, and how we plan to make changes in the future.

## Releases and Versioning

This project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html), which means its release versions are in format `MAJOR.MINOR.PATCH`:

- `MAJOR` version may contain incompatible changes
- `MINOR` version may add new functionality in a backward-compatible manner
- `PATCH` version contains only backward-compatible bug fixes

Even though we keep these rules as best we can, a BC breaking change might be introduced in a `MINOR` or `PATCH` release.
E.g., for an important security fix or a critical bug fix.
If this is the case, we will mark the Pull Request with `[BC-BREAK]` in the title and explain the reasons for it in its description.

Released versions will always be marked using git tags with the `v` prefix (e.g., `v7.0.0`).
Once created, a git tag marking a release will never be removed or edited.

!!! note

    Pre-release versions may introduce incompatible changes and can be used to try out the new functions and changes.<br>
    Pre-release version format is `MAJOR.MINOR.PATCH-<alpha|beta|rc><n>`, e.g., `7.0.0-beta5`.

## The BC Promise in Detail

Shopsys Platform is built on the shoulders of giants, so we've based our BC promise on the [**Symfony Backward Compatibility Promise**](https://symfony.com/doc/5.4/contributing/code/bc.html).
Exceptions from adhering to Symfony's promise and clarifications for non-PHP source codes can be found below.

### Project-base Repository

The [project-base repository](https://github.com/shopsys/project-base) is not meant to be extended or depended upon.
For this reason, the changes in it are not subject to the rules of this BC promise.
It should be viewed as a template for your own e-commerce projects built on top of Shopsys Platform.

This means the `project-base` should run with any higher minor version of Shopsys Platform up to the next `MAJOR` version.

Changes to the `project-base` may contain new features for the front-end or examples of newly implemented features and configuration options.
You can follow the changes in the repository to see how working with Shopsys Platform changes between the versions and to keep in touch with best practices and recommendations.

During an upgrade to the next major version, you must make the changes yourself according to the advice in [UPGRADE.md](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md).

!!! note

    The same holds true for the [demoshop repository](https://github.com/shopsys/demoshop), which is a complex example of an e-commerce project using a custom design and modifications.

### PHP Code

Basic rules for PHP code are covered by [Symfony Backward Compatibility Promise](https://symfony.com/doc/3.4/contributing/code/bc.html).

#### Exceptions

There are some exceptions that extend or override the BC promise of Symfony, which allows us a bit more flexibility without compromising the user's code:

**Adding an optional parameter to a constructor** is not considered a BC break and may be done in any release.

**Changing the constructor's parameter type to a parent type** (e.g., changing `array` to `iterable`) is not considered a BC break and may be done in any release.

Constructors in extended classes are not forced to have the same signature as the parent. The previous usage is still supported in these cases.
Doing this in other public or protected methods is still considered a BC break.

### Database Migrations

A new version may include database migrations if the structure of [the entities](../model/entities.md) is changed.

Migrations in `MINOR` releases are backward-compatible.
It means they may not change the types of existing columns, rename columns and tables, and remove the nullability of a column.

Migrations in `PATCH` releases are backward-compatible and may be used only for bug fixes.

Before running them on your production data, you should always check and test the database migrations.

_Tip: Read about the possibilities of altering the execution of DB migration using the [`migrations-lock.yml` file](../introduction/database-migrations.md#locking-the-order-of-migrations)._

### Translation Messages

New [translation messages](../introduction/translations.md) may be added or have their translation changed in any release.
However, they may be removed or have their message ID changed only in `MAJOR` releases.

When changing the message (e.g., because of a typo) in a `MINOR` or `PATCH` release, only its translation may be changed.
In such instances, it is preferred to keep using the original message ID to ensure backward compatibility with existing user-defined translations.
This might be unintuitive for contributors because we use English text as message IDs.
See an example of fixing a typo in an English translation message:

Initially, there is only the message ID in the `messages.en.po` (translation doesn't have to be filled):

```diff
  msgid "Exaple translation"
  msgstr ""
```

In a `PATCH` or `MINOR` release, the original message ID may not be removed:

```diff
  msgid "Exaple translation"
- msgstr ""
+ msgstr "Example translation"
```

In a `MAJOR` release, the original message ID with the typo may be removed:

```diff
- msgid "Exaple translation"
- msgstr "Example translation"
+ msgid "Example translation"
+ msgstr ""
```

### Routing

New routes may be added in any release.
Existing routes should not be modified in a `MINOR` or `PATCH` release, except for adding a new [optional placeholder](https://symfony.com/doc/3.4/routing/optional_placeholders.html).

Routes may be changed or removed only in a `MAJOR` release.

### Docker Configuration and Orchestration Manifests

The configuration of containers and orchestration is mostly in the `project-base` repository, which means that [it's excluded from the BC promise](#project-base-repository).

The changes should always be described in [upgrade notes](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) (in the _Infrastructure_ section).

### Twig

Changes of Twig functions and filters in `MINOR` and `PATCH` releases must be backward-compatible.
This means only a new optional argument or a support for new data type of existing argument may be added.

Twig blocks, functions, filters, macros, and macros imports in templates can be removed or renamed only in a `MAJOR` release.

### HTML

Backward-compatible changes and additions to the HTML structure may be introduced in any release.

Significant changes of the HTML structure should be avoided in the `MINOR` and `PATCH` releases.
They may be introduced in a `MAJOR` release, and they must always be described in detail in [upgrade notes](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) (in the _Application_ section).

The front-end templates are in the `project-base` repository, which means that [they're excluded from the BC promise](#project-base-repository).

### LESS / CSS

Classes can be renamed only in a `MAJOR` release.

Changes in the administration's visual style are not considered a BC break and may be introduced in any version.

The front-end style is in the `project-base` repository, which means that [it's excluded from the BC promise](#project-base-repository).

### Javascript

Javascript code should adhere to similar rules as the PHP code ([except in project-base repository](#project-base-repository)).

New keys in an `options` map or a new expected data attribute may be added in a `MINOR` or `PATCH` release only if it has a default value.
Adding a new optional argument to a method is considered a BC break (as the method might be overridden by the user with the new argument already in use) and is not allowed in a `MINOR` or `PATCH` release.

The behavior of elements with classes prefixed with `.js-` should be changed only in a `MAJOR` release.

## Summary

### If You Are a User of Shopsys Platform...

- we use [Semantic Versioning](http://semver.org/spec/v2.0.0.html) (`MAJOR.MINOR.PATCH`)
- all higher `MINOR` and `PATCH` releases should be compatible with your project
- use [caret version ranges](https://getcomposer.org/doc/articles/versions.md#caret-version-range-) in your `composer.json` (e.g., `^7.0.0`)
- when upgrading to a new major release, read the [upgrade notes](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md)
- watch for changes marked as `BC-BREAK` in the [changelog](https://github.com/shopsys/shopsys/blob/master/CHANGELOG.md)
- don't forget to execute new DB migrations via `./phing db-migrations` during upgrades
- we recommend to always upgrade to the highest minor version first, fix all deprecation notices, and then upgrade to the next major release
- read the [section Using Symfony Code](https://symfony.com/doc/3.4/contributing/code/bc.html#using-symfony-code) in their BC Promise for a nice clarification about BC in PHP
- upgrade often, it will be easier

### If You Are a Contributor to Shopsys Platform...

- read about [Semantic Versioning](http://semver.org/spec/v2.0.0.html)
- when making a change, always think about backward compatibility
- [add a deprecation](https://symfony.com/doc/3.4/contributing/code/conventions.html#deprecations) instead of removing code
- read the [section Working on Symfony Code](https://symfony.com/doc/3.4/contributing/code/bc.html#working-on-symfony-code) in their BC Promise for a nice clarification about BC in PHP
- test changes using an older version of `project-base`, it shouldn't cause any issues
- never change [translation message IDs](#translation-messages) except in `MAJOR` releases
- take your time when [explaining](./guidelines-for-writing-upgrade.md) how to upgrade to a BC-breaking change in [upgrade notes](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md)
- test new [database migrations](#database-migrations) thoroughly

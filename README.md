# congraph/eav

`congraph/eav` is the core content-model package in Congraph. It provides entity types, attribute sets, attributes, entities, and field handling.

## Responsibilities

- entity type CRUD
- attribute and attribute-set CRUD
- entity CRUD
- field-type behavior
- localized and workflow-aware entity handling
- repository queries for structured content

## Installation

```bash
composer require congraph/eav
```

Laravel package provider:

- [EavServiceProvider.php](/Users/nikolap/git/np/congraph/packages/eav/EavServiceProvider.php)

## Package relationships

`eav` depends on:

- `core`
- `contracts`
- `filesystem`
- `locales`
- `workflows`

It is the package that ties most of the content-model pieces together.

## Typical usage

Use `eav` to define:

- entity types such as articles, pages, products, or cases
- attribute sets for those content types
- field definitions and field behavior
- stored entities with localized and workflow-aware fields

## Search and delivery

`eav` is the canonical persistence layer.  
When delivery/search is enabled through `entity-elastic`, this package still owns the source data model.

## Development

### Install dependencies

```bash
cd /Users/nikolap/git/np/congraph/packages/eav
composer update -W
```

### Run tests

```bash
composer test
composer test:pgsql
```

The package has:

- integration tests
- field-type tests
- sample content-model tests

## Contribution notes

- prefer PostgreSQL-safe and SQLite-safe query behavior
- avoid MySQL-only raw SQL
- field-type changes should include tests in both field-level and integration contexts
- when changing entity status logic, re-test `entity-elastic`

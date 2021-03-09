# Wherewithal
Given constraints, parses a string of conditions into a valid MySQL WHERE clause

![Tests](https://github.com/markjaquith/Wherewithal/actions/workflows/tests.yml/badge.svg)

## Installation

`composer require markjaquith/wherewithal`

## Usage

```php
use MarkJaquith\Wherewithal\{Parser, Config};

$config = (new Config)
	->addOperators('<', '>', '<=', '>=', '=', '/') // Or add a subset.
	->addColumn('column_name', 'column_alias1', 'column_alias2')
	->addColumn('quantity')
	->addColumn('price', 'cost');

$parser = new Parser($config);

$structure = $parser->parse('quantity > 5 and (price < 3.00 or column_alias2 = 10'));

$structure->toString();
/*
  string(57) "quantity > ? and ( price < ? or price / column_name = ? )"
*/

$structure->getBindings()]);
/*
  array(3) {
    [0]=>
    string(1) "5"
    [1]=>
    string(4) "3.00"
    [2]=>
    string(2) "10"
  }
*/
```

You can also map simple column names to complex expressions like so:

```php
$structure->mapColumns(function($col) {
	return [
		'column_name' => '`some_long_table_name`.`long_column_name`',
	]($col) ?? $col;
})->toString();
/*
	string(92) "(`some_long_table_name`.`long_column_name`) > ? and ( price < ? or price / column_name = ? )"
*/
```

Columns that you don't put in the config will be assumed to be values. Values
always use the placeholder token `?`.

You should combine the resulting `WHERE` (or `HAVING`) clause using your database
layer. Here's how you'd do it in Laravel:

```php
$orders = DB::table('orders')
	->whereRaw($structure->toString(), $structure->getBindings())
  ->get();
```

<?php

namespace Unit;

use MarkJaquith\Wherewithal\{Parser, Config, Structure};
use MarkJaquith\Wherewithal\Contracts\{ParserContract, ConfigContract, StructureContract};
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {
	public function test_config_can_add_comparisons() {
		$config = new Config();
		$this->assertEmpty($config->getComparisons());

		$config->addComparison('<');
		$this->assertEquals(['<'], $config->getComparisons());

		$config->addComparison('<');
		$this->assertEquals(['<'], $config->getComparisons());

		$config->addComparison('>');
		$this->assertEquals(['<', '>'], $config->getComparisons());

		$config->addComparisons('>=', '<=');
		$this->assertEquals(['<', '>', '>=', '<='], $config->getComparisons());
	}

	public function test_config_can_add_columns() {
		$config = new Config();
		$this->assertEmpty($config->getColumns());

		$config->addColumn('foo');
		$this->assertEquals(['foo' => 'foo'], $config->getColumns());

		$config->addColumn('foo');
		$this->assertEquals(['foo' => 'foo'], $config->getColumns());

		$config->addColumn('bar', 'bar2', 'bar3');
		$this->assertEquals(['foo' => 'foo', 'bar' => 'bar', 'bar2' => 'bar', 'bar3' => 'bar'], $config->getColumns());
	}
}

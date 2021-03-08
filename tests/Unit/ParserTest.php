<?php

namespace Unit;

use MarkJaquith\Wherewithal\{Parser, Config, Structure};
use MarkJaquith\Wherewithal\Contracts\{ParserContract, ConfigContract, StructureContract};
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase {
	public function test_parser_yields_structure() {
		$config = new Config();
		$parser = new Parser($config);
		$structure = $parser->parse('foo > 0');

		$this->assertInstanceOf(StructureContract::class, $structure);
	}
}

<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\{ConfigContract, ParserContract, StructureContract};

class Parser implements ParserContract {
	private ConfigContract $config;

	/**
	 * Parser constructor.
	 *
	 * @param ConfigContract $config
	 */
	public function __construct(ConfigContract $config) {
		$this->config = $config;
	}

	public function parse(string $query): StructureContract {
		return new Structure;
	}
}

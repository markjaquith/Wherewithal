<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\{ConfigContract, ParserContract, StructureContract};

class Parser implements ParserContract {
	const PREG_DELIMITER = '#';
	private ConfigContract $config;

	/**
	 * Parser constructor.
	 *
	 * @param ConfigContract $config
	 */
	public function __construct(ConfigContract $config) {
		$this->config = $config;
	}

	public function normalizeQuery(string $query): string {
		return preg_replace('# (and|or)\(#i', ' $1 (', $query);
	}

	private function splitByDelimiters(string $input, array $delimiters) {
		$splits = array_map(fn($delimiter) => preg_quote($delimiter, self::PREG_DELIMITER), $delimiters);
		$matches = preg_split(sprintf('%s(%s)%si', self::PREG_DELIMITER, join('|', $splits), self::PREG_DELIMITER), $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$matches = array_map('trim', $matches);

		return array_filter($matches, fn($match) => strlen($match) > 0);
	}

	public function tokenize(string $query): array {
		$query = $this->normalizeQuery($query);

		return $this->splitByDelimiters($query, ['(', ')', ' and ', ' or ']);
	}

	public function parse(string $query): StructureContract {
		$tokens = $this->tokenize($query);

		return new Structure([]);
	}

	public function parseComparison(string $input): array {
		return $this->splitByDelimiters($input, $this->config->getComparisons());
	}
}

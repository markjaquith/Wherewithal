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

	public function normalizeQuery(string $query): string {
		return preg_replace('# (and|or)\(#i', ' $1 (', $query);
	}

	public function tokenize(string $query): array {
		$query = $this->normalizeQuery($query);

		$pregDelimiter = '#';
		$splits = [
			preg_quote('(', $pregDelimiter),
			preg_quote(')', $pregDelimiter),
			' and ',
			' or ',
		];

		// Add our allowed comparisons
		foreach ($this->config->getComparisons() as $comparison) {
			$splits[] = preg_quote($comparison, $pregDelimiter);
		}

		$matches = preg_split(sprintf('%s(%s)%si', $pregDelimiter, join('|', $splits), $pregDelimiter), $query, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		$matches = array_map('trim', $matches);
		return array_filter($matches, fn($match) => strlen($match) > 0);
	}

	public function parse(string $query): StructureContract {
		$tokens = $this->tokenize($query);

		return new Structure([]);
	}
}

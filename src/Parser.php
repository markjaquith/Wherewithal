<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\{ConfigContract, ParserContract, StructureContract};
use MarkJaquith\Wherewithal\Exceptions\{AdjacentColumnException,
	AdjacentOperatorException,
	EmptyGroupException,
	MissingOperatorException,
	ParenthesesMismatchException,
	InvalidConjunctionPlacementException};

class Parser implements ParserContract {
	const PREG_DELIMITER = '#';
	const TOKEN_GROUP_START = 0b00000001;
	const TOKEN_GROUP_END = 0b00000010;
	const TOKEN_AND = 0b00000100;
	const TOKEN_OR = 0b00001000;
	const TOKEN_OPERATOR = 0b00010000;
	const TOKEN_COLUMN = 0b00100000;
	const TOKEN_VALUE = 0b01000000;
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

	public function splitConjunctions(string $query): array {
		$query = $this->normalizeQuery($query);

		return $this->splitByDelimiters($query, ['(', ')', ' and ', ' or ']);
	}

	public function isConjunction(string $input): bool {
		return in_array($input, ['(', ')', 'and', 'or', 'AND', 'OR']);
	}

	public function makeToken($value = '') {
		$column = $this->config->getColumn(strtolower($value));
		if ($column) {
			return ['type' => self::TOKEN_COLUMN, 'value' => $column];
		} elseif ($this->config->isOperator($value)) {
			return ['type' => self::TOKEN_OPERATOR, 'value' => $value];
		} elseif ($this->isConjunction($value)) {
			switch (strtolower($value)) {
				case '(':
					return ['type' => self::TOKEN_GROUP_START];
				case ')':
					return ['type' => self::TOKEN_GROUP_END];
				case 'and':
					return ['type' => self::TOKEN_AND];
				case 'or':
					return ['type' => self::TOKEN_OR];
			}
		}

		return ['type' => self::TOKEN_VALUE, 'value' => $value];
	}

	public function parse(string $query): StructureContract {
		$out = [];
		$parts = $this->splitConjunctions($query);
		foreach ($parts as $part) {
			if ($this->isConjunction($part)) {
				$out[] = $this->makeToken($part);
			} else {
				// It's a condition.
				foreach ($this->parseCondition($part) as $conditionPart) {
					$out[] = $this->makeToken($conditionPart);
				}
			}
		}

		// Sanity checks.

		// Parentheses do not match.
		$leftParen = count(array_filter($out, fn($part) => $part['type'] === self::TOKEN_GROUP_START));
		$rightParen = count(array_filter($out, fn($part) => $part['type'] === self::TOKEN_GROUP_END));

		if ($leftParen !== $rightParen) {
			throw new ParenthesesMismatchException;
		}

		array_reduce($out, function ($previous, $current) {
			switch([$previous['type'], $current['type']]) {
				case [self::TOKEN_GROUP_START, self::TOKEN_GROUP_END]:
					throw new EmptyGroupException;
				case [0, self::TOKEN_GROUP_END]:
					throw new ParenthesesMismatchException;
				case [self::TOKEN_OPERATOR, self::TOKEN_OPERATOR]:
					throw new AdjacentOperatorException;
				case [self::TOKEN_COLUMN, self::TOKEN_COLUMN];
					throw new AdjacentColumnException;
				case [self::TOKEN_VALUE, self::TOKEN_COLUMN]:
				case [self::TOKEN_COLUMN, self::TOKEN_VALUE]:
					throw new MissingOperatorException;
			}

			return $current;
		}, ['type' => 0]);

		return new Structure($out);
	}

	public function parseCondition(string $input): array {
		return $this->splitByDelimiters($input, $this->config->getOperators());
	}
}

<?php

namespace MarkJaquith\Wherewithal\Contracts;

interface ConfigContract {
	public function addOperator(string $operator): self;
	public function addOperators(string ...$operators): self;
	public function getOperators(): array;
	public function addColumn(string $column, string ...$aliases): self;
	public function getColumns(): array;
	public function getColumn(string $column): ?string;
	public function isOperator(string $operator): bool;
}

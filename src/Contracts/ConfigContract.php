<?php

namespace MarkJaquith\Wherewithal\Contracts;

interface ConfigContract {
	public function addComparison(string $comparison): self;
	public function addComparisons(string ...$comparisons): self;
	public function getComparisons(): array;
	public function addColumn(string $column, string ...$aliases): self;
	public function getColumns(): array;
}

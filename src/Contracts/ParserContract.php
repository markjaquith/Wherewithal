<?php

namespace MarkJaquith\Wherewithal\Contracts;

interface ParserContract {
	public function parse(string $query): StructureContract;
	public function tokenize(string $query): array;
}

<?php

namespace MarkJaquith\Wherewithal\Contracts;

interface StructureContract {
	public function __toString(): string;
	public function toString(): string;
	public function getBindings(): array;
	public function toArray(): array;
}
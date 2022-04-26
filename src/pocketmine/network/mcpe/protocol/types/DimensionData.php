<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;

final class DimensionData {

	public function __construct(
		private int $maxHeight,
		private int $minHeight,
		private int $generator
	){}

	public function getMaxHeight() : int{ return $this->maxHeight; }

	public function getMinHeight() : int{ return $this->minHeight; }

	public function getGenerator() : int{ return $this->generator; }

	public static function read(NetworkBinaryStream $stream) : self{
		$maxHeight = $stream->getVarInt();
		$minHeight = $stream->getVarInt();
		$generator = $stream->getVarInt();

		return new self($maxHeight, $minHeight, $generator);
	}

	public function write(NetworkBinaryStream $stream) : void{
		$stream->putVarInt($this->maxHeight);
		$stream->putVarInt($this->minHeight);
		$stream->putVarInt($this->generator);
	}
}
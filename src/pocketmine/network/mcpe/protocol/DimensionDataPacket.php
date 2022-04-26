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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\DimensionData;
use pocketmine\network\mcpe\protocol\types\DimensionNameIds;
use function count;

class DimensionDataPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::DIMENSION_DATA_PACKET;

	/**
	 * @var DimensionData[]
	 * @phpstan-var array<DimensionNameIds::*, DimensionData>
	 */
	private $definitions;

	/**
	 * @generate-create-func
	 * @param DimensionData[] $definitions
	 * @phpstan-param array<DimensionNameIds::*, DimensionData> $definitions
	 */
	public static function create(array $definitions) : self{
		$result = new self;
		$result->definitions = $definitions;
		return $result;
	}

	/**
	 * @return DimensionData[]
	 * @phpstan-return array<DimensionNameIds::*, DimensionData>
	 */
	public function getDefinitions() : array{ return $this->definitions; }

	protected function decodePayload() : void{
		$this->definitions = [];

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; $i++){
			$dimensionNameId = $this->getString();
			$dimensionData = DimensionData::read($this);

			if(isset($this->definitions[$dimensionNameId])){
				throw new \ErrorException("Repeated dimension data for key \"$dimensionNameId\"");
			}
			if($dimensionNameId !== DimensionNameIds::OVERWORLD && $dimensionNameId !== DimensionNameIds::NETHER && $dimensionNameId !== DimensionNameIds::THE_END){
				throw new \ErrorException("Invalid dimension name ID \"$dimensionNameId\"");
			}
			$this->definitions[$dimensionNameId] = $dimensionData;
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->definitions));

		foreach($this->definitions as $dimensionNameId => $definition){
			$this->putString((string) $dimensionNameId);
			$definition->write($this);
		}
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleDimensionData($this);
	}
}
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

class ChangeMobPropertyPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::CHANGE_MOB_PROPERTY_PACKET;

	/** @var int */
	private $entityUniqueId;
	/** @var string */
	private $propertyName;
	/** @var bool */
	private $boolValue;
	/** @var string */
	private $stringValue;
	/** @var int */
	private $intValue;
	/** @var float */
	private $floatValue;

	/**
	 * @generate-create-func
	 */
	private static function create(int $entityUniqueId, string $propertyName, bool $boolValue, string $stringValue, int $intValue, float $floatValue) : self{
		$result = new self;
		$result->entityUniqueId = $entityUniqueId;
		$result->propertyName = $propertyName;
		$result->boolValue = $boolValue;
		$result->stringValue = $stringValue;
		$result->intValue = $intValue;
		$result->floatValue = $floatValue;
		return $result;
	}

	public static function boolValue(int $entityUniqueId, string $propertyName, bool $value) : self{
		return self::create($entityUniqueId, $propertyName, $value, "", 0, 0);
	}

	public static function stringValue(int $entityUniqueId, string $propertyName, string $value) : self{
		return self::create($entityUniqueId, $propertyName, false, $value, 0, 0);
	}

	public static function intValue(int $entityUniqueId, string $propertyName, int $value) : self{
		return self::create($entityUniqueId, $propertyName, false, "", $value, 0);
	}

	public static function floatValue(int $entityUniqueId, string $propertyName, float $value) : self{
		return self::create($entityUniqueId, $propertyName, false, "", 0, $value);
	}

	public function getEntityUniqueId_PUB() : int{ return $this->entityUniqueId; }

	public function getPropertyName() : string{ return $this->propertyName; }

	public function isBoolValue() : bool{ return $this->boolValue; }

	public function getStringValue() : string{ return $this->stringValue; }

	public function getIntValue() : int{ return $this->intValue; }

	public function getFloatValue() : float{ return $this->floatValue; }

	protected function decodePayload() : void{
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->propertyName = $this->getString();
		$this->boolValue = $this->getBool();
		$this->stringValue = $this->getString();
		$this->intValue = $this->getVarInt();
		$this->floatValue = $this->getLFloat();
	}

	protected function encodePayload() : void{
		$this->putEntityUniqueId($this->entityUniqueId);
		$this->putString($this->propertyName);
		$this->putBool($this->boolValue);
		$this->putString($this->stringValue);
		$this->putVarInt($this->intValue);
		$this->putLFloat($this->floatValue);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleChangeMobProperty($this);
	}
}
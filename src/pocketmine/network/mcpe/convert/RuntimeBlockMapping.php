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

namespace pocketmine\network\mcpe\convert;

use pocketmine\block\BlockIds;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\AssumptionFailedError;
use function file_get_contents;
use function json_decode;

/**
 * @internal
 */
final class RuntimeBlockMapping{

	/** @var int[][] */
	private static $legacyToRuntimeMap = [];
	/** @var int[][] */
	private static $runtimeToLegacyMap = [];
	/** @var CompoundTag[][]|null */
	private static $bedrockKnownStates = [];

	private function __construct(){
		//NOOP
	}

	public static function init() : void{
		foreach (ProtocolInfo::STR as $protocolId => $versionStr) {
			$canonicalBlockStatesFile = file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/canonical_block_states" . $versionStr . ".nbt");
			if($canonicalBlockStatesFile === false){
				throw new AssumptionFailedError("Missing required resource file");
			}
			$stream = new NetworkBinaryStream($canonicalBlockStatesFile);
			$list = [];
			while(!$stream->feof()){
				$list[] = $stream->getNbtCompoundRoot();
			}
			self::$bedrockKnownStates[$protocolId] = $list;

			self::setupLegacyMappings($protocolId);
		}
	}

	private static function setupLegacyMappings(int $protocol) : void{
		$strpro = ProtocolInfo::STR[$protocol];
		$legacyIdMap = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/block_id_map.json"), true);
		$metaMap = [];

		/** @var R12ToCurrentBlockMapEntry[] $legacyStateMap */
		$legacyStateMap = [];
		foreach(RuntimeBlockMapping::$bedrockKnownStates[$protocol] as $runtimeId => $state){
			$name = $state->getString("name");
			if(!isset($legacyIdMap[$name])){
				continue;
			}

			$legacyId = $legacyIdMap[$name];
			if($legacyId <= 469){
				continue;
			}elseif(!isset($metaMap[$legacyId])){
				$metaMap[$legacyId] = 0;
			}

			$meta = $metaMap[$legacyId]++;
			if($meta > 15){
				continue;
			}

			self::registerMapping($runtimeId, $legacyId, $meta, $protocol);
		}
		$legacyStateMapReader = new NetworkBinaryStream(file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/r12_to_current_block_map" . $strpro . ".bin"));
		$nbtReader = new NetworkLittleEndianNBTStream();
		while(!$legacyStateMapReader->feof()){
			$id = $legacyStateMapReader->getString();
			$meta = $legacyStateMapReader->getLShort();

			$offset = $legacyStateMapReader->getOffset();
			$state = $nbtReader->read($legacyStateMapReader->getBuffer(), false, $offset);
			$legacyStateMapReader->setOffset($offset);
			if(!($state instanceof CompoundTag)){
				throw new \RuntimeException("Blockstate should be a TAG_Compound");
			}
			$legacyStateMap[] = new R12ToCurrentBlockMapEntry($id, $meta, $state);
		}

		/**
		 * @var int[][] $idToStatesMap string id -> int[] list of candidate state indices
		 */
		$idToStatesMap = [];
		foreach(self::$bedrockKnownStates[$protocol] as $k => $state){
			$idToStatesMap[$state->getString("name")][] = $k;
		}
		foreach($legacyStateMap as $pair){
			$id = $legacyIdMap[$pair->getId()] ?? null;
			if($id === null){
				throw new \RuntimeException("No legacy ID matches " . $pair->getId());
			}
			$data = $pair->getMeta();
			if($data > 15){
				//we can't handle metadata with more than 4 bits
				continue;
			}
			$mappedState = $pair->getBlockState();

			//TODO HACK: idiotic NBT compare behaviour on 3.x compares keys which are stored by values
			$mappedState->setName("");
			$mappedName = $mappedState->getString("name");
			if(!isset($idToStatesMap[$mappedName])){
				throw new \RuntimeException("Mapped new state does not appear in network table");
			}
			foreach($idToStatesMap[$mappedName] as $k){
				$networkState = self::$bedrockKnownStates[$protocol][$k];
				if($mappedState->equals($networkState)){
					self::registerMapping($k, $id, $data, $protocol);
					continue 2;
				}
			}
			throw new \RuntimeException("Mapped new state does not appear in network table");
		}
	}

	private static function lazyInit() : void{
		if(self::$bedrockKnownStates === null){
			self::init();
		}
	}

	public static function toStaticRuntimeId(int $protocol, int $id, int $meta = 0) : int{
//		self::lazyInit();
		/*
		 * try id+meta first
		 * if not found, try id+0 (strip meta)
		 * if still not found, return update! block
		 */
		if(count(self::$bedrockKnownStates) < 1){
			self::init();
		}
		return self::$legacyToRuntimeMap[$protocol][($id << 4) | $meta] ?? self::$legacyToRuntimeMap[$protocol][$id << 4] ?? self::$legacyToRuntimeMap[$protocol][BlockIds::INFO_UPDATE << 4];
	}

	/**
	 * @return int[] [id, meta]
	 */
	public static function fromStaticRuntimeId(int $runtimeId, int $protocol) : array{
		self::lazyInit();
		$v = self::$runtimeToLegacyMap[$protocol][$runtimeId];
		return [$v >> 4, $v & 0xf];
	}

	private static function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta, int $protocol) : void{
		self::$legacyToRuntimeMap[$protocol][($legacyId << 4) | $legacyMeta] = $staticRuntimeId;
		self::$runtimeToLegacyMap[$protocol][$staticRuntimeId] = ($legacyId << 4) | $legacyMeta;
	}

	/**
	 * @return CompoundTag[]
	 */
	public static function getBedrockKnownStates(int $protocol = ProtocolInfo::PROTOCOL_V30) : array{
		self::lazyInit();
		return self::$bedrockKnownStates[$protocol];
	}
}

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

/**
 * Network-related classes
 */
namespace pocketmine\network;

use pocketmine\network\mcpe\NetworkSession;

class NetworkSessionManager {
	private $sessions = [];
	
	public function getSessions(): array{
		return $this->sessions;
	}
	
	public function add(NetworkSession $session){
		$id = spl_object_id($session);
		$this->sessions[$id] = $session;
	}
	
	public function remove(NetworkSession $session){
		if (isset($this->sessions[$session])) unset($this->sessions[spl_object_id($session)]);
	}
	
	public function getSessionsCount() : int{
		return count($this->sessions);
	}
	
	public function close(){
		
	}
	
	public function tick(){
		
	}
}
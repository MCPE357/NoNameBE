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

namespace pocketmine\network\mcpe;

use pocketmine\Server;
use pocketmine\network\NetworkSessionManager;
use pocketmine\network\mcpe\protocol\DataPacket;

use pocketmine\network\mcpe\protocol\ACK;
use pocketmine\Player;

class NetworkSession {
	
	private $server;
	private $manager;
	private ?Player $player;
	private $ip;
	private $port;
	
	public function __construct(Server $server, NetworkSessionManager $manager, $ip, $port){
		$this->server = $server;
		$this->manager = $manager;
		$this->ip = $ip;
		$this->port = $port;
	}
	
	public function getSessionManager() : SessionManager{
		return $this->manager;
	}
	
	public function getIp(){
		return $this->ip;
	}
	
	public function getPort() : int{
		return $this->port;
	}
	
	public function getPlayer(): Player{
		return $this->player;
	}
	
	public function setPlayer(Player $player){
		$this->player = $player;
	}
	
	public function sendDataPacket(DataPacket $packet, bool $needACK = false, bool $immediate = false){
		$this->player->sendDataPacket($packet, $needACK, $inmmediate);
	}
	
}

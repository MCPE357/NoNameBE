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

namespace pocketmine\player;

use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\Player as IPlayer;
use pocketmine\network\SourceInterface;
use pocketmine\network\mcpe\NetworkSession;

class Player extends IPlayer{
	
	public function __construct(Server $server, NetworkSession $session, SourceInterface $interface, $ip, $port) {
		//SourceInterface = RaklibInterface
		parent::__construct($interface, $ip, $port);
	}

	/*public function getPlayerInfo(): self
	{
		return $this;
	}soon implement */

	public function getWorld() : Level {
		return $this->getLevelNonNull();
	}
}

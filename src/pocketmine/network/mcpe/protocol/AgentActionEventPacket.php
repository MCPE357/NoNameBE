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

class AgentActionEventPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::AGENT_ACTION_EVENT_PACKET;

	/** @var string */
	private $requestId;
	/** @var int */
	private $action;
	/** @var string */
	private $responseJson;

	/**
	 * @generate-create-func
	 */
	public static function create(string $requestId, int $action, string $responseJson) : self{
		$result = new self;
		$result->requestId = $requestId;
		$result->action = $action;
		$result->responseJson = $responseJson;
		return $result;
	}

	public function getRequestId() : string{ return $this->requestId; }

	/** @see AgentActionType */
	public function getAction() : int{ return $this->action; }

	public function getResponseJson() : string{ return $this->responseJson; }

	protected function decodePayload() : void{
		$this->requestId = $this->getString();
		$this->action = $this->getLInt();
		$this->responseJson = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putString($this->requestId);
		$this->putLInt($this->action);
		$this->putString($this->responseJson);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleAgentActionEvent($this);
	}
}
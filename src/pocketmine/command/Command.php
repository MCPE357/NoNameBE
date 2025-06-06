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
 * Command handling related classes
 */
namespace pocketmine\command;

use pocketmine\command\utils\CommandException;
use pocketmine\lang\TextContainer;
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\types\CommandData;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\TextFormat;

use function array_values;
use function explode;
use function str_replace;
use function strlen;
use function ucfirst;

abstract class Command{

	/** @var CommandData */
	private $commandData;

	/** @var string */
	private $nextLabel;

	/** @var string */
	private $label;

	/** @var string[] */
	private $aliases = [];

	/** @var string[] */
	private $activeAliases = [];

	/** @var CommandMap|null */
	private $commandMap = null;

	/** @var string */
	protected $description = "";

	/** @var string */
	protected $usageMessage;

	/** @var string|null */
	private $permission = null;

	/** @var string|null */
	private $permissionMessage = null;

	/** @var TimingsHandler|null */
	public $timings = null;

	/**
	 * @param string[] $aliases
	 * @param CommandParameter[][] $overloads
	 */
	public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [], ?array $overloads = null){
		if(strlen($description) > 0 and $description[0] == '%'){
			$description = Server::getInstance()->getLanguage()->translateString($description);
		}

		$this->commandData = new CommandData();

		$this->commandData->commandName = $name;
		$this->commandData->commandDescription = $description;
		$this->commandData->flags = 0;
		$this->commandData->permission = 0;
		$this->commandData->aliases = null;
		$this->commandData->overloads = $overloads ?? [[new CommandParameter()]];

		$this->setLabel($name);
		$this->setAliases($aliases);
		$this->usageMessage = $usageMessage ?? ("/" . $name);
	}

	/**
	 * @param string[] $args
	 *
	 * @return mixed
	 * @throws CommandException
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args);

	public function getData() : CommandData{
		$data = clone $this->commandData;
		if(!empty($this->activeAliases)){
			$data->aliases = new CommandEnum(ucfirst($this->getName()) . "Aliases", array_values($this->activeAliases));
		}

		return $data;
	}

	public function getName() : string{
		return $this->commandData->commandName;
	}

	/**
	 * @return string|null
	 */
	public function getPermission(){
		return $this->permission;
	}

	/**
	 * @return void
	 */
	public function setPermission(string $permission = null){
		$this->permission = $permission;
	}

	public function testPermission(CommandSender $target) : bool{
		if($this->testPermissionSilent($target)){
			return true;
		}

		if($this->permissionMessage === null){
			$target->sendMessage($target->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));
		}elseif($this->permissionMessage !== ""){
			$target->sendMessage(str_replace("<permission>", $this->permission, $this->permissionMessage));
		}

		return false;
	}

	public function testPermissionSilent(CommandSender $target) : bool{
		if($this->permission === null or $this->permission === ""){
			return true;
		}

		foreach(explode(";", $this->permission) as $permission){
			if($target->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}

	public function getLabel() : string{
		return $this->label;
	}

	public function setLabel(string $name) : bool{
		$this->nextLabel = $name;
		if(!$this->isRegistered()){
			if($this->timings instanceof TimingsHandler){
				$this->timings->remove();
			}
			$this->timings = new TimingsHandler("** Command: " . $name);
			$this->label = $name;

			return true;
		}

		return false;
	}

	/**
	 * Registers the command into a Command map
	 */
	public function register(CommandMap $commandMap) : bool{
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	public function unregister(CommandMap $commandMap) : bool{
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = null;
			$this->setAliases($this->aliases);
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	private function allowChangesFrom(CommandMap $commandMap) : bool{
		return $this->commandMap === null or $this->commandMap === $commandMap;
	}

	public function isRegistered() : bool{
		return $this->commandMap !== null;
	}

	/**
	 * @return string[]
	 */
	public function getAliases() : array{
		return $this->aliases;
	}

	/**
	 * @param string[] $aliases
	 */
	public function setAliases(array $aliases){
		$this->aliases = $aliases;
		if(!$this->isRegistered()){
			$this->activeAliases = $aliases;
		}
	}

	public function getPermissionMessage() : ?string{
		return $this->permissionMessage;
	}

	public function setPermissionMessage(string $permissionMessage){
		$this->permissionMessage = $permissionMessage;
	}

	public function getDescription() : string{
		return $this->commandData->commandDescription;
	}

	public function setDescription(string $description){
		if(strlen($description) > 0 and $description[0] == '%'){
			$description = Server::getInstance()->getLanguage()->translateString($description);
		}

		$this->commandData->commandDescription = $description;
	}

	public function getUsage() : string{
		return $this->usageMessage;
	}

	/**
	 * @return void
	 */
	public function setUsage(string $usage){
		$this->usageMessage = $usage;
	}

	/**
	 * @param TextContainer|string $message
	 *
	 * @return void
	 */
	public static function broadcastCommandMessage(CommandSender $source, $message, bool $sendToSource = true){
		if($message instanceof TextContainer){
			$m = clone $message;
			$result = "[" . $source->getName() . ": " . ($source->getServer()->getLanguage()->get($m->getText()) !== $m->getText() ? "%" : "") . $m->getText() . "]";

			$users = PermissionManager::getInstance()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . $result;

			$m->setText($result);
			$result = clone $m;
			$m->setText($colored);
			$colored = clone $m;
		}else{
			$users = PermissionManager::getInstance()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$result = new TranslationContainer("chat.type.admin", [$source->getName(), $message]);
			$colored = new TranslationContainer(TextFormat::GRAY . TextFormat::ITALIC . "%chat.type.admin", [$source->getName(), $message]);
		}

		if($sendToSource and !($source instanceof ConsoleCommandSender)){
			$source->sendMessage($message);
		}

		foreach($users as $user){
			if($user instanceof CommandSender){
				if($user instanceof ConsoleCommandSender){
					$user->sendMessage($result);
				}elseif($user !== $source){
					$user->sendMessage($colored);
				}
			}
		}
	}

	/**
	 * Adds parameter to overload
	 */
	public function addParameter(CommandParameter $parameter, int $overloadIndex = 0) : void{
		$this->commandData->overloads[$overloadIndex][] = $parameter;
	}

	/**
	 * Sets parameter to overload
	 */
	public function setParameter(CommandParameter $parameter, int $parameterIndex, int $overloadIndex = 0) : void{
		$this->commandData->overloads[$overloadIndex][$parameterIndex] = $parameter;
	}

	/**
	 * Sets parameters to overload
	 *
	 * @param CommandParameter[] $parameters
	 */
	public function setParameters(array $parameters, int $overloadIndex = 0) : void{
		$this->commandData->overloads[$overloadIndex] = array_values($parameters);
	}

	/**
	 * Removes parameter from overload
	 */
	public function removeParameter(int $parameterIndex, int $overloadIndex = 0) : void{
		unset($this->commandData->overloads[$overloadIndex][$parameterIndex]);
	}

	/**
	 * Remove all overloads
	 */
	public function removeAllParameters() : void{
		$this->commandData->overloads = [];
	}

	/**
	 * Removes overload and includes.
	 */
	public function removeOverload(int $overloadIndex) : void{
		unset($this->commandData->overloads[$overloadIndex]);
	}

	/**
	 * Returns overload
	 *
	 * @return CommandParameter[]|null
	 */
	public function getOverload(int $index) : ?array{
		return $this->commandData->overloads[$index] ?? null;
	}

	/**
	 * Returns all overloads
	 *
	 * @return CommandParameter[][]
	 */
	public function getOverloads() : array{
		return $this->commandData->overloads;
	}

	public function __toString() : string{
		return $this->commandData->commandName;
	}
}

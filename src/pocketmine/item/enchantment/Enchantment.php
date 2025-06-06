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

namespace pocketmine\item\enchantment;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\Durable;
use pocketmine\item\Elytra;
use pocketmine\item\FishingRod;
use pocketmine\item\FlintSteel;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use function constant;
use function defined;
use function mb_strtoupper;

/**
 * Manages enchantment type data.
 */
class Enchantment{

	public const PROTECTION = 0;
	public const FIRE_PROTECTION = 1;
	public const FEATHER_FALLING = 2;
	public const BLAST_PROTECTION = 3;
	public const PROJECTILE_PROTECTION = 4;
	public const THORNS = 5;
	public const RESPIRATION = 6;
	public const DEPTH_STRIDER = 7;
	public const AQUA_AFFINITY = 8;
	public const SHARPNESS = 9;
	public const SMITE = 10;
	public const BANE_OF_ARTHROPODS = 11;
	public const KNOCKBACK = 12;
	public const FIRE_ASPECT = 13;
	public const LOOTING = 14;
	public const EFFICIENCY = 15;
	public const SILK_TOUCH = 16;
	public const UNBREAKING = 17;
	public const FORTUNE = 18;
	public const POWER = 19;
	public const PUNCH = 20;
	public const FLAME = 21;
	public const INFINITY = 22;
	public const LUCK_OF_THE_SEA = 23;
	public const LURE = 24;
	public const FROST_WALKER = 25;
	public const MENDING = 26;
	public const BINDING = 27;
	public const VANISHING = 28;
	public const IMPALING = 29;
	public const RIPTIDE = 30;
	public const LOYALTY = 31;
	public const CHANNELING = 32;
	public const MULTISHOT = 33;
	public const PIERCING = 34;
	public const QUICK_CHARGE = 35;
	public const SOUL_SPEED = 36;

	public const RARITY_COMMON = 10;
	public const RARITY_UNCOMMON = 5;
	public const RARITY_RARE = 2;
	public const RARITY_MYTHIC = 1;

	public const SLOT_NONE = 0x0;
	public const SLOT_ALL = 0xffff;
	public const SLOT_ARMOR = self::SLOT_HEAD | self::SLOT_TORSO | self::SLOT_LEGS | self::SLOT_FEET;
	public const SLOT_HEAD = 0x1;
	public const SLOT_TORSO = 0x2;
	public const SLOT_LEGS = 0x4;
	public const SLOT_FEET = 0x8;
	public const SLOT_SWORD = 0x10;
	public const SLOT_BOW = 0x20;
	public const SLOT_TOOL = self::SLOT_HOE | self::SLOT_SHEARS | self::SLOT_FLINT_AND_STEEL;
	public const SLOT_HOE = 0x40;
	public const SLOT_SHEARS = 0x80;
	public const SLOT_FLINT_AND_STEEL = 0x100;
	public const SLOT_DIG = self::SLOT_AXE | self::SLOT_PICKAXE | self::SLOT_SHOVEL;
	public const SLOT_AXE = 0x200;
	public const SLOT_PICKAXE = 0x400;
	public const SLOT_SHOVEL = 0x800;
	public const SLOT_FISHING_ROD = 0x1000;
	public const SLOT_CARROT_STICK = 0x2000;
	public const SLOT_ELYTRA = 0x4000;
	public const SLOT_TRIDENT = 0x8000;
	public const SLOT_WEARABLE = 0x16000;
	public const SLOT_SHIELD = 0x32000;
	public const SLOT_CROSSBOW = 0x64000;

	/**
	 * @var \SplFixedArray|Enchantment[]
	 * @phpstan-var \SplFixedArray<Enchantment>
	 */
	protected static $enchantments;

	public static function init() : void{
		self::$enchantments = new \SplFixedArray(256);

		self::registerEnchantment(new ProtectionEnchantment(self::PROTECTION, "%enchantment.protect.all", self::RARITY_COMMON, self::SLOT_ARMOR, self::SLOT_NONE, 4, 0.75, null));
		self::registerEnchantment(new ProtectionEnchantment(self::FIRE_PROTECTION, "%enchantment.protect.fire", self::RARITY_UNCOMMON, self::SLOT_ARMOR, self::SLOT_NONE, 4, 1.25, [
			EntityDamageEvent::CAUSE_FIRE,
			EntityDamageEvent::CAUSE_FIRE_TICK,
			EntityDamageEvent::CAUSE_LAVA
			//TODO: check fireballs
		]));
		self::registerEnchantment(new ProtectionEnchantment(self::FEATHER_FALLING, "%enchantment.protect.fall", self::RARITY_UNCOMMON, self::SLOT_FEET, self::SLOT_NONE, 4, 2.5, [
			EntityDamageEvent::CAUSE_FALL
		]));
		self::registerEnchantment(new ProtectionEnchantment(self::BLAST_PROTECTION, "%enchantment.protect.explosion", self::RARITY_RARE, self::SLOT_ARMOR, self::SLOT_NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
			EntityDamageEvent::CAUSE_ENTITY_EXPLOSION
		]));
		self::registerEnchantment(new ProtectionEnchantment(self::PROJECTILE_PROTECTION, "%enchantment.protect.projectile", self::RARITY_UNCOMMON, self::SLOT_ARMOR, self::SLOT_NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_PROJECTILE
		]));
		self::registerEnchantment(new ThornsEnchantment(self::THORNS, "%enchantment.thorns", self::RARITY_MYTHIC, self::SLOT_TORSO, self::SLOT_HEAD | self::SLOT_LEGS | self::SLOT_FEET, 3));
		self::registerEnchantment(new RespirationEnchantment(self::RESPIRATION, "%enchantment.oxygen", self::RARITY_RARE, self::SLOT_HEAD, self::SLOT_NONE, 3));

		self::registerEnchantment(new SharpnessEnchantment(self::SHARPNESS, "%enchantment.damage.all", self::RARITY_COMMON, self::SLOT_SWORD, self::SLOT_AXE, 5));
		self::registerEnchantment(new KnockbackEnchantment(self::KNOCKBACK, "%enchantment.knockback", self::RARITY_UNCOMMON, self::SLOT_SWORD, self::SLOT_NONE, 2));
		self::registerEnchantment(new FireAspectEnchantment(self::FIRE_ASPECT, "%enchantment.fire", self::RARITY_RARE, self::SLOT_SWORD, self::SLOT_NONE, 2));

		self::registerEnchantment(new EfficiencyEnchantment(self::EFFICIENCY, "%enchantment.digging", self::RARITY_COMMON, self::SLOT_DIG, self::SLOT_SHEARS, 5));
		self::registerEnchantment(new SilkTouchEnchantment(self::SILK_TOUCH, "%enchantment.untouching", self::RARITY_MYTHIC, self::SLOT_DIG, self::SLOT_SHEARS, 1));
		self::registerEnchantment(new UnbreakingEnchantment(self::UNBREAKING, "%enchantment.durability", self::RARITY_UNCOMMON, self::SLOT_DIG | self::SLOT_ARMOR | self::SLOT_FISHING_ROD | self::SLOT_BOW | self::SLOT_CROSSBOW | self::SLOT_SWORD | self::SLOT_TRIDENT, self::SLOT_TOOL | self::SLOT_CARROT_STICK | self::SLOT_ELYTRA | self::SLOT_SHIELD, 3));

		self::registerEnchantment(new PowerEnchantment(self::POWER, "%enchantment.arrowDamage", self::RARITY_COMMON, self::SLOT_BOW, self::SLOT_NONE, 5));
		self::registerEnchantment(new PunchEnchantment(self::PUNCH, "%enchantment.arrowKnockback", self::RARITY_RARE, self::SLOT_BOW, self::SLOT_NONE, 2));
		self::registerEnchantment(new FlameEnchantment(self::FLAME, "%enchantment.arrowFire", self::RARITY_RARE, self::SLOT_BOW, self::SLOT_NONE, 1));
		self::registerEnchantment(new InfinityEnchantment(self::INFINITY, "%enchantment.arrowInfinite", self::RARITY_MYTHIC, self::SLOT_BOW, self::SLOT_NONE, 1));

		self::registerEnchantment(new MendingEnchantment(self::MENDING, "%enchantment.mending", self::RARITY_RARE, self::SLOT_NONE, self::SLOT_ALL, 1));

		self::registerEnchantment(new VanishingEnchantment(self::VANISHING, "%enchantment.curse.vanishing", self::RARITY_MYTHIC, self::SLOT_NONE, self::SLOT_ALL, 1));
		self::registerEnchantment(new BindingEnchantment(self::BINDING, "%enchantment.curse.binding", self::RARITY_MYTHIC, self::SLOT_NONE, self::SLOT_ALL, 1));
		self::registerEnchantment(new AquaAffinityEnchantment(self::AQUA_AFFINITY, "%enchantment.aqua_affinity", self::RARITY_RARE, self::SLOT_HEAD, self::SLOT_NONE, 1));
		self::registerEnchantment(new BaneOfArthropodsEnchantment(self::BANE_OF_ARTHROPODS, "%enchantment.bane_of_arthropods", self::RARITY_UNCOMMON, self::SLOT_SWORD, self::SLOT_AXE, 5));
		self::registerEnchantment(new ChannelingEnchantment(self::CHANNELING, "%enchantment.channeling", self::RARITY_MYTHIC, self::SLOT_TRIDENT, self::SLOT_NONE, 1));
		self::registerEnchantment(new RiptideEnchantment(self::RIPTIDE, "%enchantment.riptide", self::RARITY_RARE, self::SLOT_TRIDENT, self::SLOT_NONE, 3));
		self::registerEnchantment(new DepthStriderEnchantment(self::DEPTH_STRIDER, "%enchantment.depth_strider", self::RARITY_RARE, self::SLOT_FEET, self::SLOT_NONE, 3));
		self::registerEnchantment(new EfficiencyEnchantment(self::EFFICIENCY, "%enchantment.efficiency", self::RARITY_COMMON, self::SLOT_PICKAXE | self::SLOT_AXE | self::SLOT_SHOVEL, self::SLOT_NONE, 5));
		self::registerEnchantment(new FlameEnchantment(self::FLAME, "%enchantment.flame", self::RARITY_RARE, self::SLOT_BOW, self::SLOT_NONE, 1));

		self::registerEnchantment(new LootEnchantment(self::FORTUNE, "%enchantment.fortune", self::RARITY_RARE, self::SLOT_PICKAXE | self::SLOT_SHOVEL | self::SLOT_AXE, self::SLOT_NONE, 3));
		self::registerEnchantment(new LootEnchantment(self::LOOTING, "%enchantment.looting", self::RARITY_RARE, self::SLOT_SWORD, self::SLOT_NONE, 3));
		self::registerEnchantment(new LootEnchantment(self::LUCK_OF_THE_SEA, "%enchantment.luck_of_the_sea", self::RARITY_RARE, self::SLOT_FISHING_ROD, self::SLOT_NONE, 3));

		self::registerEnchantment(new LureEnchantment(self::LURE, "%enchantment.lure", self::RARITY_RARE, self::SLOT_FISHING_ROD, self::SLOT_NONE, 3));
		self::registerEnchantment(new SmiteEnchantment(self::SMITE, "%enchantment.smite", self::RARITY_UNCOMMON, self::SLOT_SWORD, self::SLOT_AXE, 5));
		self::registerEnchantment(new FrostWalkerEnchantment(self::FROST_WALKER, "%enchantment.frost_walker", self::RARITY_RARE, self::SLOT_NONE, self::SLOT_FEET, 2));
		self::registerEnchantment(new LoyaltyEnchantment(self::LOYALTY, "%enchantment.loyalty", self::RARITY_UNCOMMON, self::SLOT_TRIDENT, self::SLOT_NONE, 3));
		self::registerEnchantment(new ImpalingEnchantment(self::IMPALING, "%enchantment.impaling", self::RARITY_RARE, self::SLOT_TRIDENT, self::SLOT_NONE, 5));
	}

	/**
	 * Registers an enchantment type.
	 */
	public static function registerEnchantment(Enchantment $enchantment) : void{
		self::$enchantments[$enchantment->getId()] = clone $enchantment;
	}

	public static function getEnchantment(int $id) : ?Enchantment{
		if($id < 0 or $id >= self::$enchantments->getSize()){
			return null;
		}
		return self::$enchantments[$id] ?? null;
	}

	public static function getEnchantmentByName(string $name) : ?Enchantment{
		$const = Enchantment::class . "::" . mb_strtoupper($name);
		if(defined($const)){
			return self::getEnchantment(constant($const));
		}
		return null;
	}

	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var int */
	private $rarity;
	/** @var int */
	private $primaryItemFlags;
	/** @var int */
	private $secondaryItemFlags;
	/** @var int */
	private $maxLevel;

	public function __construct(int $id, string $name, int $rarity, int $primaryItemFlags, int $secondaryItemFlags, int $maxLevel){
		$this->id = $id;
		$this->name = $name;
		$this->rarity = $rarity;
		$this->primaryItemFlags = $primaryItemFlags;
		$this->secondaryItemFlags = $secondaryItemFlags;
		$this->maxLevel = $maxLevel;
	}

	/**
	 * Returns the ID of this enchantment as per Minecraft PE
	 */
	public function getId() : int{
		return $this->id;
	}

	/**
	 * Returns a translation key for this enchantment's name.
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * Returns an int constant indicating how rare this enchantment type is.
	 */
	public function getRarity() : int{
		return $this->rarity;
	}

	/**
	 * Returns a bitset indicating what item types can have this item applied from an enchanting table.
	 */
	public function getPrimaryItemFlags() : int{
		return $this->primaryItemFlags;
	}

	/**
	 * Returns a bitset indicating what item types cannot have this item applied from an enchanting table, but can from
	 * an anvil.
	 */
	public function getSecondaryItemFlags() : int{
		return $this->secondaryItemFlags;
	}

	/**
	 * Returns whether this enchantment can apply to the item type from an enchanting table.
	 */
	public function hasPrimaryItemType(int $flag) : bool{
		return ($this->primaryItemFlags & $flag) !== 0;
	}

	/**
	 * Returns whether this enchantment can apply to the item type from an anvil, if it is not a primary item.
	 */
	public function hasSecondaryItemType(int $flag) : bool{
		return ($this->secondaryItemFlags & $flag) !== 0;
	}

	/**
	 * Returns the maximum level of this enchantment that can be found on an enchantment table.
	 */
	public function getMaxLevel() : int{
		return $this->maxLevel;
	}

	/**
	 * Determines if this enchantment can be applied to a specific Item.
	 */
	public function canApply(Item $item) : bool{
		if(($item instanceof Shears or $item instanceof FlintSteel or $item instanceof Hoe) and $item instanceof Durable and $item->getMaxDurability() >= 0){
			return $this->hasPrimaryItemType(self::SLOT_TOOL) or $this->hasSecondaryItemType(self::SLOT_TOOL);
		}elseif($item instanceof Armor){
			if($item->getArmorSlot() === Armor::SLOT_HELMET){
				return $this->hasPrimaryItemType(self::SLOT_HEAD) or $this->hasSecondaryItemType(self::SLOT_HEAD);
			}elseif($item->getArmorSlot() === Armor::SLOT_CHESTPLATE){
				return $this->hasPrimaryItemType(self::SLOT_TORSO) or $this->hasSecondaryItemType(self::SLOT_TORSO);
			}elseif($item->getArmorSlot() === Armor::SLOT_LEGGINGS){
				return $this->hasPrimaryItemType(self::SLOT_LEGS) or $this->hasSecondaryItemType(self::SLOT_LEGS);
			}elseif($item->getArmorSlot() === Armor::SLOT_BOOTS){
				return $this->hasPrimaryItemType(self::SLOT_FEET) or $this->hasSecondaryItemType(self::SLOT_FEET);
			}
		}else{
			if($item instanceof Sword){
				return $this->hasPrimaryItemType(self::SLOT_SWORD) or $this->hasSecondaryItemType(self::SLOT_SWORD);
			}elseif($item instanceof Pickaxe or $item instanceof Shovel or $item instanceof Axe){
				return $this->hasPrimaryItemType(self::SLOT_DIG) or $this->hasSecondaryItemType(self::SLOT_DIG);
			}elseif($item instanceof Bow){
				return $this->hasPrimaryItemType(self::SLOT_BOW) or $this->hasSecondaryItemType(self::SLOT_BOW);
			}elseif($item instanceof FishingRod){
				return $this->hasPrimaryItemType(self::SLOT_FISHING_ROD) or $this->hasSecondaryItemType(self::SLOT_FISHING_ROD);
			}elseif($item instanceof Elytra){
				return $this->hasPrimaryItemType(self::SLOT_ELYTRA) or $this->hasSecondaryItemType(self::SLOT_ELYTRA);
			}elseif($item->getId() === Item::SKULL or $item->getId() === Item::PUMPKIN){
				return $this->hasPrimaryItemType(self::SLOT_WEARABLE) or $this->hasSecondaryItemType(self::SLOT_WEARABLE);
			}elseif($item->getId() === Item::SHIELD){
				return $this->hasPrimaryItemType(self::SLOT_SHIELD) or $this->hasSecondaryItemType(self::SLOT_SHIELD);
			}elseif($item->getId() === Item::TRIDENT){
				return $this->hasPrimaryItemType(self::SLOT_TRIDENT) or $this->hasSecondaryItemType(self::SLOT_TRIDENT);
			}
		}

		return false;
	}

	public function canApplyTogether(Enchantment $enchantment) : bool{
		return $enchantment !== $this;
	}

	public function getMinEnchantAbility(int $level) : int{
		return 1 + $level * 10;
	}

	public function getMaxEnchantAbility(int $level) : int{
		return $this->getMinEnchantAbility($level) + 5;
	}

	public function onHurtEntity(Entity $attacker, Entity $victim, Item $item, int $enchantmentLevel) : void{

	}
}

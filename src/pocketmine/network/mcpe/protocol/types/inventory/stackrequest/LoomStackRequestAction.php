<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;

final class LoomStackRequestAction extends ItemStackRequestAction {

    public const ID = ItemStackRequestActionType::CRAFTING_LOOM;

    /**
     * @param string $patternId
     */
    public function __construct(
        private string $patternId
    ) {}

    /**
     * @return string
     */
    public function getPatternId(): string {
        return $this->patternId;
    }

    public static function getTypeId(): int {
        return self::ID;
    }

    public static function read(NetworkBinaryStream $in) : self{
        return new self($in->getString());
    }

    public function write(NetworkBinaryStream $out): void {
        $out->putString($this->patternId);
    }
}
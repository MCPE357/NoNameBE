<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;

final class PlaceIntoBundleStackRequestAction extends ItemStackRequestAction {

    public const ID = ItemStackRequestActionType::PLACE_INTO_BUNDLE;

    public static function getTypeId(): int {
        return self::ID;
    }

    final public function __construct(
        private int $count,
        private ItemStackRequestSlotInfo $source,
        private ItemStackRequestSlotInfo $destination
    ){}

    final public function getCount() : int{ return $this->count; }

    final public function getSource() : ItemStackRequestSlotInfo{ return $this->source; }

    final public function getDestination() : ItemStackRequestSlotInfo{ return $this->destination; }

    public static function read(NetworkBinaryStream $in): self {
        $count = $in->getByte();
        $src = ItemStackRequestSlotInfo::read($in);
        $dst = ItemStackRequestSlotInfo::read($in);
        return new self($count, $src, $dst);
    }

    public function write(NetworkBinaryStream $out): void {
        $out->putByte($this->count);
        $this->source->write($out);
        $this->destination->write($out);
    }
}
<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope\editor;

use Exception;
use InvalidStateException;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;

class Edit implements \Serializable{

    //From BuilderTools BlockArray (slightly edited for own use)
    //Github: https://github.com/CzechPMDevs/BuilderTools

    /** @var bool */
    protected bool $detectDuplicates;

    /** @var int[] */
    public array $coords = [];
    /** @var int[] */
    public array $blocks = [];

    /** @var Level|null */
    protected ?Level $level = null;

    /** @var bool */
    protected bool $isCompressed = false;
    /** @var string */
    public string $compressedCoords;

    /** @var string */
    public string $compressedBlocks;

    /** @var int */
    public int $offset = 0;

    protected int $lastHash, $lastBlockHash;

    public function __construct(?Level $level, bool $detectDuplicates = false) {
        $this->detectDuplicates = $detectDuplicates;
        $this->level = $level;
    }

    public function addBlock(Vector3 $vector3, int $id, int $meta): Edit {
        return $this->addBlockAt($vector3->getFloorX(), $vector3->getFloorY(), $vector3->getFloorZ(), $id, $meta);
    }

    public function addBlockAt(int $x, int $y, int $z, int $id, int $meta): Edit {
        $this->lastHash = Level::blockHash($x, $y, $z);

        if($this->detectDuplicates && in_array($this->lastHash, $this->coords)) {
            return $this;
        }

        $this->coords[] = $this->lastHash;
        $this->blocks[] = $id << 4 | $meta;

        return $this;
    }

    public function hasNext(): bool {
        return $this->offset < count($this->blocks);
    }

    public function readNext(?int &$x, ?int &$y, ?int &$z, ?int &$id, ?int &$meta): void {
        $this->lastHash = $this->coords[$this->offset];
        $this->lastBlockHash = $this->blocks[$this->offset++];

        Level::getBlockXYZ($this->lastHash, $x, $y, $z);
        $id = $this->lastBlockHash >> 4; $meta = $this->lastBlockHash & 0xf;
    }

    public function addVector3(Vector3 $vector3): Edit {
        $floorX = $vector3->getFloorX();
        $floorY = $vector3->getFloorY();
        $floorZ = $vector3->getFloorZ();

        $edit = new Edit();
        $edit->blocks = $this->blocks;

        foreach ($this->coords as $hash) {
            Level::getBlockXYZ($hash, $x, $y, $z);
            $edit->coords[] = Level::blockHash(($floorX + $x), ($floorY + $y), ($floorZ + $z));
        }

        return $edit;
    }

    public function setLevel(?Level $level): self {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): ?Level {
        return $this->level;
    }

    /**
     * @return int[]
     */
    public function getBlockArray(): array {
        return $this->blocks;
    }

    /**
     * @return int[]
     */
    public function getCoordsArray(): array {
        return $this->coords;
    }

    public function removeDuplicates(): void {

        $blocks = array_combine(array_reverse($this->coords, true), array_reverse($this->blocks, true));
        if($blocks === false) {
            return;
        }

        $this->coords = array_keys($blocks);
        $this->blocks = array_values($blocks);
    }

    public function isCompressed(): bool {
        return $this->isCompressed;
    }

    /**
     * Removes all the blocks whose were checked already
     */
    public function cleanGarbage(): void {
        $this->coords = array_slice($this->coords, $this->offset);
        $this->blocks = array_slice($this->blocks, $this->offset);

        $this->offset = 0;
    }

    public function serialize(): ?string {
        $this->compress();

        $nbt = new CompoundTag("BlockArray");
        $nbt->setByteArray("Coords", $this->compressedCoords);
        $nbt->setByteArray("Blocks", $this->compressedBlocks);
        $nbt->setByte("DuplicateDetection", $this->detectDuplicates ? 1 : 0);

        $stream = new BigEndianNBTStream();
        $buffer = $stream->writeCompressed($nbt);

        if($buffer === false) {
            return null;
        }

        return $buffer;
    }

    public function unserialize($data): void {
        if(!is_string($data)) {
            return;
        }

        /** @var CompoundTag $nbt */
        $nbt = (new BigEndianNBTStream())->readCompressed($data);
        if(!$nbt->hasTag("Coords", ByteArrayTag::class) || !$nbt->hasTag("Blocks", ByteArrayTag::class) || !$nbt->hasTag("DuplicateDetection", ByteTag::class)) {
            return;
        }

        $this->compressedCoords = $nbt->getByteArray("Coords");
        $this->compressedBlocks = $nbt->getByteArray("Blocks");
        $this->detectDuplicates = $nbt->getByte("DuplicateDetection") == 1;

        $this->decompress();
    }

    public function compress(bool $cleanDecompressed = true): void {
        /** @phpstan-var string|false $coords */
        $coords = pack("q*", ...$this->coords);
        /** @phpstan-var string|false $blocks */
        $blocks = pack("N*", ...$this->blocks);

        if($coords === false || $blocks === false) {
            throw new InvalidStateException("Error whilst compressing");
        }

        $this->compressedCoords = $coords;
        $this->compressedBlocks = $blocks;

        if($cleanDecompressed) {
            $this->coords = [];
            $this->blocks = [];
        }
    }

    public function decompress(bool $cleanCompressed = true): void {
        /** @phpstan-var int[]|false $coords */
        $coords = unpack("q*", $this->compressedCoords);
        /** @phpstan-var int[]|false $coords */
        $blocks = unpack("N*", $this->compressedBlocks);

        if($coords === false || $blocks === false) {
            throw new InvalidStateException("Error whilst decompressing");
        }

        $this->coords = array_values($coords);
        $this->blocks = array_values($blocks);

        if($cleanCompressed) {
            unset($this->compressedCoords);
            unset($this->compressedBlocks);
        }
    }
}
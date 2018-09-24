<?php

declare(strict_types=1);

namespace redstonex\block;

use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use redstonex\RedstoneX;

/**
 * Class Redstone
 * @package redstonex\block
 */
class Redstone extends Transparent {

    /** @var int $id */
    protected $id = RedstoneX::REDSTONE_WIRE;

    /** @var $meta */
    public $meta = 0;

    public $ticksSinceSignalUpdate = 0;

    /**
     * Redstone constructor.
     * @param int $meta
     */
    public function __construct($meta = 0) {
        parent::__construct($this->id, $meta, $this->getName(), RedstoneX::REDSTONE_ITEM);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return "Redstone Wire";
    }

    /**
     * @param int $type
     * @return int
     */
    public function onUpdate(int $type) {
        //$this->activateRedstone();
        //$this->deactivateRedstone();

        $this->activateRedstone();

        return $type;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $facePos, Player $player = \null) : bool{
        $below = $this->getSide(Vector3::SIDE_DOWN);

        $foundRedstone = \false;
        for ($x = $this->getX() - 1; $x <= $this->getX() + 1; $x++) {
            for ($y = $this->getY() - 1; $y <= $this->getY() + 1; $y++) {
              $block = $this->getLevel()->getBlock(new Vector3($x, $y, $this->getZ()));

              RedstoneX::consoleDebug(get_class($block));
              //if (get_class($block) == "Redstone") {
            }
        }


        if($blockClicked->isTransparent() === \false and $face !== Vector3::SIDE_DOWN){
            $faces = [
                Vector3::SIDE_UP => 5,
                Vector3::SIDE_NORTH => 4,
                Vector3::SIDE_SOUTH => 3,
                Vector3::SIDE_WEST => 2,
                Vector3::SIDE_EAST => 1
            ];
            $this->meta = $faces[$face];
            $this->getLevel()->setBlock($blockReplace, $this, \true, \true);

            return \true;
        }elseif($below->isTransparent() === \false or $below->getId() === self::FENCE or $below->getId() === self::COBBLESTONE_WALL or $below->getId() === self::REDSTONE_WIRE){
            $this->meta = 0;
            $this->getLevel()->setBlock($blockReplace, $this, \true, \true);

            return \true;
        }

        return \false;
    }

    public function onScheduledUpdate() : void{
      $this->$ticksSinceSignalUpdate += 1;
      if($this->$ticksSinceSignalUpdate > 1){
        RedstoneX::setInactive($this);
      }

      $this->activateRedstone();

      RedstoneX::consoleDebug($signalStrength);
    }

    public function setSignalStrength(int $strength) : void{
      if($this->$ticksSinceSignalUpdate >= 1 || $strength > $signalStrength){
        $this->$ticksSinceSignalUpdate = 0;
        RedstoneX::setRedstoneActivity($this, $signalStrength);
      }
    }

    public function activateRedstone($trigger = \false){
      RedstoneX::consoleDebug("§aUpdating neighbours...");

      $babyStrength = RedstoneX::getRedstoneActivity($this)-1;
      if($babyStrength < 0){
        return;
      }
      // We check each neighbouring block
      for ($x = $this->getX() - 1; $x <= $this->getX() + 1; $x++) {
          for ($y = $this->getY() - 1; $y <= $this->getY() + 1; $y++) {
              $block = $this->getLevel()->getBlock(new Vector3($x, $y, $this->getZ()));
              if ($block != $trigger and $block != $this and $block instanceof Redstone) {
                  RedstoneX::consoleDebug("§aFound one! setting s. strength to $babyStrength");
                  RedstoneX::setRedstoneActivity($block,$babyStrength);
                  $block->activateRedstone($this);
              }
            }
        }
    }

    /**
    * @return int 0-15
    */
    public function getLightLevel() : int{
      return RedstoneX::getRedstoneActivity($this);
    }

    /*public function deactivateRedstone() {
        RedstoneX::consoleDebug("§aDEACTIVING (???)");

        $signal = false;
        for ($x = $this->getX() - 1; $x <= $this->getX() + 1; $x++) {
            for ($y = $this->getY() - 1; $y <= $this->getY() + 1; $y++) {
                if ($x != $this->getX()) {
                    $block = $this->getLevel()->getBlock(new Vector3($x, $y, $this->getZ()));
                    if (RedstoneX::isActive($block)) {
                        $signal = true;
                    }
                }
            }
        }

        if (RedstoneX::isActive($this->getLevel()->getBlock(new Vector3($this->getX(), $this->getY() + 1, $this->getZ())), $this->getDamage())) {
            $signal = true;
        }

        for ($z = $this->getZ() - 1; $z <= $this->getZ() + 1; $z++) {
            for ($y = $this->getY() - 1; $y <= $this->getY() + 1; $y++) {
                if ($z != $this->getZ()) {
                    $block = $this->getLevel()->getBlock(new Vector3($this->getX(), $this->getY(), $z));
                    if (RedstoneX::isActive($block)) {
                        $signal = true;
                    }
                }
            }
        }

        if ($signal === false) {
            if (RedstoneX::isActive($this)) {
                RedstoneX::setInactive($this);
            }
            RedstoneX::consoleDebug("§aDEACTIVED BLOCK!");
        }
    }

    public function activateRedstone() {

        if ($this->meta < 1) return;

        RedstoneX::consoleDebug("ACTIVATING (redstone wire by redstone wire)");

        for ($x = $this->getX() - 1; $x <= $this->getX() + 1; $x++) {
            for ($y = $this->getY() - 1; $y <= $this->getY() + 1; $y++) {
                if ($x != $this->getX()) {
                    $block = $this->getLevel()->getBlock(new Vector3($x, $y, $this->getZ()));
                    if ($block->getId() == RedstoneX::REDSTONE_WIRE || $block instanceof Redstone) {
                        RedstoneX::setActive($block, intval($this->getDamage() - 1));
                        RedstoneX::consoleDebug("ACTIVATING found");
                    } else {
                        RedstoneX::consoleDebug("nothing found.");
                    }
                }
            }
        }

        /* WHY ?
         *
         * for ($y = $this->getY(); $y <= $this->getY() + 1; $y++) {
            if ($y != $this->getY()) {
                $block = $this->getLevel()->getBlock(new Vector3($this->getX(), $y, $this->getZ()));
                if ($block->getId() == RedstoneX::REDSTONE_WIRE || $block instanceof Redstone) {
                    RedstoneX::setActive($block, intval($this->getDamage() - 1));
                    RedstoneX::consoleDebug("ACTIVATING found");
                } else {
                    RedstoneX::consoleDebug("nothing found.");
                }
            }
        }

        for ($z = $this->getZ() - 1; $z <= $this->getZ() + 1; $z++) {
            for ($y = $this->getY() - 1; $y <= $this->getY() + 1; $y++) {
                if ($z != $this->getZ()) {
                    $block = $this->getLevel()->getBlock(new Vector3($this->getX(), $y, $z));
                    if ($block->getId() == RedstoneX::REDSTONE_WIRE || $block instanceof Redstone) {
                        RedstoneX::setActive($block, intval($this->getDamage() - 1));
                        RedstoneX::consoleDebug("ACTIVATING found");
                    } else {
                        RedstoneX::consoleDebug("nothing found.");
                    }
                }
            }
        }
    }*/

    public function getHardness(): float {
        return 0.2;
    }
}

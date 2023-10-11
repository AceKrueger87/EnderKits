<?php

declare(strict_types=1);

namespace Terpz710\EnderKits;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\scheduler\TaskHandler;
use Terpz710\EnderKits\Command\KitCommand;
use Terpz710\EnderKits\Task\CoolDownTask;

class Main extends PluginBase {

    /** @var TaskHandler|null */
    private $coolDownTaskHandler;

    public function onEnable(): void {
        $this->getServer()->getCommandMap()->register("kit", new KitCommand($this, new Config($this->getDataFolder() . "kits.yml", Config::YAML)));
        $this->coolDownTaskHandler = $this->getScheduler()->scheduleRepeatingTask(new CoolDownTask($this), 20 * 60);
    }
}

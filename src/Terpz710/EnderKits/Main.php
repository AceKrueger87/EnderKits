<?php

declare(strict_types=1);

namespace Terpz710\EnderKits;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Terpz710\EnderKits\Task\CoolDownTask;
use Terpz710\EnderKits\Command\KitCommand;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->getServer()->getCommandMap()->register("kit", new KitCommand($this, new Config($this->getDataFolder() . "kits.yml", Config::YAML)));
        $this->getScheduler()->scheduleRepeatingTask(new CoolDownTask($this, new Config($this->getDataFolder() . "kits.yml", Config::YAML)), 20 * 60);
    }
}

<?php

declare(strict_types=1);

namespace Terpz710\EnderKits;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Terpz710\EnderKits\Task\CoolDownTask;
use Terpz710\EnderKits\Command\KitCommand;

class Main extends PluginBase {

    public function onEnable(): void {
        $kitsConfig = new Config($this->getDataFolder() . "kits.yml", Config::YAML);
        $kitCommand = new KitCommand($this, $kitsConfig);
        $this->getServer()->getCommandMap()->register("kit", $kitCommand);

        $cooldownsConfig = new Config($this->getDataFolder() . "cooldowns.yml", Config::YAML);
        $coolDownTask = new CoolDownTask($this, $kitsConfig, $cooldownsConfig);
        $this->getScheduler()->scheduleRepeatingTask($coolDownTask, 20 * 60);
    }
}

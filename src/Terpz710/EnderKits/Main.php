<?php

declare(strict_types=1);

namespace Terpz710\EnderKits;

use pocketmine\plugin\PluginBase;
use Terpz710\EnderKits\Command\KitCommand;
use Terpz710\EnderKits\Command\KitsCommand;
use Terpz710\EnderKits\Command\VIPCommand;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->getServer()->getCommandMap()->register("kit", new Command\KitCommand($this));
        $this->getServer()->getCommandMap()->register("kits", new Command\KitsCommand($this));
        $this->getServer()->getCommandMap()->register("VIPKITS710", new Command\VIPCommand($this));
        $this->saveResource("kits.yml");
    }
}

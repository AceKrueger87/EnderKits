<?php

declare(strict_types=1);

namespace Terpz710\EnderKits;

use pocketmine\plugin\PluginBase;
use Terpz710\EnderKits\Command\KitCommand;
use Terpz710\EnderKits\Task\CoolDownTask;

class EnderKitsPlugin extends PluginBase {
    
    public function onEnable(): void {
        $this->getServer()->getCommandMap()->register("kit", new KitCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new CoolDownTask($this), 20 * 60);
    }
}

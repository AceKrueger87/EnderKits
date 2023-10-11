<?php

declare(strict_types=1);

namespace Terpz710\EnderKits\Task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\Plugin;

class CoolDownTask extends Task {

    /** @var Config */
    private $config;
    /** @var Plugin */
    private $plugin;

    public function __construct(Plugin $plugin, Config $config) {
        $this->plugin = $plugin;
        $this->config = $config;
    }

    public function onRun(): void {
        $kits = $this->config->getAll();
        foreach ($kits as $kitName => $kitData) {
            if (!is_array($kitData) || !isset($kitData["cooldown"])) {
                continue;
            }

            $cooldown = (int) $kitData["cooldown"];
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                $playerName = $player->getName();
                if ($this->config->exists("cooldowns.$playerName.$kitName")) {
                    $lastUsage = $this->config->get("cooldowns.$playerName.$kitName");
                    $timePassed = time() - $lastUsage;
                    if ($timePassed < $cooldown) {
                        $remainingTime = $cooldown - $timePassed;
                        $player->sendMessage("Kit $kitName is on cooldown. You can use it again in $remainingTime seconds.");
                    } else {
                        $this->config->remove("cooldowns.$playerName.$kitName");
                        $this->config->save();
                    }
                }
            }
        }
    }
}

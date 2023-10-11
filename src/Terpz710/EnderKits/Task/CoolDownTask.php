<?php

declare(strict_types=1);

namespace Terpz710\EnderKits\Task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class CoolDownTask extends Task {

    /** @var PluginBase */
    private $plugin;
    /** @var Config */
    private $kitsConfig;
    /** @var Config */
    private $cooldownsConfig;

    public function __construct(PluginBase $plugin, Config $kitsConfig, Config $cooldownsConfig) {
        $this->plugin = $plugin;
        $this->kitsConfig = $kitsConfig;
        $this->cooldownsConfig = $cooldownsConfig;
    }

    public function onRun(): void {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $this->processKitCoolDowns($player);
        }
    }

    private function processKitCoolDowns(Player $player) {
        foreach ($this->kitsConfig->getAll() as $kitName => $kitData) {
            if (isset($kitData["cooldown"])) {
                $cooldown = (int) $kitData["cooldown"];
                if ($this->isKitOnCooldown($player, $kitName, $cooldown)) {
                    $remainingTime = $this->getRemainingCooldownTime($player, $kitName, $cooldown);
                    $player->sendMessage("Kit $kitName is on cooldown. You can use it again in $remainingTime seconds.");
                }
            }
        }
    }

    private function isKitOnCooldown(Player $player, string $kitName, int $cooldown): bool {
        $playerName = $player->getName();
        $cooldownData = $this->cooldownsConfig->get("$playerName.$kitName", null);
        if ($cooldownData !== null) {
            $lastUsage = $cooldownData["timestamp"];
            $timePassed = time() - $lastUsage;
            return $timePassed < $cooldown;
        }
        return false;
    }

    private function getRemainingCooldownTime(Player $player, string $kitName, int $cooldown): int {
        $playerName = $player->getName();
        $cooldownData = $this->cooldownsConfig->get("$playerName.$kitName", null);
        if ($cooldownData !== null) {
            $lastUsage = $cooldownData["timestamp"];
            $timePassed = time() - $lastUsage;
            $remainingTime = $cooldown - $timePassed;
            return max(0, $remainingTime);
        }
        return 0;
    }
}

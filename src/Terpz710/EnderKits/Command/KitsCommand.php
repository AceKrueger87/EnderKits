<?php

namespace Terpz710\EnderKits\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class KitsCommand extends Command implements PluginOwned {

    /** @var Plugin */
    private $plugin;

    public function __construct(Plugin $plugin) {
        parent::__construct("kits", "List available kits");
        $this->plugin = $plugin;
        $this->setPermission("enderkits.cmd");
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            $kitConfig = $this->loadKitConfig();

            if ($kitConfig === null) {
                $sender->sendMessage(TextFormat::RED . "Kit configuration is missing or invalid.");
                return true;
            }

            $kitsList = implode(", ", array_keys($kitConfig));
            $sender->sendMessage(TextFormat::GREEN . "Available kits: $kitsList");
        } else {
            $sender->sendMessage("This command can only be used in-game.");
        }
        return true;
    }

    private function loadKitConfig() {
        $configPath = $this->plugin->getDataFolder() . "kits.yml";
        if (file_exists($configPath)) {
            $config = new Config($configPath, Config::YAML);
            $kitData = $config->get("kits");

            if ($kitData !== null && is_array($kitData)) {
                return $kitData;
            }
        }
        return [];
    }
}

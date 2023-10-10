<?php

declare(strict_types=1);

namespace Terpz710\EnderKits\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;

class KitCommand extends Command implements PluginOwned {

    /** @var Plugin */
    private $plugin;

    public function __construct(Plugin $plugin) {
        parent::__construct("kit", "Get a kit");
        $this->plugin = $plugin;
        $this->setPermission("enderkits.cmd");
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            $kitConfig = yaml_parse_file($this->plugin->getDataFolder() . "kits.yml");

            if (isset($kitConfig["default"])) {
                $kitItems = $kitConfig["default"];
                
                $items = [];
                foreach ($kitItems as $itemName => $itemData) {
                    $item = StringToItemParser::getInstance()->parse($itemName);
                    if ($item === null) {
                        $item = VanillaItems::AIR();
                    }
                    if (isset($itemData["enchantments"])) {
                    foreach ($itemData["enchantments"] as $enchantmentName => $level) {
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                    $item->addEnchantment($enchantment->getId(), $level);
                    }
                }
            }
                    $items[] = $item;
                }
                
                $sender->getInventory()->setContents($items);
                $sender->sendMessage(TextFormat::GREEN . "You received the Kit!");
            } else {
                $sender->sendMessage(TextFormat::RED . "The 'default' kit is not configured.");
            }
        } else {
            $sender->sendMessage("This command can only be used in-game.");
        }
        return true;
    }
}

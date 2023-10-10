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
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\EnchantmentInstance;

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
                $kit = $kitConfig["default"];

                $items = [];
                foreach ($kit["items"] as $itemString) {
                    $item = StringToItemParser::getInstance()->parse($itemString);
                    if ($item !== null) {
                        $items[] = $item;
                    }
                }

                $armor = [
                    "helmet" => $kit["helmet"] ?? "",
                    "chestplate" => $kit["chestplate"] ?? "",
                    "leggings" => $kit["leggings"] ?? "",
                    "boots" => $kit["boots"] ?? ""
                ];

                $sender->getInventory()->setContents($items);
                foreach ($armor as $slot => $armorItemString) {
                    $armorItem = StringToItemParser::getInstance()->parse($armorItemString);
                    if ($armorItem !== null) {
                        if ($slot === "helmet") {
                            $sender->getArmorInventory()->setHelmet($armorItem);
                        } elseif ($slot === "chestplate") {
                            $sender->getArmorInventory()->setChestplate($armorItem);
                        } elseif ($slot === "leggings") {
                            $sender->getArmorInventory()->setLeggings($armorItem);
                        } elseif ($slot === "boots") {
                            $sender->getArmorInventory()->setBoots($armorItem);
                        }
                    }
                }

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

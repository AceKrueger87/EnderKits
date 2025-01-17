<?php

declare(strict_types=1);

namespace Terpz710\EnderKits\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\utils\TextFormat;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Config;
use pocketmine\permission\DefaultPermissions;

class KitCommand extends Command implements PluginOwned {

    /** @var Plugin */
    private $plugin;

    public function __construct(Plugin $plugin) {
        parent::__construct("kit", "Get a kit", "/kit <kitName>");
        $this->plugin = $plugin;
        $this->setPermission("enderkits.kit");
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if (count($args) === 1) {
                $kitName = $args[0];
                $kitConfig = $this->loadKitConfig();

                if ($kitConfig === null) {
                    $sender->sendMessage(TextFormat::RED . "Kit configuration is missing or invalid.");
                    return true;
                }

                if (isset($kitConfig[$kitName])) {
                    $kitData = $kitConfig[$kitName];

                    $requiredPermission = $kitData['permissions'] ?? null;

                    if ($requiredPermission === "ALL" || ($requiredPermission === "VIP" && $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR))) {
                        $this->applyKit($sender, $kitData);
                        $sender->sendMessage(TextFormat::GREEN . "You received the kit '$kitName'!");
                    } else {
                        $sender->sendMessage(TextFormat::RED . "You don't have permission to use this kit.");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "Kit '$kitName' does not exist.");
                }
            } else {
                $sender->sendMessage("Usage: /kit <kitName>");
            }
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

    private function applyKit(Player $player, array $kitData) {
        $extraArmor = [];

        if (isset($kitData["armor"])) {
            $armorInventory = $player->getArmorInventory();
            foreach (["helmet", "chestplate", "leggings", "boots"] as $armorType) {
                if (isset($kitData["armor"][$armorType])) {
                    $armorData = $kitData["armor"][$armorType];
                    $itemString = $armorData["item"];
                    $item = StringToItemParser::getInstance()->parse($itemString);

                    if ($item !== null) {
                        if (isset($armorData["enchantments"])) {
                            foreach ($armorData["enchantments"] as $enchantmentName => $level) {
                                $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                                if ($enchantment !== null) {
                                    $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
                                    $item->addEnchantment($enchantmentInstance);
                                }
                            }
                        }

                        $currentArmorItem = $armorInventory->{"get" . ucfirst($armorType)}();
                        if ($currentArmorItem->isNull()) {
                            $armorInventory->{"set" . ucfirst($armorType)}($item);
                        } else {
                            $extraArmor[] = $item;
                        }

                        if (isset($armorData["name"])) {
                            $item->setCustomName(TextFormat::colorize($armorData["name"]));
                        }
                    }
                }
            }
            $player->getInventory()->addItem(...$extraArmor);
        }

        if (isset($kitData["items"])) {
            $items = [];
            $inventory = $player->getInventory();

            foreach ($kitData["items"] as $itemName => $itemData) {
                $item = StringToItemParser::getInstance()->parse($itemName);

                if (isset($itemData["enchantments"])) {
                    foreach ($itemData["enchantments"] as $enchantmentName => $level) {
                        $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                        if ($enchantment !== null) {
                            $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
                            $item->addEnchantment($enchantmentInstance);
                        }
                    }
                }

                if ($item !== null) {
                    $item->setCount((int) $itemData["quantity"]);
                }
                if (isset($itemData["name"])) {
                    $item->setCustomName(TextFormat::colorize($itemData["name"]));
                }

                $items[] = $item;
            }

            $inventory->addItem(...$items);
        }
    }
}

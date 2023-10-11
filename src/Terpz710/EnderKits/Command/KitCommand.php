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
                if (isset($kitConfig["default"]["armor"])) {
                    $armorInventory = $sender->getArmorInventory();
                    $extraArmor = [];

                    foreach (["helmet", "chestplate", "leggings", "boots"] as $armorType) {
                        if (isset($kitConfig["default"]["armor"][$armorType])) {
                            $armorData = $kitConfig["default"]["armor"][$armorType];
                            $item = StringToItemParser::getInstance()->parse($armorData["item"]);
                            if ($item !== null) {
                                switch ($armorType) {
                                    case "helmet":
                                        if ($armorInventory->getHelmet()->isNull()) {
                                            $armorInventory->setHelmet($item);
                                        } else {
                                            $extraArmor[] = $item;
                                        }
                                        break;
                                    case "chestplate":
                                        if ($armorInventory->getChestplate()->isNull()) {
                                            $armorInventory->setChestplate($item);
                                        } else {
                                            $extraArmor[] = $item;
                                        }
                                        break;
                                    case "leggings":
                                        if ($armorInventory->getLeggings()->isNull()) {
                                            $armorInventory->setLeggings($item);
                                        } else {
                                            $extraArmor[] = $item;
                                        }
                                        break;
                                    case "boots":
                                        if ($armorInventory->getBoots()->isNull()) {
                                            $armorInventory->setBoots($item);
                                        } else {
                                            $extraArmor[] = $item;
                                        }
                                        break;
                                }

                                if (isset($armorData["enchantments"])) {
                                    foreach ($armorData["enchantments"] as $enchantmentName => $level) {
                                        $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                                        if ($enchantment !== null) {
                                            $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
                                            $item->addEnchantment($enchantmentInstance);
                                        }
                                    }
                                }

                                if (isset($armorData["name"])) {
                                    $item->setCustomName(TextFormat::colorize($armorData["name"]));
                                }
                            }
                        }
                    }

                    $sender->getInventory()->addItem(...$extraArmor);
                }

                if (isset($kitConfig["default"]["items"])) {
                    $items = [];
                    $inventory = $sender->getInventory();

                    foreach ($kitConfig["default"]["items"] as $itemName => $itemData) {
                        $item = StringToItemParser::getInstance()->parse($itemName);

                        if ($item === null) {
                            $item = VanillaItems::AIR();
                        }

                        if (isset($itemData["enchantments"])) {
                            foreach ($itemData["enchantments"] as $enchantmentName => $level) {
                                $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                                if ($enchantment !== null) {
                                    $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
                                    $item->addEnchantment($enchantmentInstance);
                                }
                            }
                        }

                        if (isset($itemData["quantity"])) {
                            $item->setCount((int) $itemData["quantity"]);
                        }
                        if (isset($itemData["name"])) {
                            $item->setCustomName(TextFormat::colorize($itemData["name"]));
                        }

                        if (!$inventory->contains($item)) {
                            $items[] = $item;
                        }
                    }

                    $inventory->addItem(...$items);
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

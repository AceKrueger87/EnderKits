<?php

declare(strict_types=1);

namespace Terpz710\EnderKits\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use pocketmine\item\StringToItemParser;
use pocketmine\item\StringToEnchantmentParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\utils\TextFormat;
use pocketmine\utils\MainLogger;
use pocketmine\utils\yaml\Yaml;

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
            $kitConfig = $this->loadKitConfig();

            if ($kitConfig === null) {
                $sender->sendMessage(TextFormat::RED . "Kit configuration is missing or invalid.");
                return true;
            }

            foreach ($kitConfig as $kitName => $kitData) {
                $this->applyKit($sender, $kitData);

                $sender->sendMessage(TextFormat::GREEN . "You received the kit '$kitName'!");
            }
        } else {
            $sender->sendMessage("This command can only be used in-game.");
        }
        return true;
    }

    private function loadKitConfig() {
        $configPath = $this->plugin->getDataFolder() . "kits.yml";
        if (file_exists($configPath)) {
            try {
                $config = Yaml::parseFile($configPath);
                if ($config !== null && is_array($config)) {
                    return $config['kits'];
                }
            } catch (\Throwable $e) {
                MainLogger::getLogger()->error("Error while parsing kits.yml: " . $e->getMessage());
            }
        }
        return [];
    }

    private function applyKit(Player $player, array $kitData) {
        if (isset($kitData["armor"])) {
            $armorInventory = $player->getArmorInventory();
            foreach (["helmet", "chestplate", "leggings", "boots"] as $armorType) {
                if (isset($kitData["armor"][$armorType])) {
                    $armorData = $kitData["armor"][$armorType];
                    $item = StringToItemParser::getInstance()->parse($armorData["item"]);

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

                if ($item === null) {
                    $item = \pocketmine\item\ItemFactory::get(\pocketmine\item\ItemIds::AIR);
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

                $items[] = $item;
            }

            $inventory->addItem(...$items);
        }
    }
}

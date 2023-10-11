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

            if (isset($kitConfig["test"])) {
                $kitItems = $kitConfig["test"]["items"];
                $armor = $kitConfig["test"];

                $items = [];
                foreach ($kitItems as $itemString) {
                    $itemData = explode(":", $itemString);
                    $item = new Item((int)$itemData[0], (int)$itemData[1]);
                    $item->setCustomName(TextFormat::colorize($itemData[2]));

                    for ($i = 3; $i < count($itemData); $i += 2) {
                        $enchantmentName = $itemData[$i];
                        $enchantmentLevel = (int)$itemData[$i + 1];
                        $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                        if ($enchantment !== null) {
                            $enchantmentInstance = new EnchantmentInstance($enchantment, $enchantmentLevel);
                            $item->addEnchantment($enchantmentInstance);
                        }
                    }

                    $items[] = $item;
                }

                $helmetData = explode(":", $armor["helmet"]);
                $chestplateData = explode(":", $armor["chestplate"]);
                $leggingsData = explode(":", $armor["leggings"]);
                $bootsData = explode(":", $armor["boots"]);

                $helmet = new Item((int)$helmetData[0], (int)$helmetData[1]);
                $chestplate = new Item((int)$chestplateData[0], (int)$chestplateData[1]);
                $leggings = new Item((int)$leggingsData[0], (int)$leggingsData[1]);
                $boots = new Item((int)$bootsData[0], (int)$bootsData[1]);

                $helmet->setCustomName(TextFormat::colorize($helmetData[2]));
                $chestplate->setCustomName(TextFormat::colorize($chestplateData[2]));
                $leggings->setCustomName(TextFormat::colorize($leggingsData[2]));
                $boots->setCustomName(TextFormat::colorize($bootsData[2]));

                for ($i = 3; $i < count($helmetData); $i += 2) {
                    $enchantmentName = $helmetData[$i];
                    $enchantmentLevel = (int)$helmetData[$i + 1];
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, $enchantmentLevel);
                        $helmet->addEnchantment($enchantmentInstance);
                    }
                }

                for ($i = 3; $i < count($chestplateData); $i += 2) {
                    $enchantmentName = $chestplateData[$i];
                    $enchantmentLevel = (int)$chestplateData[$i + 1];
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, $enchantmentLevel);
                        $chestplate->addEnchantment($enchantmentInstance);
                    }
                }

                for ($i = 3; $i < count($leggingsData); $i += 2) {
                    $enchantmentName = $leggingsData[$i];
                    $enchantmentLevel = (int)$leggingsData[$i + 1];
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, $enchantmentLevel);
                        $leggings->addEnchantment($enchantmentInstance);
                    }
                }

                for ($i = 3; $i < count($bootsData); $i += 2) {
                    $enchantmentName = $bootsData[$i];
                    $enchantmentLevel = (int)$bootsData[$i + 1];
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, $enchantmentLevel);
                        $boots->addEnchantment($enchantmentInstance);
                    }
                }

                $sender->getInventory()->setContents($items);
                $sender->getArmorInventory()->setHelmet($helmet);
                $sender->getArmorInventory()->setChestplate($chestplate);
                $sender->getArmorInventory()->setLeggings($leggings);
                $sender->getArmorInventory()->setBoots($boots);

                $sender->sendMessage(TextFormat::GREEN . "You received the Kit!");
            } else {
                $sender->sendMessage(TextFormat::RED . "The 'test' kit is not configured.");
            }
        } else {
            $sender->sendMessage("This command can only be used in-game.");
        }
        return true;
    }
}

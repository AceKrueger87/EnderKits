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
                    $item = StringToItemParser::getInstance()->parse($itemString);
                    if ($item === null) {
                        $item = VanillaItems::AIR();
                    }

                    $items[] = $item;
                }

                $helmet = StringToItemParser::getInstance()->parse($armor["helmet"]);
                $chestplate = StringToItemParser::getInstance()->parse($armor["chestplate"]);
                $leggings = StringToItemParser::getInstance()->parse($armor["leggings"]);
                $boots = StringToItemParser::getInstance()->parse($armor["boots"]);

                if ($helmet === null) {
                    $helmet = VanillaItems::AIR();
                }
                if ($chestplate === null) {
                    $chestplate = VanillaItems::AIR();
                }
                if ($leggings === null) {
                    $leggings = VanillaItems::AIR();
                }
                if ($boots === null) {
                    $boots = VanillaItems::AIR();
                }

                // Add custom names to armor items
                $helmet->setCustomName(TextFormat::colorize($armor["helmet_name"]));
                $chestplate->setCustomName(TextFormat::colorize($armor["chestplate_name"]));
                $leggings->setCustomName(TextFormat::colorize($armor["leggings_name"]));
                $boots->setCustomName(TextFormat::colorize($armor["boots_name"]));

                // Add enchantments to armor items
                foreach ($armor["helmet_enchantments"] as $enchantmentName => $level) {
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
                        $helmet->addEnchantment($enchantmentInstance);
                    }
                }

                foreach ($armor["chestplate_enchantments"] as $enchantmentName => $level) {
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
                        $chestplate->addEnchantment($enchantmentInstance);
                    }
                }

                foreach ($armor["leggings_enchantments"] as $enchantmentName => $level) {
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
                        $leggings->addEnchantment($enchantmentInstance);
                    }
                }

                foreach ($armor["boots_enchantments"] as $enchantmentName => $level) {
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);
                    if ($enchantment !== null) {
                        $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $level);
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

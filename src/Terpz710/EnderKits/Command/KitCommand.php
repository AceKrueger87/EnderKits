<?php

declare(strict_types=1);

namespace Terpz710\EnderKits\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\utils\Config;
use Terpz710\EnderKits\Task\CoolDownTask;

class KitCommand extends Command implements PluginOwned {

    /** @var Plugin */
    private $plugin;
    /** @var Config */
    private $kitsConfig;
    /** @var KitCoolDownTask */
    private $coolDownTask;
 
    public function __construct(Plugin $plugin, Config $kitsConfig, CoolDownTask $coolDownTask) {
        parent::__construct("kit", "Get a kit");
        $this->plugin = $plugin;
        $this->kitsConfig = $kitsConfig;
        $this->coolDownTask = $coolDownTask;
        $this->setPermission("enderkits.cmd");
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            $kitConfig = $this->kitsConfig->getAll();

            if (isset($kitConfig["default"])) {
                $kitData = $kitConfig["default"];
                $kitName = "default";

                if ($this->isKitOnCooldown($sender, $kitName)) {
                    return true;
                }

                $armorInventory = $sender->getArmorInventory();
                $extraArmor = [];

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

                $sender->getInventory()->addItem(...$extraArmor);

                if (isset($kitData["items"])) {
                    $items = [];
                    $inventory = $sender->getInventory();

                    foreach ($kitData["items"] as $itemName => $itemData) {
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

                        $items[] = $item;
                    }

                    $inventory->addItem(...$items);
                }

                $this->updateKitCooldown($sender, $kitName);

                $sender->sendMessage(TextFormat::GREEN . "You received the Kit!");
            } else {
                $sender->sendMessage(TextFormat::RED . "The 'default' kit is not configured.");
            }
        } else {
            $sender->sendMessage("This command can only be used in-game.");
        }
        return true;
    }

    private function isKitOnCooldown(Player $player, string $kitName): bool {
        return $this->coolDownTask->isKitOnCooldown($player, $kitName);
    }

    private function updateKitCooldown(Player $player, string $kitName) {
        $this->coolDownTask->setCooldown($player, $kitName);
    }
}

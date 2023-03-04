<?php

namespace royal\kitui;

use pocketmine\command\CommandSender;
use pocketmine\command\defaults\PluginsCommand;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use Vecnavium\FormsUI\SimpleForm;

class KitCommands extends PluginsCommand{
    public Main $plugin;
    private Config $config;

    public function __construct(string $name, Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct($name);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){
            $this->config = new Config($this->plugin->getDataFolder() . 'config.yml', Config::YAML);
            $this->sendKit($sender);
        }
    }

    public function sendKit(Player $sender)
    {
        $form = new SimpleForm(function (Player $player, $meta = null) {
            if($meta !== null) {
                $configkit = $this->config->getAll()['kits'];
                foreach($configkit as $name => $kitutil) {
                    $configTime = new Config($this->plugin->getDataFolder() . $name . ".yml", Config::YAML);
                    if($player->hasPermission($kitutil['permission'])){
                        if(isset($configTime->exists[$player->getName()])) {
                            if ($configTime->get($player->getName()) >= time()) {
                                $player->sendMessage($this->config->get("not_end_cooldown"));
                                return true;
                            }
                        }
                        if($kitutil['commands'] != null){
                            foreach($kitutil['commands'] as $command) {
                                $commands = str_replace('{player}', $player->getName(), $command);
                                Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $commands);
                            }
                        }
                        foreach($kitutil['items'] as $itemIdmeta => $valueList) {
                            $item = explode(":", $itemIdmeta);
                            $itemFac = ItemFactory::getInstance()->get(intval($item[0]), intval($item[1]))->setCount(intval($item[2]));
							$enchant = $kitutil['items'][$itemIdmeta]['enchant'];
                            if($enchant !== null and is_array($enchant)){
                                foreach($kitutil['items'][$itemIdmeta]['enchant'] as $enchants) {
                                    $enchantExplode = explode(":", $enchants);
                                    $enchantmentInstance = new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId($enchantExplode[0]), $enchantExplode[1]);
                                    $itemFac->addEnchantment($enchantmentInstance);
                                }
                            }
                            if($kitutil['items'][$itemIdmeta]['name'] !== "DEFAULT"){
                                $itemFac->setCustomName("§r" . $kitutil['items'][$itemIdmeta]['name']);
                            }
                            $player->getInventory()->addItem($itemFac);
                            $configTime->set($player->getName(), $this->plugin->translateTime($kitutil['cooldown']));
                            $configTime->save();
                            break;
                        }
                    } else {
                        $player->sendMessage($this->config->get("not_permissions"));
                        break;
                    }
                }
            	return true;
            }
        });
        $form->setTitle("KitUi");
        $form->setContent("Choose your kit");
        foreach($this->config->getAll()['kits'] as $name => $item) {
            if($item['texture'] !== false){
                $form->addButton($name, "0", $item['texture'], $name);
            } else {
                $form->addButton($name, "-1", "", $name);
            }
        }
        $sender->sendForm($form);
    }

    public function getCooldown(int $time)
    {

    }
}

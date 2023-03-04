<?php
namespace royal\kitui;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getLogger()->info("Have you a bug ? please contact me in my shop discord: https://discord.gg/yv7bQujyCN");
        $this->getServer()->getCommandMap()->register("kit",new KitCommands("kit", $this));
    }

    public function translateTime(string $stringtime): float|int
    {
        $time = str_split($stringtime);
        if ($time[1] === "d" || $time[1] === "D"){
            $value = $time[0] * (24 * 60 * 60);
            return time() + $value;
        }elseif ($time[1] === "h" || $time[1] === "H"){
            $value = $time[0] * (60 * 60);
            return time() + $value;
        }
        return 0;
    }
}
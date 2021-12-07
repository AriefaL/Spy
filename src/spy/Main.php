<?php declare(strict_types=1);
namespace spy;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class Main extends PluginBase implements Listener {

    private $enableSpy = [];
    
    protected function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if ($command->getName() === "spy") {
            if ($sender instanceof Player) {
                if (!isset($this->enableSpy[$sender->getName()])) {
                    $this->enableSpy[$sender->getName()] = $sender;
                    $sender->sendMessage(TextFormat::GREEN . "Spy Mode enabled");
                } else {
                    unset($this->enableSpy[$sender->getName()]);
                    $sender->sendMessage(TextFormat::RED . "Spy Mode disable");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "Command Spy Mode can use only game");
            }
        }
		return false;
	}

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        if ($this->checkPermission($player)) {
            if (!isset($this->enableSpy[$player->getName()])) {
                $this->enableSpy[$player->getName()] = $player;
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        unset($this->enableSpy[$event->getPlayer()->getName()]);
    }

    public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $command = $event->getMessage();

        if (!empty($this->enableSpy)) { 
            if ($command[0] === "/") {
                if (strpos($command, "plugins") || strpos($command, "pl") || strpos($command, "op")) {
                    $this->setMessage($player, $player->getName() . " > hidden for security reasons");
                } else {
                    $this->setMessage($player, $player->getName() . " > " . $command);
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param string $message
     */
    private function setMessage(Player $player, string $message): void{
        $this->getLogger()->info($player->getName() . " > " . $message);
        foreach ($this->enableSpy as $sender) {
            if ($this->checkPermission($sender)) {
                $sender->sendMessage($player->getName() . " > " . $message);
            }
        }
    }

    /**
     * @param Player $player
     * @return boolean
     */
    private function checkPermission(Player $player): bool{
        if ($player->hasPermission("spy.access")) {
            return true;
        }
        return false;
    }
}
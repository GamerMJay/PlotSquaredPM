<?php
declare(strict_types=1);
namespace MyPlot\subcommand;

use MyPlot\forms\MyPlotForm;
use MyPlot\MyPlot;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class AutoSubCommand extends SubCommand
{
	public function canUse(CommandSender $sender) : bool {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.auto");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) : bool {
		$levelName = $sender->getPosition()->getWorld()->getFolderName();
		if(!$this->getPlugin()->isLevelLoaded($levelName)) {
			$sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("auto.notplotworld"));
			return true;
		}
		if(($plot = $this->getPlugin()->getNextFreePlot($levelName)) !== null) {
			if($this->getPlugin()->teleportPlayerToPlot($sender, $plot, true)) {
				$sender->sendMessage(MyPlot::getPrefix() . $this->translateString("auto.success", [$plot->X, $plot->Z]));
				$cmd = new ClaimSubCommand($this->getPlugin(), "claim");
				if(isset($args[0]) and strtolower($args[0]) == "true" and $cmd->canUse($sender)) {
					$cmd->execute($sender, isset($args[1]) ? [$args[1]] : []);
				}
			}else {
				$sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("error"));
			}
		}else{
			$sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("auto.noplots"));
		}
		return true;
	}

	public function getForm(?Player $player = null) : ?MyPlotForm {
		return null;
	}
}
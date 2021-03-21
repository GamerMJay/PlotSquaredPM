<?php
declare(strict_types=1);
namespace MyPlot\subcommand;

use MyPlot\forms\MyPlotForm;
use MyPlot\forms\subforms\HomeForm;
use MyPlot\MyPlot;
use MyPlot\Plot;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HomeSubCommand extends SubCommand
{
	public function canUse(CommandSender $sender) : bool {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.home");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 *
	 * @return bool
	 */
    public function execute(CommandSender $sender, array $args): bool
    {
        if (isset($args[0]) && is_numeric($args[0]) === false) {
            if (!$s = $this->getPlugin()->getServer()->getPlayer($args[0])) {
                $p = $args[0];
            } else {
                $p = $s->getName();
            }
            if (isset($args[1]) && is_numeric($args[1])) {
                $plotNumber = (int)$args[1];
            } else {
                $plotNumber = (int)1;
            }
            $levelName = $sender->getLevel()->getFolderName();
            $plots = $this->getPlugin()->getPlotsOfPlayer($p, $levelName);
            if (empty($plots)) {
                $sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("home.noplots.target"));
                return true;
            }
            if (!isset($plots[$plotNumber - 1])) {
                $sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("home.notexist.target", [$plotNumber]));
                return true;
            }
            usort($plots, function (Plot $plot1, Plot $plot2) {
                if ($plot1->levelName == $plot2->levelName) {
                    return 0;
                }
                return ($plot1->levelName < $plot2->levelName) ? -1 : 1;
            });
            $plot = $plots[$plotNumber - 1];
            if ($this->getPlugin()->teleportPlayerToPlot($sender, $plot)) {
                $sender->sendMessage(MyPlot::getPrefix() . $this->translateString("home.success", [$plot, $plot->levelName]));
            } else {
                $sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("home.error"));
            }
            return true;
        }

        $p = $sender->getName();

        if (isset($args[0]) && is_numeric($args[0])) {
            $plotNumber = (int)$args[0];
        } else {
            $plotNumber = (int)1;
        }

        $levelName = $sender->getLevel()->getFolderName();
        $plots = $this->getPlugin()->getPlotsOfPlayer($p, $levelName);
        if (empty($plots)) {
            $sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("home.noplots"));
            return true;
        }
        if (!isset($plots[$plotNumber - 1])) {
            $sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("home.notexist", [$plotNumber]));
            return true;
        }
        usort($plots, function (Plot $plot1, Plot $plot2) {
            if ($plot1->levelName == $plot2->levelName) {
                return 0;
            }
            return ($plot1->levelName < $plot2->levelName) ? -1 : 1;
        });
        $plot = $plots[$plotNumber - 1];
        if ($this->getPlugin()->teleportPlayerToPlot($sender, $plot)) {
            $sender->sendMessage(MyPlot::getPrefix() . $this->translateString("home.success", [$plot, $plot->levelName]));
        } else {
            $sender->sendMessage(MyPlot::getPrefix() . TextFormat::RED . $this->translateString("home.error"));
        }
        return true;
    }

	public function getForm(?Player $player = null) : ?MyPlotForm {
		if($player !== null and count($this->getPlugin()->getPlotsOfPlayer($player->getName(), $player->getLevelNonNull()->getFolderName())) > 0)
			return new HomeForm($player);
		return null;
	}
}
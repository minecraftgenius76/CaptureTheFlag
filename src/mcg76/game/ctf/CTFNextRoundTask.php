<?php

namespace mcg76\game\ctf;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\level\Level;

/**
 * Scheduled Task Next Round
 * 
 * Copyright (C) 2014 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *        
 */
class CTFNextRoundTask extends PluginTask {
	private $plugin;
	private $level;
	
	public function __construct(CTFPlugIn $plugin, $level) {
		$this->plugin = $plugin;
		$this->level = $level;
		parent::__construct ( $plugin );
	}
	
	public function onRun($ticks) {
		$this->getPlugin()->ctfManager->handleStartTheGame($this->level);
	}
	
	public function getPlugin() {
		return $this->plugin;
	}
	
	public function onCancel() {
	}
}

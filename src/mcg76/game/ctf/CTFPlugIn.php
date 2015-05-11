<?php

namespace mcg76\game\ctf;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;


/**
 * CaptureTheFlag PlugIn - MCPE Mini-Game
 *
 * Copyright (C) 2015 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *        
 */
class CTFPlugIn extends PluginBase implements CommandExecutor {
	
	// object variables
	//public $config;
	public $ctfBuilder;
	public $ctfManager;
	public $ctfMessages;
	public $ctfGameKit;
	public $ctfSetup;
	
	// keep track of all points
	public $redTeamPlayers = [ ];
	public $blueTeamPLayers = [ ];
	public $gameStats = [ ];
	
	// players with the flag
	public $playersWithRedFlag = [ ];
	public $playersWithBlueFlag = [ ];
	
	// keep game statistics
	public $gameMode = 0;
	public $gameState = 0;
	public $blueTeamWins = 0;
	public $redTeamWins = 0;
	public $pos_display_flag = 0;
	public $currentGameRound = 0;
	public $maxGameRound = 3;
	
	//lobby world
	public $CTFWorldName;

	//setup mode
	public $setupModeAction = "";
	
	/**
	 * OnLoad
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onLoad()
	 */
	public function onLoad() {		
		$this->initMinigameComponents();
	}

	/**
	 * OnEnable
	 *
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {	
		$this->initConfigFile ();				
		$this->enabled = true;
		$this->getServer ()->getPluginManager ()->registerEvents ( new CTFListener ( $this ), $this );
		$this->getLogger ()->info ( TextFormat::GREEN . "-MCG76 CTF Enabled" );
		$this->getLogger ()->info ( TextFormat::GREEN . "-------------------------------------------------" );
		$this->initMessageTests();
		
		//check if everything initializared
		if ($this->ctfManager==null) {
			$this->getLogger()->info(" manager not initialized properly");
		}		
		if ($this->ctfSetup==null) {
			$this->getLogger()->info(" setup not initialized properly");
		}
		if ($this->ctfMessages==null) {
			$this->getLogger()->info(" messages not initialized properly");
		}
		if ($this->ctfBuilder==null) {
			$this->getLogger()->info(" builder not initialized properly");
		}
		if ($this->ctfGameKit==null) {
			$this->getLogger()->info(" gamekit not initialized properly");
		}
	}
	
	private function initMinigameComponents() {
		try {
		$this->ctfSetup = new CTFSetup ( $this );
		$this->ctfMessages = new CTFMessages ( $this );
		$this->ctfManager = new CTFManager ( $this );		
		$this->ctfBuilder = new CTFBlockBuilder ( $this );
		$this->ctfGameKit = new CTFGameKit ( $this );
		} catch ( \Exception $ex ) {
			$this->getLogger ()->error( $ex->getMessage() );
		}
	}
	
	private function initConfigFile() {
		try {
			$this->saveDefaultConfig ();
			if (! file_exists ( $this->getDataFolder () )) {
				@mkdir ( $this->getDataFolder (), 0777, true );
				file_put_contents ( $this->getDataFolder () . "config.yml", $this->getResource ( "config.yml" ) );
			}
			$this->reloadConfig ();
			$this->getConfig ()->getAll ();
			
			//set game world
			$this->CTFWorldName = $this->ctfSetup->getCTFWorldName();			
		} catch ( \Exception $e ) {
			$this->getLogger ()->error ( $e->getMessage());
		}
	}
	
	private function initMessageTests() {
		if ($this->getConfig ()->get ( "run_selftest_message" ) == "YES") {
			$stmsg = new TestMessages ( $this );
			$stmsg->runTests ();
		}
	}
	
	/**
	 * OnDisable
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->getLogger ()->info ( TextFormat::RED . $this->ctfMessages->getMessageByKey ( "plugin.disable" ) );
		$this->enabled = false;
	}
	
	public function setGameMode($mode) {
		$this->gameMode = $mode;
	}
	
	public function getGameMode() {
		return $this->gameMode;
	}
	
	public function clearSetup() {
		$this->setupModeAction="";
	}
	
	/**
	 * OnCommand
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$this->ctfManager->onCommand ( $sender, $command, $label, $args );
	}
}

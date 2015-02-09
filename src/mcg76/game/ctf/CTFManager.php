<?php

namespace mcg76\game\ctf;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\command\defaults\TeleportCommand;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;

/**
 * CTF Manager
 *
 * Copyright (C) 2015 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *        
 */
class CTFManager  extends MiniGameBase  {
	/*
	 * CTF Commands
	 */
	const CTF_COMMAND = "ctf";
	const CTF_COMMAND_HOME = "home";
	const CTF_COMMAND_JOIN = "join";
	const CTF_COMMAND_JOIN_RED_TEAM = "joinred";
	const CTF_COMMAND_JOIN_BLUE_TEAM = "joinblue";
	const CTF_COMMAND_LEAVE = "leave";
	const CTF_COMMAND_START = "start";
	const CTF_COMMAND_STOP = "stop";
	const CTF_COMMAND_STATS = "stats";
	const CTF_COMMAND_CREATE_ARENA = "create";
	const CTF_COMMAND_RESET_ARENA = "reset";
	const CTF_COMMAND_BLOCK_DISPLAY_ON = "blockon";
	const CTF_COMMAND_BLOCK_DISPLAY_OFF = "blockoff";
	//change sign location
	const CTF_COMMAND_SET_SIGN_JOIN_BLUE_TEAM = "setsignblue";
	const CTF_COMMAND_SET_SIGN_JOIN_RED_TEAM = "setsignred";
	const CTF_COMMAND_SET_SIGN_VIEW_STATS = "setsignstats";
	const CTF_COMMAND_SET_SIGN_NEW_GAME = "setsignnew";
	//change block type
	const CTF_COMMAND_SETBLOCK_ID_TEAM_BORDER = "setblockborder";
	const CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_BLUE_TEAM = "setblockwallblue";
	const CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_RED_TEAM = "setblockwallred";	
					
	/*
	 * CTF permissions 
	 */
	const CTF_PERMISSION_ROOT = "mcg76.ctf";
	const CTF_PERMISSION_COMMANDS = "mcg76.ctf";	
	const CTF_PERMISSION_HOME = "mcg76.ctf.command.home";
	const CTF_PERMISSION_START = "mcg76.ctf.command.start";
	const CTF_PERMISSION_STOP = "mcg76.ctf.command.stop";
	const CTF_PERMISSION_LEAVE = "mcg76.ctf.command.leave";
	const CTF_PERMISSION_CREATE_ARENA = "mcg76.ctf.command.create";
	const CTF_PERMISSION_RESET_ARENA = "mcg76.ctf.command.reset";
	const CTF_PERMISSION_STATS = "mcg76.ctf.command.stats";	
	const CTF_PERMISSION_JOIN_BLUE_TEAM = "mcg76.ctf.command.joinblue";						
	const CTF_PERMISSION_JOIN_RED_TEAM = "mcg76.ctf.command.joinred";
	const CTF_PERMISSION_BLOCK_DISPLAY_ON = "mcg76.ctf.command.blockon";
	const CTF_PERMISSION_BLOCK_DISPLAY_OFF = "mcg76.ctf.command.blockoff";
	
	/**
	 *
	 * @param CTFPlugin $pg        	
	 */
	public function __construct(CTFPlugIn $plugin) {
		parent::__construct ( $plugin );
	}
	
	/**
	 * Handle CTF Commands
	 *
	 * @param CommandSender $sender        	
	 * @param Command $command        	
	 * @param unknown $label        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$player = null;
		if (! $sender instanceof PLayer) {
			$sender->sendMessage ( $this->getMsg ( "ctf.error.wrong-sender" ) );
			return;
		}
		$player = $sender->getPlayer ();
		if ((strtolower ( $command->getName () ) == self::CTF_COMMAND) && isset ( $args [0] )) {
			if (strtolower ( $args [0] ) == self::CTF_COMMAND_CREATE_ARENA || strtolower ( $args [0] ) == self::CTF_COMMAND_RESET_ARENA) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->buildGameArena ( $sender );
				$output = TextFormat::BLUE . "----------------------------------\n";
				$output .= TextFormat::BLUE . $this->getMsg ( "arena.created" ) . "\n";
				$output .= TextFormat::BLUE . "----------------------------------\n";
				$sender->sendMessage ( $output );
				return true;
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_BLOCK_DISPLAY_ON) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn ()->pos_display_flag = 1;
				$sender->sendMessage ( $this->getMsg ( "block.display-on" ) );
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_BLOCK_DISPLAY_OFF) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn ()->pos_display_flag = 0;
				$sender->sendMessage ( $this->getMsg ( "block.display-off" ) );
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_JOIN_BLUE_TEAM) {
				$sender->sendMessage ( $this->getMsg ( "team.join-blue" ) );
				$this->handleJoinBlueTeam ( $player );
				return;
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_JOIN_RED_TEAM) {
				$sender->sendMessage ( $this->getMsg ( "team.join-red" ) );
				$this->handleJoinRedTeam ( $player );
				return;
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_LEAVE) {
				$sender->sendMessage ( $this->getMsg ( "game.player-left" ) );
				$this->handleLeaveTheGame ( $player );
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_STOP) {
				$sender->sendMessage ( $this->getMsg ( "game.player-stop" ) );
				$this->stopGame($player );
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_HOME) {
				$sender->sendMessage ( $this->getMsg ( "sign.teleport.ctf" ) );
				$this->teleportPlayerToHome ( $player );
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_START) {
				$sender->sendMessage ( $this->getMsg ( "game.player-start" ) );
				$this->handleStartTheGame ( $player->getLevel () );
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_STATS) {	
				$sender->sendMessage ( $this->getMsg ( "game.stats" ) );
				$this->displayTeamScores ( $sender );
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_SET_SIGN_JOIN_BLUE_TEAM) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn()->setupModeAction= self::CTF_COMMAND_SET_SIGN_JOIN_BLUE_TEAM;				
				$sender->sendMessage ($this->getMsg ( "ctf.setup.action" ).self::CTF_COMMAND_SET_SIGN_JOIN_BLUE_TEAM);
				$sender->sendMessage($this->getMsg ( "ctf.setup.select" ));				
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_SET_SIGN_JOIN_RED_TEAM) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn()->setupModeAction= self::CTF_COMMAND_SET_SIGN_JOIN_RED_TEAM;
				$sender->sendMessage ($this->getMsg ( "ctf.setup.action" ).self::CTF_COMMAND_SET_SIGN_JOIN_RED_TEAM);
				$sender->sendMessage($this->getMsg ( "ctf.setup.select" ));
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_SET_SIGN_NEW_GAME) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn()->setupModeAction= self::CTF_COMMAND_SET_SIGN_NEW_GAME;
				$sender->sendMessage ($this->getMsg ( "ctf.setup.action" ).self::CTF_COMMAND_SET_SIGN_NEW_GAME);
				$sender->sendMessage($this->getMsg ( "ctf.setup.select" ));
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_SET_SIGN_VIEW_STATS) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn()->setupModeAction= self::CTF_COMMAND_SET_SIGN_VIEW_STATS;
				$sender->sendMessage ($this->getMsg ( "ctf.setup.action" ).self::CTF_COMMAND_SET_SIGN_VIEW_STATS);
				$sender->sendMessage($this->getMsg ( "ctf.setup.select" ));
				
			}elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_SETBLOCK_ID_TEAM_BORDER) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn()->setupModeAction= self::CTF_COMMAND_SETBLOCK_ID_TEAM_BORDER;
				$sender->sendMessage ($this->getMsg ( "ctf.setup.action" ).self::CTF_COMMAND_SETBLOCK_ID_TEAM_BORDER);
				$sender->sendMessage($this->getMsg ( "ctf.setup.select" ));
				
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_BLUE_TEAM) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn()->setupModeAction= self::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_BLUE_TEAM;
				$sender->sendMessage ($this->getMsg ( "ctf.setup.action" ).self::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_BLUE_TEAM);
				$sender->sendMessage($this->getMsg ( "ctf.setup.select" ));
				
			} elseif (strtolower ( $args [0] ) == self::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_RED_TEAM) {
				if (! $sender->isOp ()) {
					$sender->sendMessage ( $this->getMsg ( "ctf.error.no-permission" ) );
					return;
				}
				$this->getPlugIn()->setupModeAction= self::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_RED_TEAM;
				$sender->sendMessage ($this->getMsg ( "ctf.setup.action" ).self::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_RED_TEAM);
				$sender->sendMessage($this->getMsg ( "ctf.setup.select" ));
			}
			
		}
	}

	/**
	 * Game Stop Clean Up
	 *
	 * @param Player $player        	
	 */
	public function stopGame(Player $player) {
		//to avoid interruption, only allow in-game player allow issue stop command
		$inGamePlayers = array_merge($this->getPlugIn()->blueTeamPLayers, $this->getPlugIn()->redTeamPlayers);
		if (!isset($inGamePlayers[$player->getName()])) {
			$player->sendMessage ( $this->getMsg ( "game.in-progress" ) );
			$player->sendMessage ( $this->getMsg ( "ctf.error.not-game-stop" ) );
			return;
		}				
		// display
		$this->handleBroadcastFinalScore ( $player, false );
		// reset stats
		$this->getPlugIn ()->redTeamWins = 0;
		$this->getPlugIn ()->blueTeamWins = 0;
		$this->getPlugIn ()->currentGameRound = 0;
		$this->getPlugIn ()->redTeamPlayers = [ ];
		$this->getPlugIn ()->blueTeamPLayers = [ ];
		// remove players from arena
		$this->handleStopTheGame ();		
		//let player leave the game
		$this->handleLeaveTheGame($player);
		//close fence
		$arenaSize = $this->getSetup ()->getArenaSize ();
		$arenaPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION );
		$this->getBuilder()->closeTeamGates ( $player->level, $arenaSize, $arenaPos->x + $arenaSize, $arenaPos->y, $arenaPos->z );		
		return;
	}
	
	/**
	 * Display Team Stats
	 *
	 * @param Player $player        	
	 */
	public function displayTeamScores(Player $player) {
		// handle join game
		$maxTeamPlayers = $this->getSetup ()->getMaxPlayerPerTeam ();
		$output = "";
		$output .= "------------------------------\n";
		$status = "OFF";
		if ($this->getPlugIn ()->gameMode == 1) {
			$status = "ON";
		}
		$output .= $this->getMsg ( "ctf.name" ) . " | " . $this->getMsg ( "ctf.status" ) . ": " . $status . "\n";
		$output .= "------------------------------\n";
		$output .= $this->getMsg ( "team.scores.red-players" ) . " [" . count ( $this->getPlugIn ()->redTeamPlayers ) . "/" . $maxTeamPlayers . "] " . $this->getMsg ( "team.scores.players" ) . "\n";
		$output .= $this->getMsg ( "team.scores.blue-players" ) . " [" . count ( $this->getPlugIn ()->blueTeamPLayers ) . "/" . $maxTeamPlayers . "] " . $this->getMsg ( "team.scores.players" ) . "\n";
		$output .= "------------------------------\n";
		$output .= $this->getMsg ( "team.scores.round" ) . " [" . $this->getPlugIn ()->currentGameRound . "/" . $this->getPlugIn ()->maxGameRound . "]".$this->getMsg ( "team.scores.score" )."\n";
		$output .= "------------------------------\n";
		$output .= $this->getMsg ( "team.scores.redteam-wins" ) . $this->getPlugIn ()->redTeamWins . "\n";
		$output .= $this->getMsg ( "team.scores.blueteam-wins" ) . $this->getPlugIn ()->blueTeamWins . "\n";
		$output .= "------------------------------\n";
		$player->sendMessage ( $output );
	}
	
	/**
	 * Handle Click New Game Sign
	 *
	 * @param Player $player        	
	 * @param unknown $blockTouched        	
	 */
	public function onClickNewGameSign(Player $player, $blockTouched) {
		$signNewPos = $this->getSetup ()->getSignPos ( CTFSetup::CLICK_SIGN_NEW_GAME );
		// $player->sendMessage ("New game ".$sx." ".$sy." ".$sz);
		if (round ( $blockTouched->x ) == round ( $signNewPos->x ) && round ( $blockTouched->y ) == round ( $signNewPos->y ) && round ( $blockTouched->z ) == round ( $signNewPos->z )) {
			// check if game still on
			if ($this->getPlugIn ()->gameMode == 1) {
				$player->sendMessage ( $this->getMsg ( "game.in-progress" ) );
			}
			if ($this->getPlugIn ()->gameMode == 0) {
				$player->level->getBlockLightAt($blockTouched->x, $blockTouched->y, $blockTouched->z);
				$this->handleNewGame ( $player );
			}
			return;
		}
	}
	
	/**
	 * Handle New Game
	 *
	 * @param Player $player        	
	 */
	public function handleNewGame(Player $player) {
		$player->getServer ()->broadcastMessage ( "----------------------------------" );
		$player->getServer ()->broadcastMessage ( $this->getMsg ( "game.new-game" ) );
		$player->getServer ()->broadcastMessage ( "----------------------------------" );
		// reset close gates
		$arenaSize = $this->getSetup ()->getArenaSize ();
		$arenaPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION );
		// close gate
		$level = $player->getLevel ();
		$this->getBuilder()->closeTeamGates ( $level, $arenaSize, $arenaPos->x + $arenaSize, $arenaPos->y, $arenaPos->z );
		// reset fire
		// blue team flag
		$this->getBuilder ()->addBlueTeamFlag ( $level, 171, 11 );
		// add red team flag
		$this->getBuilder ()->addRedTeamFlag ( $level, 171, 14 );
		// add game button
		$this->getBuilder ()->addGameButtons ( $level );
		// reset stats
		$this->getPlugIn ()->blueTeamWins = 0;
		$this->getPlugIn ()->redTeamWins = 0;
	}
	
	/**
	 * Validate Captured Flag
	 *
	 * @param Player $player        	
	 * @param unknown $rb        	
	 */
	public function checkTeamCapturedFlag(Player $player, $rb) {
		// checkTeamCapturedFlag;
		$level = $player->getLevel ();
		if (isset ( $this->getPlugIn ()->blueTeamPLayers [$player->getName ()] )) {
			$this->checkBlueTeamCapturedEnermyFlag ( $player, $level, $rb );
			return;
		}
		
		if (isset ( $this->getPlugIn ()->redTeamPlayers [$player->getName ()] )) {
			$this->checkRedTeamCapturedEnermyFlag ( $player, $level, $rb );
			return;
		}
	}
	/**
	 * Validate team captured flag
	 *
	 * @param Level $level        	
	 * @param unknown $rb        	
	 */
	public function checkRedTeamCapturedEnermyFlag(Player $player, Level $level, $rb) {
		$redTeamEnemyFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_RED_TEAM_ENEMY );
		// add holder for emermy flag
		if (round ( $rb->x ) == round ( $redTeamEnemyFlagPos->x ) && round ( $rb->y ) == round ( $redTeamEnemyFlagPos->y ) && round ( $rb->z ) == round ( $redTeamEnemyFlagPos->z )) {
			// check if enermy flag is destroyed
			$blueTeamFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_BLUE_TEAM );
			$blueflag = $level->getBlock ( $blueTeamFlagPos );
			if ($blueflag->getId () != 0) {
				$player->sendMessage ( $this->getMsg ( "ctf.error.blueteam-flag-exist" ) );
				// erase placed block
				$this->getBuilder ()->resetBlock ( $rb, $level, 0 );
				return;
			}
			// blue team has enermy flag, announce blue team winning
			$output = "";
			$output .= "------------------------------\n";
			$output .= $this->getMsg ( "ctf.conglatulations" ) . "\n";
			$output .= $this->getMsg ( "ctf.red-team.capturedflag" ) . "\n";
			$output .= "------------------------------\n";
			// update blue team winning score
			$this->getPlugIn ()->redTeamWins ++;
			$output .= $this->getMsg ( "ctf.blue-team.score" ) . $this->getPlugIn ()->blueTeamWins . "\n";
			$output .= $this->getMsg ( "ctf.red-team.score" ) . $this->getPlugIn ()->redTeamWins . "\n";
			$output .= "------------------------------\n";
			$level->getServer ()->broadcastMessage ( $output );
			
			$this->getPlugIn ()->gameMode = 0;
			$this->moveTeamBackToSpawnpointOnCapturedEnermyFlag ();
			
			// send scheduled task
			$this->handleNextRoundOfGame ( $level );
		}
	}
	
	/**
	 * Validate Blue Team Captured Flag
	 *
	 * @param Player $player        	
	 * @param Level $level        	
	 * @param unknown $rb        	
	 */
	public function checkBlueTeamCapturedEnermyFlag(Player $player, Level $level, $rb) {
		$blueTeamEnemyFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_BLUE_TEAM_ENEMY );
		if (round ( $rb->x ) == round ( $blueTeamEnemyFlagPos->x ) && round ( $rb->y ) == round ( $blueTeamEnemyFlagPos->y ) && round ( $rb->z ) == round ( $blueTeamEnemyFlagPos->z )) {
			// check if enermy flag is destroyed
			$blueTeamFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_RED_TEAM );
			$redflag = $level->getBlock ( $blueTeamFlagPos );
			if ($redflag->getId () != 0) {
				$player->sendMessage ( $this->getMsg ( "ctf.error.redteam-flag-exist" ) );
				// erase placed block
				$this->getBuilder ()->resetBlock ( $rb, $level, 0 );
				return;
			}
			// blue team has enermy flag, announce blue team winning
			$output = "";
			$output .= "------------------------------\n";
			$output .= $this->getMsg ( "ctf.conglatulations" ) . "\n";
			$output .= $this->getMsg ( "ctf.blue-team.capturedflag" ) . "\n";
			$output .= "------------------------------\n";
			// update blue team winning score
			$this->getPlugIn ()->blueTeamWins ++;
			$output .= $this->getMsg ( "ctf.blue-team.score" ) . $this->getPlugIn ()->blueTeamWins . "\n";
			$output .= $this->getMsg ( "ctf.red-team.score" ) . $this->getPlugIn ()->redTeamWins . "\n";
			$output .= "------------------------------\n";
			$level->getServer ()->broadcastMessage ( $output );
			$this->getPlugIn ()->gameMode = 0;
			$this->moveTeamBackToSpawnpointOnCapturedEnermyFlag ();
			// send scheduled task
			$this->handleNextRoundOfGame ( $level );
		}
	}
	
	/**
	 * Prepare Next Round
	 *
	 * @param Level $level        	
	 */
	public function handleNextRoundOfGame(Level $level) {
		// moved players back to own territory
		$this->moveTeamBackToSpawnpointOnCapturedEnermyFlag ();
		// close the gate
		$arenaSize = $this->getSetup ()->getArenaSize ();
		$arenaPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION );
		$this->getBuilder ()->closeTeamGates ( $level, $arenaSize, $arenaPos->x + $arenaSize, $arenaPos->y, $arenaPos->z );
		
		// send scheduled task
		if ($this->getPlugIn ()->currentGameRound < $this->getPlugIn ()->maxGameRound) {
			// prepared for next round
			$resetValue = $this->getSetup ()->getRoundWaitTime ();
			$taskWaitTime = $resetValue * $this->getPlugin()->getServer()->getTicksPerSecond ();
			$resetTask = new CTFNextRoundTask ( $this->getPlugIn (), $level );
			$this->getPlugIn ()->getServer ()->getScheduler ()->scheduleDelayedTask ( $resetTask, $taskWaitTime );
			
			$this->getPlugIn ()->getServer ()->broadcastMessage ( $this->getMsg ( "game.getready" ) );
			$this->getPlugIn ()->getServer ()->broadcastMessage ( $this->getMsg ( "game.nextround" ) . " [" . $this->getPlugIn ()->currentGameRound . "/" . $this->getPlugIn ()->maxGameRound . "] " . $this->getMsg ( "game.roundstart" )." ". $resetValue . " " . $this->getMsg ( "game.ticks" ) );
		} else {
			$this->getPlugIn ()->getServer ()->broadcastMessage ( $this->getMsg ( "ctf.finished" ) );
			$this->handleStopTheGame ();
		}
	}
	
	/**
	 *
	 * Handle Player Join Blue Team
	 *
	 * @param Player $player        	
	 */
	public function handleJoinBlueTeam(Player $player) {
		$blueTeamEntryPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION_ENTRY_BLUE_TEAM );		
		$player->level->getChunk($blueTeamEntryPos->x, $blueTeamEntryPos->z);
		$player->teleport ( $blueTeamEntryPos );
		$player->sendMessage ( $this->getMsg ( "team.welcome-blue" ) );
		// $player->sendMessage("equip player with armor");
		$this->getGameKit ()->putOnGameKit ( $player, CTFGameKit::KIT_BLUE_TEAM );
		$player->sendMessage ( $this->getMsg ( "team.tap-start" ) );
		
		$this->getPlugIn ()->blueTeamPLayers [$player->getName ()] = $player;
		$player->setNameTag ( $this->getMsg ( "team.blue" ) . " | " . $player->getName () );
		$player->getLevel ()->getServer ()->broadcastMessage ( $player->getName () . $this->getMsg ( "team.joined-blue" ) );
		
		foreach ( $this->getPlugIn ()->blueTeamPLayers as $p ) {
			$p->sendMessage ( $this->getMsg ( "team.members" ) . $p->getName () );
		}
	}
	
	/**
	 * Handle Player Join Red Team
	 *
	 * @param Player $player        	
	 */
	public function handleJoinRedTeam(Player $player) {
		$redTeamEntryPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION_ENTRY_RED_TEAM );
		$player->level->getChunk($redTeamEntryPos->x, $redTeamEntryPos->z);
		$player->teleport ( $redTeamEntryPos );
		$player->sendMessage ( $this->getMsg ( "team.welcome-red" ) );
		// $this->addRedTeamPlayerAmor ( $player );
		$this->getGameKit ()->putOnGameKit ( $player, CTFGameKit::KIT_RED_TEAM );
		$player->sendMessage ( $this->getMsg ( "team.tap-start" ) );
		$this->getPlugIn ()->redTeamPlayers [$player->getName ()] = $player;
		$player->setNameTag ( $this->getMsg ( "team.red" ) . " | " . $player->getName () );
		
		$player->getLevel ()->getServer ()->broadcastMessage ( $player->getName () . $this->getMsg ( "team.joined-red" ) );
		foreach ( $this->getPlugIn ()->redTeamPlayers as $p ) {
			$p->sendMessage ( $this->getMsg ( "team.members" ) . $p->getName () );
		}
	}
	/**
	 * Handle Player leave the game
	 *
	 * @param Player $player        	
	 */
	public function handleLeaveTheGame(Player $player) {
		// check if the player
		if (isset ( $this->getPlugIn ()->redTeamPlayers [$player->getName ()] )) {
			unset ( $this->getPlugIn ()->redTeamPlayers [$player->getName ()] );
			$player->setNameTag ( $player->getName () );
			$this->getGameKit ()->removePlayerIventory ( $player );
			$player->updateMovement ();
			$player->sendMessage ( $this->getMsg ( "game.remove-equipment" ) );
		}
		if (isset ( $this->getPlugIn ()->blueTeamPlayers [$player->getName ()] )) {
			unset ( $this->getPlugIn ()->blueTeamPlayers [$player->getName ()] );
			$player->setNameTag ( $player->getName () );
			$this->getGameKit ()->removePlayerIventory ( $player );
			$player->updateMovement ();
			$player->sendMessage ( $this->getMsg ( "game.remove-equipment" ) );
		}
		
		$gameWaitingRoomPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION_WAITING_ROOM );
		$player->teleport ( $gameWaitingRoomPos );
		$player->getServer ()->broadcastMessage ( $player->getName () . $this->getMsg ( "ctf.left-game" ) );
	}
	
	/**
	 * Game Stop Clean up
	 */
	public function handleStopTheGame() {
		// send all players to waiting room
		$waitingRoomPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION_WAITING_ROOM );
		foreach ( $this->getPlugIn ()->redTeamPlayers as $rp ) {
			$rp->sendMessage ( $this->getMsg ( "game.stop" ) );
			$rp->setNameTag ( $rp->getName () );
			$this->getGameKit ()->removePlayerIventory ( $rp );
			$rp->sendMessage ( $this->getMsg ( "ctf.return-waiting-area" ) );
			$rp->teleport ( $waitingRoomPos );
		}
		
		foreach ( $this->getPlugIn ()->blueTeamPLayers as $bp ) {
			$bp->sendMessage ( $this->getMsg ( "game.stop" ) );
			$bp->setNameTag ( $bp->getName () );
			$this->getGameKit ()->removePlayerIventory ( $bp );
			$bp->sendMessage ( $this->getMsg ( "ctf.return-waiting-area" ) );
			$bp->teleport ( $waitingRoomPos );
		}
		
		$this->getPlugIn ()->gameMode = 0;
		$this->getPlugIn ()->blueTeamPLayers = [ ];
		$this->getPlugIn ()->redTeamPlayers = [ ];
	}
	
	/**
	 * Send Team Back to Spawn Point
	 */
	public function moveTeamBackToSpawnpointOnCapturedEnermyFlag() {
		// send all players to waiting room
		$redTeamSpawnPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION_ENTRY_RED_TEAM );
		foreach ( $this->getPlugIn ()->redTeamPlayers as $rp ) {
			$rp->teleport ( $redTeamSpawnPos );
		}
		$blueTeamSpawnPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION_ENTRY_BLUE_TEAM );
		foreach ( $this->getPlugIn ()->blueTeamPLayers as $bp ) {
			$bp->teleport ( $blueTeamSpawnPos );
		}
		$this->getPlugIn ()->gameMode = 0;
	}
	
	/**
	 *
	 * Touched Join Read Team Sign
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onClickJoinRedTeamSign(Player $player, $blockTouched) {
		$maxTeamPlayers = $this->getSetup ()->getMaxPlayerPerTeam ();
		$joinRedPos = $this->getSetup ()->getSignPos ( CTFSetup::CLICK_SIGN_JOIN_RED_TEAM );
		// Join RED Team SIGN
		if (round ( $blockTouched->x ) == round ( $joinRedPos->x ) && round ( $blockTouched->y ) == round ( $joinRedPos->y ) && round ( $blockTouched->z ) == round ( $joinRedPos->z )) {
			if (count ( $this->getPlugIn ()->redTeamPlayers ) >= $maxTeamPlayers) {
				$player->sendMessage ( $this->getMsg (  "game.full" ) );
				return;
			}
			if ($this->getPlugIn ()->gameMode == 0) {
				// auto start change if not started yet
				$player->level->getBlockLightAt($blockTouched->x, $blockTouched->y, $blockTouched->z);
				$this->handleNewGame ( $player );
			}
			$this->handleJoinRedTeam ( $player );
			// $player->sendMessage ( "------------------------------" );
			$player->sendMessage ( $this->getMsg ( "team.scores.red-players" ) . " [" . count ( $this->getPlugIn ()->redTeamPlayers ) . "/" . $maxTeamPlayers . "] " . $this->getMsg ( "team.scores.players" ) );
			return;
		}
	}
	
	/**
	 *
	 * Touched Join Blue Team Sign
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onClickJoinBlueTeamSign(Player $player, $blockTouched) {
		$maxTeamPlayers = $this->getSetup ()->getMaxPlayerPerTeam ();
		$joinBluePos = $this->getSetup ()->getSignPos ( CTFSetup::CLICK_SIGN_JOIN_BLUE_TEAM );
		// Join BLUE Team SIGN
		if (round ( $blockTouched->x ) == round ( $joinBluePos->x ) && round ( $blockTouched->y ) == round ( $joinBluePos->y ) && round ( $blockTouched->z ) == round ( $joinBluePos->z )) {
			if (count ( $this->getPlugIn ()->blueTeamPLayers ) >= $maxTeamPlayers) {
				$player->sendMessage ( $this->getMsg ( "game.full" ) );
				return;
			}
			if ($this->getPlugIn ()->gameMode == 0) {
				// auto start change if not started yet
				$player->level->getBlockLightAt($blockTouched->x, $blockTouched->y, $blockTouched->z);
				$this->handleNewGame ( $player );
			}
			$this->handleJoinBlueTeam ( $player );
			$player->sendMessage ( $this->getMsg ( "team.scores.blue-players" ) . " [" . count ( $this->getPlugIn ()->blueTeamPLayers ) . "/" . $maxTeamPlayers . "] " . $this->getMsg ( "team.scores.players" ) );
			return;
		}
	}
	
	/**
	 * Clicked View Game Statistic Sign
	 *
	 * @param Player $player        	
	 * @param unknown $blockTouched        	
	 */
	public function onClickViewGameStatsSign(Player $player, $blockTouched) {
		$statSignPos = $this->getSetup ()->getSignPos ( CTFSetup::CLICK_SIGN_SHOW_GAME_STAT );
		// View Stat SIGN
		if (round ( $blockTouched->x ) == round ( $statSignPos->x ) && round ( $blockTouched->y ) == round ( $statSignPos->y ) && round ( $blockTouched->z ) == round ( $statSignPos->z )) {
			$player->level->getBlockLightAt($blockTouched->x, $blockTouched->y, $blockTouched->z);
			$this->displayTeamScores ( $player );
			return;
		}
	}
	
	/**
	 * re-build game arena
	 *
	 * @param CommandSender $sender        	
	 */
	public function resetGame(CommandSender $sender) {
		$this->getPlugIn ()->gameMode = 0;
		$player->sendMessage ( $player->sendMessage ( $this->getMsg ( "game.resetting" ) ) );
		$this->buildGameArena ( $sender );
	}
	
	/**
	 * Send player to Lobby
	 *
	 * @param Player $player        	
	 */
	public function teleportPlayerToLobby(Player $player) {
		// load this world
		// $levelname = $this->getConfig ( "lobby_world" );
		$levelname = $this->getSetup ()->getLobbyWorldName ();
		// $levelname = $line3;
		$level;
		if (! $player->getServer ()->isLevelGenerated ( $levelname )) {
			$player->getServer ()->generateLevel ( $levelname );
		}
		if (! $player->getServer ()->isLevelLoaded ( $levelname )) {
			$player->getServer ()->loadLevel ( $levelname );
		}
		if ($player->getServer ()->isLevelLoaded ( $levelname )) {
			$level = $player->getServer ()->getLevelByName ( $levelname );
			if ($level == null) {
				$this->log ( "level not found: " . $levelname );
				return;
			}
			$this->getMsg ( "game.resetting" );
			$message = $this->getMsg ( "ctf.spawn_player" ) . " [" . $level->getName () . "]";
			$player->sendMessage ( $message );
			
			$level->getChunk ( $level->getSafeSpawn ()->x, $level->getSafeSpawn ()->z );
			$player->teleport ( $level->getSafeSpawn () );
			// move player to new level
			if ($this->getSetup ()->isEnableSpanwToLobby ()) {
				$lobbyPos = $this->getSetup ()->getLobbyPos ();
				$level->getChunk($lobbyPos->x, $lobbyPos->z);
				$player->teleport ( $lobbyPos );
			}
		}
	}
	
	/**
	 * send player home
	 *
	 * @param Player $player        	
	 */
	public function teleportPlayerToHome(Player $player) {
		$levelhome = $this->getSetup ()->getCTFWorldName ();
		$level;
		if (! $player->getServer ()->isLevelGenerated ( $levelhome )) {
			$player->sendMessage ( $this->getMsg ( "sign.world-not-found" ) );
			return;
		}
		
		if (! $player->getServer ()->isLevelLoaded ( $levelhome )) {
			$player->getServer ()->loadLevel ( $levelhome );
		}
		
		if ($player->getServer ()->isLevelLoaded ( $levelhome )) {
			$level = $player->getServer ()->getLevelByName ( $levelhome );
			if ($level == null) {
				$this->log ( "level not found: " . $levelhome );
				return;
			}
			$message = $this->getMsg ( "sign.teleport.spawn" ) . $level->getName ();
			$player->sendMessage ( $message );
			$level->getChunk($level->getSafeSpawn()->x, $level->getSafeSpawn()->z);
			$player->teleport ( $level->getSafeSpawn() );
			
			$message = $this->getMsg ( "sign.teleport.ctf" ) . $levelhome;
			$ctfGamePos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ENTRY );
			$level->getChunk($ctfGamePos->x, $ctfGamePos->z);
			$player->teleport ( $ctfGamePos );
			$player->sendMessage ( $message );
		}
	}
	
	/**
	 * Build New Game
	 *
	 * @param CommandSender $sender        	
	 */
	public function buildGameArena(CommandSender $sender) {
		$arenaSize = $this->getSetup ()->getArenaSize ();
		$arenaPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION );
		if (! $sender instanceof Player) {
			$sender->sendMessage ( $this->getMsg ( "ctf.error.wrong-sender" ) );
			return;
		}
		$time_start = microtime ( true );
		$level = $sender->getLevel ();
		$this->getBuilder ()->plotArenaMap ( $level, $arenaSize, 8, $arenaPos->x, $arenaPos->y, $arenaPos->z, 1 );
		$this->getPlugIn ()->gameMode = 0;
		
		$time_end = microtime ( true );
		$time = $time_end - $time_start;
		// $this->getPlugIn()->getLogger ()->info (TextFormat::AQUA."Building arena elapse time $time seconds\n");
		$message = "building arena took time ".round($time,2). " seconds\n";
		$sender->sendMessage ( $message );
	}
	
	/**
	 *
	 * Touched Start Button
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onClickStartGameButton($level, Player $player, $blockTouched) {
		$startButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_START_GAME );
		// START BUTTON
		if ((round ( $blockTouched->x ) == round ( $startButtonPos->x ) && round ( $blockTouched->y ) == round ( $startButtonPos->y ) && round ( $blockTouched->z ) == round ( $startButtonPos->z ))) {
			// @fix-1
			// //Check if Team has minimal players
			if ($this->getPlugIn ()->blueTeamPLayers == null || $this->getPlugIn ()->blueTeamPLayers == null) {
				$player->sendMessage ( $this->getMsg ( "game.not-enought-players" ) );
				return;
			}
			if (count ( $this->getPlugIn ()->blueTeamPLayers ) == 0 ) {
				$player->sendMessage ( $this->getMsg ( "game.not-enought-players" ) );
				return;
			}
			if (count ( $this->getPlugIn ()->blueTeamPLayers ) == 0) {
				$player->sendMessage ( $this->getMsg ( "game.not-enought-players" ) );
				return;
			}			
			if ($this->getPlugIn ()->gameMode == 0) {
				$this->handleStartTheGame ( $level );
			} else {
				$output = "-------------------------\n";
				$output .= $this->getMsg ( "game.in-progress" ) . "\n";
				$output .= $this->getMsg ( "game.hit-stop" ) . "\n";
				$output .= "-------------------------\n";
				$player->sendMessage ( $output );
			}
		}
	}
	
	/**
	 * handle start of new game
	 *
	 * @param Level $level        	
	 */
	public function handleStartTheGame(Level $level) {
		// change gamemode
		$this->getPlugIn ()->setGameMode ( 1 );
		// $builder = new CTFArenaBuilder ( $this->getPlugIn() );
		// blue team flag
		$this->getBuilder ()->addBlueTeamFlag ( $level, 171, 11 );
		// add red team flag
		$this->getBuilder ()->addRedTeamFlag ( $level, 171, 14 );
		// add fire and remove start button
		$this->getBuilder ()->addRoundPlayButtons ( $level );
		// remove any blue/red flag from players inventory
		// avoid cheating
		if ($this->getPlugIn ()->blueTeamPLayers != null && count ( $this->getPlugIn ()->blueTeamPLayers ) > 0) {
			foreach ( $this->getPlugIn ()->blueTeamPLayers as $p ) {
				// remove any flag item from player inventory
				$p->getInventory ()->remove ( new Item ( 171 ) );
			}
		}
		
		if ($this->getPlugIn ()->redTeamPlayers != null && count ( $this->getPlugIn ()->redTeamPlayers ) > 0) {
			foreach ( $this->getPlugIn ()->redTeamPlayers as $p ) {
				// remove any flag item from player inventory
				$p->getInventory ()->remove ( new Item ( 171 ) );
			}
		}
		$arenaSize = $this->getSetup ()->getArenaSize ();
		$arenaPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION );
		$this->getBuilder ()->openTeamGates ( $level, $arenaSize, $arenaPos->x + $arenaSize, $arenaPos->y, $arenaPos->z );
		
		// announce game
		$this->getPlugIn ()->currentGameRound ++;
		$output = "";
		$output .= "-------------------------\n";
		$output .= $this->getMsg ( "game.round" ) . " [" . $this->getPlugIn ()->currentGameRound . "/" . $this->getPlugIn ()->maxGameRound . "] " . $this->getMsg ( "game.go" ) . "\n";
		$output .= "-------------------------\n";
		$level->getServer ()->broadcastMessage ( $output );
		// change gamemode
		$this->getPlugIn ()->setGameMode ( 1 );
	}
	
	/**
	 *
	 * Touched Stop Button
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onClickStopGameButton($level, Player $player, $blockTouched) {
		$stopButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_STOP_GAME );
		// STOP BUTTON
		if ((round ( $blockTouched->x ) == round ( $stopButtonPos->x ) && round ( $blockTouched->y ) == round ( $stopButtonPos->y ) && round ( $blockTouched->z ) == round ( $stopButtonPos->z ))) {
			// set the floor to be breakable
			// blue team flag
			$this->getBuilder ()->addBlueTeamFlag ( $level, 171, 11 );
			// add red team flag
			$this->getBuilder ()->addRedTeamFlag ( $level, 171, 14 );
			// add fire
			$this->getBuilder ()->addGameButtons ( $level );
			// brodcast
			$this->handleBroadcastFinalScore ( $player, true );
			// reset stats
			$this->getPlugIn ()->redTeamWins = 0;
			$this->getPlugIn ()->blueTeamWins = 0;
			$this->getPlugIn ()->currentGameRound = 0;
			
			// remove players
			$this->handleStopTheGame ();
			$arenaSize = $this->getSetup ()->getArenaSize ();
			$arenaPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ARENA_POSITION );
			// close gates
			$this->getBuilder ()->closeTeamGates ( $level, $arenaSize, $arenaPos->x + $arenaSize, $arenaPos->y, $arenaPos->z );
		}
	}
	public function handleBroadcastFinalScore(Player $player, $toEveryone = false) {
		// brodcast
		$output = "";
		$output .= "------------------------------\n";
		$output .= $this->getMsg ( "game.final.title" ) . "\n";
		$output .= "------------------------------\n";
		$output .= $this->getMsg ( "game.final.red-team" ) . $this->getPlugIn ()->redTeamWins . "\n";
		$output .= $this->getMsg ( "game.final.blue-team" ) . $this->getPlugIn ()->blueTeamWins . "\n";
		$output .= "------------------------------\n";
		// same score then it's a tie
		if ($this->getPlugIn ()->redTeamWins == $this->getPlugIn ()->blueTeamWins) {
			$output .= $this->getMsg ( "game.final.draw" ) . "\n";
		} elseif ($this->getPlugIn ()->redTeamWins > $this->getPlugIn ()->blueTeamWins) {
			$output .= $this->getMsg ( "game.final.red-win" ) . "\n";
		} elseif ($this->getPlugIn ()->blueTeamWins > $this->getPlugIn ()->redTeamWins) {
			$output .= $this->getMsg ( "game.final.blue-win" ) . "\n";
		}
		$output .= "------------------------------\n";
		if ($toEveryone) {
			$player->getServer ()->broadcastMessage ( $output );
			// $player->getServer()->broadcast($output, Server::BROADCAST_CHANNEL_USERS);
		} else {
			$player->sendMessage ( $output );
		}
	}
	
	/**
	 *
	 * Clicked Stop Button
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onClickLeaveGameButton($level, $player, $blockTouched) {
		$leaveButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_LEAVE_GAME );
		// LEAVE BUTTON
		if ((round ( $blockTouched->x ) == $leaveButtonPos->x && round ( $blockTouched->y ) == $leaveButtonPos->y && round ( $blockTouched->z ) == $leaveButtonPos->z)) {
			// send all players to waiting room
			$this->handleLeaveTheGame ( $player );
		}
	}
	
	/**
	 * Handle Player Disconnect, Death or Kicked
	 *
	 * @param Player $player        	
	 */
	public function handlePlayerQuit(Player $player) {
		// @fix1
		// check if the player
		if (isset ( $this->getPlugIn ()->redTeamPlayers [$player->getName ()] )) {
			$msg = $player->getName () . $this->getMsg ( "team.left-red" );
			$player->getServer ()->broadcastMessage ( $msg );
			unset ( $this->getPlugIn ()->redTeamPlayers [$player->getName ()] );
			$player->setNameTag ( $player->getName () );
			// check if this player has the flag
			if ($player->getInventory ()->contains ( new Item ( 171 ) )) {
				// put this flag back to team
				// assume red team only enermy flag - blue team
				$msg = $player->getName () . " [" . $this->getMsg ( "ctf.return-flag" ) . "]";
				$player->getServer ()->broadcastMessage ( $msg );
				
				$this->getBuilder ()->addBlueTeamFlag ( $player->getLevel (), 171, 11 );
				// remove it from player
				$player->getInventory ()->remove ( new Item ( 171 ) );
			}
		}
		
		if (isset ( $this->getPlugIn ()->blueTeamPlayers [$player->getName ()] )) {
			$msg = $player->getName () . $this->getMsg ( "team.left-blue" );
			$player->getServer ()->broadcastMessage ( $msg );
			unset ( $this->getPlugIn ()->blueTeamPlayers [$player->getName ()] );
			$player->setNameTag ( $player->getName () );
			if ($player->getInventory ()->contains ( new Item ( 171 ) )) {
				// put this flag back to team
				$msg = $player->getName () . " [" . $this->getMsg ( "ctf.return-flag" ) . "]";
				$player->getServer ()->broadcastMessage ( $msg );
				
				$this->getBuilder ()->addRedTeamFlag ( $player->getLevel (), 171, 14 );
				// remove it from player
				$player->getInventory ()->remove ( new Item ( 171 ) );
			}
		}
		
		if ($this->getPlugIn ()->gameMode > 0) {
			// auto stop the game and declare winner if no team member left in anyone team
			if (count ( $this->getPlugIn ()->redTeamPlayers ) == 0 && count ( $this->getPlugIn ()->blueTeamPLayers ) > 0) {
				$message = $this->getMsg ( "team.red-no-players" );
				$player->getServer ()->broadcastMessage ( $message );
				// blue team win
				$this->getPlugIn ()->blueTeamWins ++;
				$this->handleStopTheGame ();
				$this->handleBroadcastFinalScore ( $player, true );
			} elseif (count ( $this->getPlugIn ()->redTeamPlayers ) > 0 && count ( $this->getPlugIn ()->blueTeamPLayers ) == 0) {
				$message = $this->getMsg ( "team.blue-no-players" );
				$player->getServer ()->broadcastMessage ( $message );
				// red team win
				$this->getPlugIn ()->redTeamWins ++;
				$this->handleStopTheGame ();
				$this->handleBroadcastFinalScore ( $player, true );
			} elseif (count ( $this->getPlugIn ()->redTeamPlayers ) == 0 && count ( $this->getPlugIn ()->blueTeamPLayers ) == 0) {
				$message = $this->getMsg ( "team.no-players" );
				$player->getServer ()->broadcastMessage ( $message );
				// draw
				$this->handleStopTheGame ();
				$this->handleBroadcastFinalScore ( $player, true );
			}
		}
	}
	
	/**
	 * Handle player entry to CTF game world
	 *
	 * @param Player $player        	
	 */
	public function handlePlayerEntry(Player $player) {
		// send player to lobby if specify
		if ($this->getSetup ()->isEnableSpanwToLobby ()) {
			$lobbyPos = $this->getSetup ()->getLobbyPos ();
			$player->teleport ( $lobbyPos );
			return;
		}
		// player should be outside of arena
		$gameWorld = $this->getSetup ()->getCTFWorldName ();
		if (strtolower ( $player->level->getName () ) == strtolower ( $gameWorld )) {
			// send entry point
			$gameWorldPos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ENTRY );
			$player->level->getChunk($gameWorldPos->x, $gameWorldPos->z);
			$player->level->getBlockLightAt($gameWorldPos->x, $gameWorldPos->y, $gameWorldPos->z);
			$player->teleport ( new Vector3 ( $gameWorldPos->x, $gameWorldPos->y, $gameWorldPos->z ) );
			return;
		}
		
		//grant player permissions
		$this->grantPlayerDefaultPermissions($player);
	}
	
	/**
	 * Give default permissions to players
	 * @param Player $player
	 */
	private function grantPlayerDefaultPermissions(Player $player) {
		$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_HOME, TRUE);
		$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_JOIN_BLUE_TEAM, TRUE);		
		$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_JOIN_RED_TEAM, TRUE);		
		$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_STATS, TRUE);
		$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_LEAVE, TRUE);
		$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_START, TRUE);
		$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_STOP, TRUE);
		if ($player->isOp()) {
			$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_CREATE_ARENA, TRUE);
			$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_RESET_ARENA, TRUE);
			$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_BLOCK_DISPLAY_ON, TRUE);
			$player->addAttachment($this->getPlugIn(),self::CTF_PERMISSION_BLOCK_DISPLAY_OFF, TRUE);
		}
	}

}
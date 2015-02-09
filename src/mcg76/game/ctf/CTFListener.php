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
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\command\defaults\TeleportCommand;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;

/**
 * MCG76 CTF Listener
 *
 * Copyright (C) 2014 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *      
 */
class CTFListener extends MiniGameBase implements Listener {
	public function __construct(CTFPlugIn $plugin) {
		parent::__construct ( $plugin );
	}
	public function onBlockBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer ();
		$b = $event->getBlock ();
		if ($this->getPlugin ()->pos_display_flag == 1) {
			$event->getPlayer ()->sendMessage ( "BREAKED: [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
		}
		// @fix1- team can only break enermy flag and not own
		$redTeamFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_RED_TEAM );
		if ((round ( $b->x ) == round ( $redTeamFlagPos->x ) && round ( $b->y ) == round ( $redTeamFlagPos->y ) && round ( $b->z ) == round ( $redTeamFlagPos->y ))) {
			if (isset ( $this->pgin->redTeamPlayers [$player->getName ()] )) {
				// update again to fix color issue
				$this->getBuilder ()->addBlueTeamFlag ( $player->getLevel (), 171, 14 );
				$event->setCancelled ( true );
			}
		}
		$blueTeamFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_BLUE_TEAM );
		if ((round ( $b->x ) == round ( $blueTeamFlagPos->x ) && round ( $b->y ) == round ( $blueTeamFlagPos->y ) && round ( $b->z ) == round ( $blueTeamFlagPos->z ))) {
			if (isset ( $this->pgin->blueTeamPLayers [$player->getName ()] )) {
				// update again to fix color issue
				$this->getBuilder ()->addBlueTeamFlag ( $player->getLevel (), 171, 11 );
				$event->setCancelled ( true );
			}
		}		
		// @fix #2 stop player break anything else other than the flags
		if (strtolower ( $player->level->getName () ) == strtolower ( $this->getSetup ()->getCTFWorldName () )) {
			if ($this->getSetup ()->isCTFWorldBlockBreakDisable () || ! $player->isOp()) {
				if ($b->getId()!=171) {
					$event->setCancelled ( true );
				}
			}
		}
	}
	public function onBlockPlace(BlockPlaceEvent $event) {
		$player = $event->getPlayer ();
		$b = $event->getBlock ();
		if ($this->getPlugin ()->pos_display_flag == 1) {
			$player->sendMessage ( "PLACED:*" . $b->getName () . " [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
		}
		if ($this->getPlugin ()->gameMode == 1) {
			// check if the flag if the enermy one
			if (isset ( $this->getPlugin ()->blueTeamPLayers [$player->getName ()] )) {
				$this->getManager ()->checkBlueTeamCapturedEnermyFlag ( $player, $player->level, $b );
				return;
			}
			if (isset ( $this->getPlugin ()->redTeamPlayers [$player->getName ()] )) {
				$this->getManager ()->checkRedTeamCapturedEnermyFlag ( $player, $player->level, $b );
				return;
			}
		}
		// @fix #2 stop player place anything else other than the flags
		if (strtolower ( $player->level->getName () ) == strtolower ( $this->getSetup ()->getCTFWorldName () )) {
			if ($this->getSetup ()->isCTFWorldBlockPlaceDisable () || !$player->isOp()) {
				if ($b->getId()!=171) {
					$event->setCancelled ( true );
				}
			}
		}	
	}
	
	/**
	 * OnPlayerJoin
	 *
	 * @param PlayerJoinEvent $event        	
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			$event->getPlayer ()->addAttachment ( $this->getPlugin (), "mcg76.plugin.ctf", true );
			if ($this->getManager () == null) {
				$this->log ( " getManager is null!" );
			} else {
				$this->getManager ()->handlePlayerEntry ( $event->getPlayer () );
			}
		}
	}
	public function onPlayerRespawn(PlayerRespawnEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if ($this->getManager () == null) {
				$this->log ( " getManager is null!" );
			} else {
				$this->getManager ()->handlePlayerEntry ( $event->getPlayer () );
			}
		}
	}
	
	/**
	 *
	 * @param PlayerQuitEvent $event        	
	 */
	public function onQuit(PlayerQuitEvent $event) {
		// @fix - remove captured flag
		if ($event->getPlayer () instanceof Player) {
			$this->getManager ()->handlePlayerQuit ( $event->getPlayer () );
		}
	}
	
	/**
	 * OnPlayerInteract
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) {
		$blockTouched = $event->getBlock ();
		$player = $event->getPlayer ();
		$level = $event->getPlayer ()->getLevel ();
		$b = $event->getBlock ();
		if ($this->getPlugin ()->pos_display_flag == 1) {
			$event->getPlayer ()->sendMessage ( "TOUCHED: [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
		}
		// process clickable blocks
		$this->getManager ()->onClickStartGameButton ( $level, $player, $blockTouched );
		$this->getManager ()->onClickLeaveGameButton ( $level, $player, $blockTouched );
		$this->getManager ()->onClickStopGameButton ( $level, $player, $blockTouched );
		
		// process clickable signs
		$this->getManager ()->onClickJoinRedTeamSign ( $player, $blockTouched );
		$this->getManager ()->onClickJoinBlueTeamSign ( $player, $blockTouched );
		$this->getManager ()->onClickNewGameSign ( $player, $blockTouched );
		$this->getManager ()->onClickViewGameStatsSign ( $player, $blockTouched );
		
		// process sign setup actions
		if ($this->getPlugin ()->setupModeAction != "") {
			$this->getSetup ()->handleClickSignSetup ( $player, $this->getPlugin ()->setupModeAction, new Position ( $b->x, $b->y, $b->z ) );
			$this->getSetup ()->handleSetBlockSetup ( $player, $this->getPlugin ()->setupModeAction, $b->getId () );
		}
	}
	public function onPlayerDeath(PlayerDeathEvent $event) {
		// player held the flag until death
		if ($event->getEntity () instanceof Player) {
			$this->getManager ()->handlePlayerQuit ( $event->getEntity () );
		}
	}
	public function onPlayerKick(PlayerKickEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			$this->getManager ()->handlePlayerQuit ( $event->getPlayer () );
		}
	}
	
	/**
	 * Watch sign change
	 * @fix01
	 *
	 * @param SignChangeEvent $event        	
	 */
	public function onSignChange(SignChangeEvent $event) {
		$player = $event->getPlayer ();
		$block = $event->getBlock ();
		$line1 = $event->getLine ( 0 );
		$line2 = $event->getLine ( 1 );
		$line3 = $event->getLine ( 2 );
		$line4 = $event->getLine ( 3 );
		
		if ($line1 != null && $line1 == CTFManager::CTF_COMMAND) {
			if ($line2 != null && $line2 == CTFManager::CTF_COMMAND_HOME) {
				
				$gameworld = $this->getSetup ()->getCTFWorldName ();
				$gamePos = $this->getSetup ()->getGamePos ( CTFSetup::CTF_GAME_ENTRY );
				$gameX = $gamePos->x;
				$gameY = $gamePos->y;
				$gameZ = $gamePos->z;
				
				$levelhome = $gameworld;
				$level = null;
				if (! $player->getServer ()->isLevelGenerated ( $levelhome )) {
					$player->sendMessage ( $this->getMsg ( "sign.world-not-found" ) . " [" . $levelhome . "]" );
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
					$message = $this->getMsg ( "sign.teleport.world" ) . " [" . $level->getName () . "]";
					$player->sendMessage ( $message );
					$player->teleport ( $level->getSpawnLocation () );
					if ($gameX != null && $gameY != null && $gameZ != null) {
						$player->teleport ( new Vector3 ( $gameX, $gameY, $gameZ ) );
						$message = $this->getMsg ( "sign.teleport.game" );
						$player->sendMessage ( $message );
					}
					$message = $this->getMsg ( "sign.done" );
					$player->sendMessage ( $message );
				}
				return;
			}
			
			if ($line2 != null && $line2 == CTFManager::CTF_COMMAND_JOIN_BLUE_TEAM) {
				$this->getManager ()->handleJoinBlueTeam ( $player );
				return;
			}
			if ($line2 != null && $line2 == CTFManager::CTF_COMMAND_JOIN_RED_TEAM) {
				$this->getManager ()->handleJoinRedTeam ( $player );
				return;
			}
			if ($line2 != null && $line2 == CTFManager::CTF_COMMAND_LEAVE) {
				$this->getManager ()->handleLeaveTheGame ( $player );
				return;
			}
		}
	}
}
<?php

namespace mcg76\game\ctf;

use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\block\Block;
use pocketmine\Player;

/**
 * MCG76 CTF Setup
 *
 * Copyright (C) 2014 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *      
 */
class CTFSetup extends MiniGameBase {
	const DIR_ARENA = "arena/";

	const LOBBY_POSITION = 4001;
	const CLICK_SIGN_JOIN_RED_TEAM = 1000;
	const CLICK_SIGN_JOIN_BLUE_TEAM = 1001;
	const CLICK_SIGN_SHOW_GAME_STAT = 1002;
	const CLICK_SIGN_NEW_GAME = 1003;
	const CLICK_BUTTON_START_GAME = 2001;
	const CLICK_BUTTON_STOP_GAME = 2002;
	const CLICK_BUTTON_LEAVE_GAME = 2003;
	const CTF_FLAG_RED_TEAM = 3000;
	const CTF_FLAG_RED_TEAM_ENEMY = 3001;
	const CTF_FLAG_BLUE_TEAM = 3002;
	const CTF_FLAG_BLUE_TEAM_ENEMY = 3003;
	const CTF_BLOCK_ID_BORDER_FENCE = 4001;
	const CTF_BLOCK_ID_DEFENCE_WALL_BLUE_TEAM = 4002;
	const CTF_BLOCK_ID_DEFENCE_WALL_RED_TEAM = 4003;
	const CTF_GAME_ENTRY = 5000;
	const CTF_GAME_ARENA_POSITION = 5001;
	const CTF_GAME_ARENA_POSITION_ENTRY_RED_TEAM = 5002;
	const CTF_GAME_ARENA_POSITION_ENTRY_BLUE_TEAM = 5003;
	const CTF_GAME_ARENA_POSITION_WAITING_ROOM = 5004;
	
	// arena building material types
	const CTF_ARENA_WALL = "arenaWall";
	const CTF_ARENA_FLOOR = "arenaFloor";
	const CTF_ARENA_FLOOR_BLUE_TEAM = "arenaFloorBlueTeam";
	const CTF_ARENA_FLOOR_RED_TEAM = "arenaFloorRedTeam";
	
	/**
	 * Constructor
	 *
	 * @param CTFPlugIn $plugin        	
	 */
	public function __construct(CTFPlugIn $plugin) {
		parent::__construct ( $plugin );
		$this->init ();
	}
	private function init() {
		@mkdir ( $this->plugin->getDataFolder () . self::DIR_ARENA, 0777, true );
		$this->getArenaBuildingBlocks ( self::CTF_ARENA_WALL );
		$this->getArenaBuildingBlocks ( self::CTF_ARENA_FLOOR );
		$this->getArenaBuildingBlocks ( self::CTF_ARENA_FLOOR_BLUE_TEAM );
		$this->getArenaBuildingBlocks ( self::CTF_ARENA_FLOOR_RED_TEAM );
	}
	/**
	 * Arena building blocks
	 *
	 * @param unknown $blockType        	
	 * @return \pocketmine\utils\Config
	 */
	public function getArenaBuildingBlocks($blockType) {
		if (! (file_exists ( $this->getPlugin ()->getDataFolder () . self::DIR_ARENA . strtolower ( $blockType ) . ".yml" ))) {
			
			if ($blockType == self::CTF_ARENA_WALL) {
				return new Config ( $this->plugin->getDataFolder () . self::DIR_ARENA . strtolower ( self::CTF_ARENA_WALL ) . ".yml", Config::YAML, array (
						"blockType" => self::CTF_ARENA_WALL,
						"blocks" => array (
								"stone1" => "50",
								"stone2" => "1",
								"stone3" => "1",
								"stone4" => "1",
								"stone5" => "1",
								"stone6" => "20",
								"stone7" => "1",
								"stone8" => "1",
								"stone9" => "1" 
						) 
				) );
			} elseif ($blockType == self::CTF_ARENA_FLOOR) {
				return new Config ( $this->plugin->getDataFolder () . self::DIR_ARENA . strtolower ( self::CTF_ARENA_FLOOR ) . ".yml", Config::YAML, array (
						"blockType" => self::CTF_ARENA_FLOOR,
						"blocks" => array (
								"stone1" => "1",
								"stone3" => "10",
								"stone4" => "1",
								"stone5" => "1",
								"stone2" => "8",
								"stone6" => "1",
								"stone7" => "1",
								"stone8" => "1",
								"stone9" => "1",
								"stone10" => "1",
								"stone11" => "1",
								"stone12" => "1" 
						) 
				) );
			} elseif ($blockType == self::CTF_ARENA_FLOOR_BLUE_TEAM) {
				return new Config ( $this->plugin->getDataFolder () . self::DIR_ARENA . strtolower ( self::CTF_ARENA_FLOOR_BLUE_TEAM ) . ".yml", Config::YAML, array (
						"blockType" => self::CTF_ARENA_FLOOR_BLUE_TEAM,
						"blocks" => array (
								"stone1" => "21",
								"stone3" => "10",
								"stone4" => "1",
								"stone5" => "21",
								"stone2" => "8",
								"stone6" => "21",
								"stone7" => "1",
								"stone8" => "1",
								"stone9" => "48",
								"stone10" => "48",
								"stone11" => "21",
								"stone12" => "48" 
						) 
				) );
			} elseif ($blockType == self::CTF_ARENA_FLOOR_RED_TEAM) {
				return new Config ( $this->plugin->getDataFolder () . self::DIR_ARENA . strtolower ( self::CTF_ARENA_FLOOR_RED_TEAM ) . ".yml", Config::YAML, array (
						"blockType" => self::CTF_ARENA_FLOOR_RED_TEAM,
						"blocks" => array (
								"stone1" => "74",
								"stone3" => "10",
								"stone4" => "1",
								"stone5" => "74",
								"stone2" => "8",
								"stone6" => "1",
								"stone7" => "1",
								"stone8" => "48",
								"stone9" => "74",
								"stone10" => "48",
								"stone11" => "74",
								"stone12" => "48" 
						) 
				) );
			}
		} else {
			return new Config ( $this->getPlugin ()->getDataFolder () . self::DIR_ARENA . strtolower ( $blockType ) . ".yml", Config::YAML, array () );
		}
	}
	
	public function isCTFWorldBlockBreakDisable() {
		return $this->getConfig("disable_CTF_world_blockBreak", true );
	}
	
	public function isCTFWorldBlockPlaceDisable() {
		return $this->getConfig("disable_CTF_world_blockPlace", true );
	}
	
	public function getMessageLanguage() {
		$configlang = $this->getConfig ( "language" );
		if ($configlang == null) {
			$configlang = "EN";
		}
		return $configlang;
	}
	public function getMaxGameRounds() {
		$maxRounds = $this->getConfig ( "maximum_game_rounds" );
		if ($maxRounds != null && $maxRounds != $this->getPlugIn ()->maxGameRound) {
			$this->getPlugIn ()->maxGameRound = $maxRounds;
		}
		return $maxRounds;
	}
	public function getRoundWaitTime() {
		$resetValue = $this->getConfig ( "round_wait_time" );
		if ($resetValue == null) {
			$resetValue = 300;
		}
		return $resetValue;
	}
	public function getMaxPlayerPerTeam() {
		$maxTeamPlayers = $this->getConfig ( "maximum_team_players" );
		if ($maxTeamPlayers == null) {
			$maxTeamPlayers = 10;
		}
		return $maxTeamPlayers;
	}
	public function getArenaName() {
		$arenaName = $this->getConfig ( "ctf_arena_name" );
		return $arenaName;
	}
	public function getBlockId($typeId) {
		switch ($typeId) {
			case self::CTF_BLOCK_ID_BORDER_FENCE :
				$blockId = $this->getConfig ( "ctf_border_fence" );
				if ($blockId == null) {
					$blockId = Block::FENCE;
				}
				return $blockId;
				break;
			case self::CTF_BLOCK_ID_DEFENCE_WALL_BLUE_TEAM :
				$blockId = $this->getConfig ( "blue_team_defence_wall" );
				if ($blockId == null) {
					$blockId = Block::FENCE;
				}
				return $blockId;
				break;
			case self::CTF_BLOCK_ID_DEFENCE_WALL_RED_TEAM :
				$blockId = $this->getConfig ( "red_team_defence_wall" );
				if ($blockId == null) {
					$blockId = Block::FENCE;
				}
				return $blockId;
				break;
			default :
				return Block::AIR;
		}
	}
	public function getArenaSize() {
		$arenaSize = $this->getConfig ( "ctf_arena_size" );
		return $arenaSize;
	}
	public function getArenaPos() {
		$dataX = $this->getConfig ( "ctf_arena_x" );
		$dataY = $this->getConfig ( "ctf_arena_y" );
		$dataZ = $this->getConfig ( "ctf_arena_z" );
		return new Position ( $dataX, $dataY, $dataZ );
	}
	public function getCTFWorldName() {
		$gameworld = $this->getConfig ( "ctf_game_world" );
		return $gameworld;
	}
	public function isEnableSpanwToLobby() {
		$enableSpawnLobby = $this->getConfig ( "enable_spaw_lobby" );
		if ($enableSpawnLobby != null && $enableSpawnLobby == "yes") {
			return true;
		}
		return false;
	}
	public function getLobbyWorldName() {
		return $this->getConfig ( "lobby_world" );
	}
	public function getLobbyPos() {
		$lobbyX = $this->getConfig ( "lobby_x" );
		$lobbyY = $this->getConfig ( "lobby_y" );
		$lobbyZ = $this->getConfig ( "lobby_z" );
		return new Position ( $lobbyX, $lobbyY, $lobbyZ );
	}
	public function getGamePos($posTypeId) {
		switch ($posTypeId) {
			case self::CTF_GAME_ARENA_POSITION_ENTRY_RED_TEAM :
				$sx = $this->getConfig ( "ctf_red_team_spawn_x" );
				$sy = $this->getConfig ( "ctf_red_team_spawn_y" );
				$sz = $this->getConfig ( "ctf_red_team_spawn_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CTF_GAME_ARENA_POSITION_ENTRY_BLUE_TEAM :
				$sx = $this->getConfig ( "ctf_blue_team_spawn_x" );
				$sy = $this->getConfig ( "ctf_blue_team_spawn_y" );
				$sz = $this->getConfig ( "ctf_blue_team_spawn_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CTF_GAME_ARENA_POSITION :
				$sx = $this->getConfig ( "ctf_arena_x" );
				$sy = $this->getConfig ( "ctf_arena_y" );
				$sz = $this->getConfig ( "ctf_arena_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CTF_GAME_ARENA_POSITION_WAITING_ROOM :
				$sx = $this->getConfig ( "ctf_waiting_room_x" );
				$sy = $this->getConfig ( "ctf_waiting_room_y" );
				$sz = $this->getConfig ( "ctf_waiting_room_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CTF_GAME_ENTRY :
				$gameX = $this->getConfig ( "ctf_game_x" );
				$gameY = $this->getConfig ( "ctf_game_y" );
				$gameZ = $this->getConfig ( "ctf_game_z" );
				return new Position ( $gameX, $gameY, $gameZ );
				break;
			default :
				return null;
		}
	}
	public function getButtonPos($buttonTypeId) {
		switch ($buttonTypeId) {
			case self::CLICK_BUTTON_STOP_GAME :
				$sx = $this->getConfig ( "ctf_stop_button_1_x" );
				$sy = $this->getConfig ( "ctf_stop_button_1_y" );
				$sz = $this->getConfig ( "ctf_stop_button_1_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CLICK_BUTTON_START_GAME :
				$sx = $this->getConfig ( "ctf_start_button_1_x" );
				$sy = $this->getConfig ( "ctf_start_button_1_y" );
				$sz = $this->getConfig ( "ctf_start_button_1_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CLICK_BUTTON_LEAVE_GAME :
				$sx = $this->getConfig ( "ctf_leave_button_1_x" );
				$sy = $this->getConfig ( "ctf_leave_button_1_y" );
				$sz = $this->getConfig ( "ctf_leave_button_1_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			default :
				return null;
		}
	}
	public function getFlagPos($flagTypeId) {
		switch ($flagTypeId) {
			case self::CTF_FLAG_RED_TEAM :
				$sx = $this->getConfig ( "ctf_red_team_flag_x" );
				$sy = $this->getConfig ( "ctf_red_team_flag_y" );
				$sz = $this->getConfig ( "ctf_red_team_flag_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CTF_FLAG_RED_TEAM_ENEMY :
				$sx = $this->getConfig ( "ctf_red_team_enermy_flag_x" );
				$sy = $this->getConfig ( "ctf_red_team_enermy_flag_y" );
				$sz = $this->getConfig ( "ctf_red_team_enermy_flag_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CTF_FLAG_BLUE_TEAM :
				$sx = $this->getConfig ( "ctf_blue_team_flag_x" );
				$sy = $this->getConfig ( "ctf_blue_team_flag_y" );
				$sz = $this->getConfig ( "ctf_blue_team_flag_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CTF_FLAG_BLUE_TEAM_ENEMY :
				$sx = $this->getConfig ( "ctf_blue_team_enermy_flag_x" );
				$sy = $this->getConfig ( "ctf_blue_team_enermy_flag_y" );
				$sz = $this->getConfig ( "ctf_blue_team_enermy_flag_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			default :
				return null;
		}
	}
	public function getSignPos($signTypeId) {
		switch ($signTypeId) {
			case self::CLICK_SIGN_SHOW_GAME_STAT :
				$sx = $this->getConfig ( "ctf_stat_sign_x" );
				$sy = $this->getConfig ( "ctf_stat_sign_y" );
				$sz = $this->getConfig ( "ctf_stat_sign_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CLICK_SIGN_JOIN_BLUE_TEAM :
				$sx = $this->getConfig ( "ctf_blue_team_join_sign1_x" );
				$sy = $this->getConfig ( "ctf_blue_team_join_sign1_y" );
				$sz = $this->getConfig ( "ctf_blue_team_join_sign1_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CLICK_SIGN_JOIN_RED_TEAM :
				$sx = $this->getConfig ( "ctf_red_team_join_sign1_x" );
				$sy = $this->getConfig ( "ctf_red_team_join_sign1_y" );
				$sz = $this->getConfig ( "ctf_red_team_join_sign1_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			case self::CLICK_SIGN_NEW_GAME :
				$sx = $this->getConfig ( "ctf_new_sign_x" );
				$sy = $this->getConfig ( "ctf_new_sign_y" );
				$sz = $this->getConfig ( "ctf_new_sign_z" );
				return new Position ( $sx, $sy, $sz );
				break;
			default :
				return null;
		}
	}
	
	/**
	 * Handle Click Sign Setup Actions
	 *
	 * @param Player $player        	
	 * @param unknown $setupAction        	
	 * @param Position $pos        	
	 */
	public function handleClickSignSetup(Player $player, $setupAction, Position $pos) {
		// handle setup selection
		if ($setupAction == CTFManager::CTF_COMMAND_SET_SIGN_VIEW_STATS) {
			$this->getPlugIn ()->setupModeAction = "";
			if ($this->setSignPosViewStats ( $pos )) {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.success" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			} else {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.failed" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			}
			return;
		}
		if ($setupAction == CTFManager::CTF_COMMAND_SET_SIGN_NEW_GAME) {
			$this->getPlugIn ()->setupModeAction = "";
			if ($this->setSignPosNewGame ( $pos )) {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.success" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			} else {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.failed" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			}
			return;
		}
		if ($setupAction == CTFManager::CTF_COMMAND_SET_SIGN_JOIN_BLUE_TEAM) {
			$this->getPlugIn ()->setupModeAction = "";
			if ($this->setSignPosJoinBlue ( $pos )) {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.success" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			} else {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.failed" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			}
			return;
		}
		if ($setupAction == CTFManager::CTF_COMMAND_SET_SIGN_JOIN_RED_TEAM) {
			$this->getPlugIn ()->setupModeAction = "";
			if ($this->setSignPosJoinRed ( $pos )) {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.success" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			} else {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.failed" ) . "\n" . round ( $pos->x ) . " " . round ( $pos->y ) . " " . round ( $pos->z ) );
			}
			return;
		}
	}
	
	/**
	 * Setup Sign for View Stats
	 *
	 * @param Position $pos        	
	 * @return boolean
	 */
	public function setSignPosViewStats(Position $pos) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "ctf_stat_sign_x", round ( $pos->x ) );
			$config->set ( "ctf_stat_sign_y", round ( $pos->y ) );
			$config->set ( "ctf_stat_sign_z", round ( $pos->z ) );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	/**
	 * Setup Sign for Join Blue Team
	 *
	 * @param Position $pos        	
	 * @return boolean
	 */
	public function setSignPosJoinBlue(Position $pos) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "ctf_blue_team_join_sign1_x", round ( $pos->x ) );
			$config->set ( "ctf_blue_team_join_sign1_y", round ( $pos->y ) );
			$config->set ( "ctf_blue_team_join_sign1_z", round ( $pos->z ) );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	/**
	 * Setup Sign for Join Red Team
	 *
	 * @param Position $pos        	
	 * @return boolean
	 */
	public function setSignPosJoinRed(Position $pos) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "ctf_red_team_join_sign1_x", round ( $pos->x ) );
			$config->set ( "ctf_red_team_join_sign1_y", round ( $pos->y ) );
			$config->set ( "ctf_red_team_join_sign1_z", round ( $pos->z ) );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	/**
	 * Setup Sign for New Game
	 *
	 * @param Position $pos        	
	 * @return boolean
	 */
	public function setSignPosNewGame(Position $pos) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "ctf_new_sign_x", round ( $pos->x ) );
			$config->set ( "ctf_new_sign_y", round ( $pos->y ) );
			$config->set ( "ctf_new_sign_z", round ( $pos->z ) );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	
	/**
	 * Handle Set Block Setup Action
	 * 	  
	 * @param Player $player        	
	 * @param unknown $setupAction        	
	 * @param Position $pos        	
	 */
	public function handleSetBlockSetup(Player $player, $setupAction, $blockId) {
		// handle setup selection
		if ($setupAction == CTFManager::CTF_COMMAND_SETBLOCK_ID_TEAM_BORDER) {
			$this->getPlugIn ()->setupModeAction = "";
			if ($this->setBorderFenceBlock ( $blockId )) {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.success" ) . "\n" . "set block id:" . $blockId );
			} else {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.failed" ) . "\n" );
			}
			return;
		}
		
		if ($setupAction == CTFManager::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_BLUE_TEAM) {
			$this->getPlugIn ()->setupModeAction = "";
			if ($this->setBlueTeamDefenceWallBlock ( $blockId )) {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.success" ) . "\n" . "set block id :" . $blockId );
			} else {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.failed" ) . "\n" );
			}
			return;
		}
		
		if ($setupAction == CTFManager::CTF_COMMAND_SETBLOCK_ID_DEFENCE_WALL_RED_TEAM) {
			$this->getPlugIn ()->setupModeAction = "";
			if ($this->setRedTeamDefenceWallBlock ( $blockId )) {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.success" ) . "\n" . "set block id :" . $blockId );
			} else {
				$player->sendMessage ( $this->getMsg ( "ctf.setup.failed" ) . "\n" );
			}
			return;
		}
	}
	
	/**
	 * Setup Border Fence Block Type
	 * # Block Id must be a valid PE block id
	 * # 85 - wood fence
	 * # 101 - iron fence
	 * # 98 - stone
	 * # 45 - brick
	 * # 48 - mossy stone
	 *
	 * @param Position $pos        	
	 * @return boolean
	 */
	public function setBorderFenceBlock($blockId) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "ctf_border_fence", $blockId );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	
	/**
	 * Setup Blue Team Defence Wall Block Type
	 *
	 * @param Position $pos        	
	 * @return boolean
	 */
	public function setBlueTeamDefenceWallBlock($blockId) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "blue_team_defence_wall", $blockId );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	
	/**
	 * Setup Red Team Defence Wall Block Type
	 *
	 * @param Position $pos        	
	 * @return boolean
	 */
	public function setRedTeamDefenceWallBlock($blockId) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "red_team_defence_wall", $blockId );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
}
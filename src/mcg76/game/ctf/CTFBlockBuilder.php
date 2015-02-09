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
use pocketmine\utils\Cache;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\entity\FallingBlock;
use pocketmine\nbt\NBT;
use pocketmine\item\ItemBlock;
use pocketmine\block\SignPost;
use pocketmine\item\Sign;
use pocketmine\item\Item;
use pocketmine\block\Liquid;

/**
 * CTF Arena Builder
 *
 * Copyright (C) 2014 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *      
 */
class CTFBlockBuilder extends MiniGameBase  {
	public $boardsize = 16;
	public $wallBlocksTypes = [ ];
	public $floorBlocksTypes = [ ];
	public $blueTeamFloorBlocks = [ ];
	public $redTeamFloorBlocks = [ ];
	
	/**
	 * Constructor
	 *
	 * @param CTFPlugIn $pg        	
	 */
	public function __construct(CTFPlugIn $plugin) {
		parent::__construct ( $plugin );
		$this->initBlockTypes ();
	}
	
	/**
	 * Initialize Building Blocks
	 */
	private function initBlockTypes() {
		$wallBlocks = $this->getSetup ()->getArenaBuildingBlocks ( CTFSetup::CTF_ARENA_WALL )->getAll ();
		$this->wallBlocksTypes = $wallBlocks ["blocks"];
		
		$floorBlocks = $this->getSetup ()->getArenaBuildingBlocks ( CTFSetup::CTF_ARENA_FLOOR )->getAll ();
		$this->floorBlocksTypes = $floorBlocks ["blocks"];
		
		$floorBlocksBlueTeam = $this->getSetup ()->getArenaBuildingBlocks ( CTFSetup::CTF_ARENA_FLOOR_BLUE_TEAM )->getAll ();
		$this->blueTeamFloorBlocks = $floorBlocksBlueTeam ["blocks"];
		
		$floorBlocksRedTeam = $this->getSetup ()->getArenaBuildingBlocks ( CTFSetup::CTF_ARENA_FLOOR_RED_TEAM )->getAll ();
		$this->redTeamFloorBlocks = $floorBlocksRedTeam ["blocks"];
	}
	
	/**
	 * render random blocks
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 */
	public function renderRandomBlocks2(Block $block, Level $level) {
		$b = array_rand ( $this->floorBlocksTypes );
		$blockType = $this->floorBlocksTypes [$b];
		$this->updateBlock2 ( $block, $level, $blockType );
	}
	public function renderRandomBlocks3(Block $block, Level $level) {
		$b = array_rand ( $this->wallBlocksTypes );
		$blockType = $this->wallBlocksTypes [$b];
		$this->updateBlock2 ( $block, $level, $blockType );
	}
	/**
	 * Render Blue Team Randomn Blocks
	 *
	 * @param Block $block        	
	 * @param Level $level        	
	 */
	public function renderBlueTeamRandomBlocks(Block $block, Level $level) {
		$b = array_rand ( $this->blueTeamFloorBlocks );
		$blockType = $this->blueTeamFloorBlocks [$b];
		// randomly place a mine
		if ($blockType == 21) {
			$this->updateBlock2 ( $block, $level, $blockType );
		} else {
			$this->updateBlock2 ( $block, $level, $blockType );
		}
	}
	/**
	 * Render Red Team Blocks
	 *
	 * @param Block $block        	
	 * @param Level $level        	
	 */
	public function renderRedTeamRandomBlocks(Block $block, Level $level) {
		$b = array_rand ( $this->redTeamFloorBlocks );
		$blockType = $this->redTeamFloorBlocks [$b];
		$this->updateBlock2 ( $block, $level, $blockType );
	}
	
	/**
	 * Building CTF Arena
	 *
	 * @param Level $level        	
	 * @param unknown $floorwidth        	
	 * @param unknown $floorheight        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 */
	public function plotArenaMap(Level $level, $floorwidth, $floorheight, $dataX, $dataY, $dataZ, $wallType) {
		$x = $dataX;
		$y = $dataY;
		$z = $dataZ;
		
		// create 5 blocks
		$this->buildBlueTeamArena ( $level, $floorwidth, $floorheight, $dataX, $dataY, $dataZ, $wallType );
		// build light bands
		$this->buildTeamArenaWall ( $level, $floorwidth, $floorheight - 1, $dataX, $dataY + 2, $dataZ + 1, 101 );
		$this->buildTeamArenaWall ( $level, $floorwidth, $floorheight - 4, $dataX, $dataY + 2, $dataZ + 1, 89 );
		$dataX = $dataX + $floorwidth;
		$this->buildRedTeamArena ( $level, $floorwidth, $floorheight, $dataX, $dataY, $dataZ, $wallType );
		// build light bands
		$this->buildTeamArenaWall ( $level, $floorwidth, $floorheight - 1, $dataX, $dataY + 2, $dataZ + 1, 101 );
		$this->buildTeamArenaWall ( $level, $floorwidth, $floorheight - 3, $dataX, $dataY + 2, $dataZ + 1, 89 );
		// erase the wall
		$this->removeArenaWall ( $level, $floorwidth - 1, $floorheight - 1, $dataX - 1, $dataY + 1, $dataZ, 1 );
		$this->markTeamBorder ( $level, $floorwidth, $dataX, $dataY, $dataZ );
		$this->closeTeamGates ( $level, $floorwidth, $dataX, $dataY, $dataZ );
		$this->addDefenceGates ( $level, $floorwidth, $dataX, $dataY, $dataZ );
		// blue team station
		$this->buildTeamArenaBase ( $level, 1, 1, $x + 1, $y + 3, ($z + 2), 21 );
		$this->buildTeamArenaBase ( $level, 3, 1, $x + 1, $y + 2, ($z + 2), 21 );
		$this->buildTeamArenaBase ( $level, 4, 1, $x + 1, $y + 1, ($z + 2), 21 );
		$this->buildTeamArenaBase ( $level, 6, 1, $x + 1, $y, ($z + 2), 21 );
		
		// blue team flag
		$this->addBlueTeamFlag ( $level, 171, 11 );
		// red team station
		$x = $dataX + $floorwidth - 6;
		$y = $dataY;
		$z = $dataZ;
		
		$this->buildTeamArenaBase ( $level, 6, 1, $x + 1, $y, ($z + 1), 74 );
		$this->buildTeamArenaBase ( $level, 4, 1, $x + 2, $y + 1, ($z + 1), 74 );
		$this->buildTeamArenaBase ( $level, 3, 1, $x + 3, $y + 2, ($z + 1), 74 );
		$this->buildTeamArenaBase ( $level, 1, 1, $x + 4, $y + 3, ($z + 1), 74 );
		
		// add red team flag
		$this->addRedTeamFlag ( $level, 171, 14 );
		$this->addGameButtons ( $level );
	}
	
	/**
	 * Build Border
	 *
	 * @param Level $level        	
	 * @param unknown $floorwidth        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 */
	public function markTeamBorder(Level $level, $floorwidth, $dataX, $dataY, $dataZ) {
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 26 - $rz;
			$rb = $level->getBlock ( new Vector3 ( $dataX, $dataY, $cz ) );
			$this->resetBlock ( $rb, $level, 8 );
		}
		$dataX = $dataX - 1;
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 26 - $rz;
			$rb = $level->getBlock ( new Vector3 ( $dataX, $dataY, $cz ) );
			$this->resetBlock ( $rb, $level, 8 );
		}
	}
	
	/**
	 * Build Closed Fence | Gate
	 *
	 * @param Level $level        	
	 * @param unknown $floorwidth        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 */
	public function closeTeamGates(Level $level, $floorwidth, $dataX, $dataY, $dataZ) {
		$gateBlock = $this->getSetup ()->getBlockId ( CTFSetup::CTF_BLOCK_ID_BORDER_FENCE );
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 24 - $rz;
			$rb = $level->getBlock ( new Vector3 ( $dataX, $dataY + 1, $cz ) );
			$this->resetBlock ( $rb, $level, $gateBlock );
		}
		$dataX = $dataX - 1;
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 24 - $rz;
			$rb = $level->getBlock ( new Vector3 ( $dataX, $dataY + 1, $cz ) );
			$this->resetBlock ( $rb, $level, $gateBlock );
		}
	}
	
	/**
	 * Add Team Defence Wall
	 *
	 * @param Level $level        	
	 * @param unknown $floorwidth        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 */
	public function addDefenceGates(Level $level, $floorwidth, $dataX, $dataY, $dataZ) {
		$gateBlock = $this->getSetup ()->getBlockId ( CTFSetup::CTF_BLOCK_ID_DEFENCE_WALL_BLUE_TEAM );
		$blueX = $dataX - 12;
		// blue team
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 24 - $rz;
			if ($rz % 2 == 0) {
				$rb = $level->getBlock ( new Vector3 ( $blueX, $dataY + 2, $cz ) );
			} else {
				$rb = $level->getBlock ( new Vector3 ( $blueX, $dataY + 1, $cz ) );
			}
			$this->resetBlock ( $rb, $level, $gateBlock );
		}
		
		$gateBlock2 = $this->getSetup ()->getBlockId ( CTFSetup::CTF_BLOCK_ID_DEFENCE_WALL_RED_TEAM );
		$redX = $dataX + 12;
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 24 - $rz;
			if ($rz % 2 == 0) {
				$rb = $level->getBlock ( new Vector3 ( $redX, $dataY + 1, $cz ) );
			} else {
				$rb = $level->getBlock ( new Vector3 ( $redX, $dataY + 2, $cz ) );
			}
			$this->resetBlock ( $rb, $level, $gateBlock2 );
		}
	}
	
	/**
	 * Open Fence
	 *
	 * @param Level $level        	
	 * @param unknown $floorwidth        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 */
	public function openTeamGates(Level $level, $floorwidth, $dataX, $dataY, $dataZ) {
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 24 - $rz;
			$rb = $level->getBlock ( new Vector3 ( $dataX, $dataY + 1, $cz ) );
			$this->resetBlock ( $rb, $level, 0 );
		}
		$dataX = $dataX - 1;
		for($rz = 0; $rz < $floorwidth; $rz ++) {
			$cz = $dataZ + 24 - $rz;
			$rb = $level->getBlock ( new Vector3 ( $dataX, $dataY + 1, $cz ) );
			$this->resetBlock ( $rb, $level, 0 );
		}
	}
	
	/**
	 * Add Game Buttons
	 *
	 * @param Level $level        	
	 */
	public function addGameButtons(Level $level) {
		$startButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_START_GAME );
		$sx = $startButtonPos->x;
		$sy = $startButtonPos->y;
		$sz = $startButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 133, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 89, 0 );
		
		$stopButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_STOP_GAME );
		$sx = $stopButtonPos->x;
		$sy = $stopButtonPos->y;
		$sz = $stopButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 22, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 89, 0 );
		
		$leaveButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_LEAVE_GAME );
		$sx = $leaveButtonPos->x;
		$sy = $leaveButtonPos->y;
		$sz = $leaveButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 41, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 89, 0 );
	}
	public function addRoundPlayButtons(Level $level) {
		$startButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_START_GAME );
		$sx = $startButtonPos->x;
		$sy = $startButtonPos->y;
		$sz = $startButtonPos->z;
		
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 0, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 0, 0 );
		
		$stopButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_STOP_GAME );
		$sx = $stopButtonPos->x;
		$sy = $stopButtonPos->y;
		$sz = $stopButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		// obesyain = 49
		$this->resetBlock ( $rb, $level, 22, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 51, 0 );
		
		$leaveButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_LEAVE_GAME );
		$sx = $leaveButtonPos->x;
		$sy = $leaveButtonPos->y;
		$sz = $leaveButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 41, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 51, 0 );
	}
	
	/**
	 * Add Fires
	 *
	 * @param Level $level        	
	 */
	public function addFireButtons(Level $level) {
		$startButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_START_GAME );
		$sx = $startButtonPos->x;
		$sy = $startButtonPos->y;
		$sz = $startButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 133, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 51, 0 );
		
		$stopButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_STOP_GAME );
		$sx = $stopButtonPos->x;
		$sy = $stopButtonPos->y;
		$sz = $stopButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		// obesyain = 49
		$this->resetBlock ( $rb, $level, 22, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 51, 0 );
		
		$leaveButtonPos = $this->getSetup ()->getButtonPos ( CTFSetup::CLICK_BUTTON_LEAVE_GAME );
		$sx = $leaveButtonPos->x;
		$sy = $leaveButtonPos->y;
		$sz = $leaveButtonPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 41, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 51, 0 );
	}
	
	/**
	 * Add Blue Flag
	 *
	 * @param Level $level        	
	 * @param unknown $blockType        	
	 * @param unknown $meta        	
	 */
	public function addBlueTeamFlag(Level $level, $blockType, $meta) {
		$blueTeamFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_BLUE_TEAM );
		$sx = $blueTeamFlagPos->x;
		$sy = $blueTeamFlagPos->y;
		$sz = $blueTeamFlagPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, $blockType, $meta );
		// add holder for emermy flag
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy - 1, $sz + 1 ) );
		$this->resetBlock ( $rb, $level, 7, 0 );//player can not break
		// clear existing one
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz + 1 ) );
		$this->resetBlock ( $rb, $level, 0, 0 );
	}
	
	/**
	 * Add Red Team Flag
	 *
	 * @param Level $level        	
	 * @param unknown $blockType        	
	 * @param unknown $meta        	
	 */
	public function addRedTeamFlag(Level $level, $blockType, $meta) {
		$redTeamFlagPos = $this->getSetup ()->getFlagPos ( CTFSetup::CTF_FLAG_RED_TEAM );
		$sx = $redTeamFlagPos->x;
		$sy = $redTeamFlagPos->y;
		$sz = $redTeamFlagPos->z;
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, $blockType, $meta );
		
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 1, $sz ) );
		$this->resetBlock ( $rb, $level, 0, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 2, $sz ) );
		$this->resetBlock ( $rb, $level, 0, 0 );
		$rb = $level->getBlock ( new Vector3 ( $sx, $sy + 3, $sz ) );
		$this->resetBlock ( $rb, $level, 0, 0 );
		
		// add holder for emermy flag
		$rb = $level->getBlock ( new Vector3 ( $sx - 1, $sy - 1, $sz ) );
		$this->resetBlock ( $rb, $level, 7, 0 );//player can not break
		// clear exisitng flag
		$rb = $level->getBlock ( new Vector3 ( $sx - 1, $sy, $sz ) );
		$this->resetBlock ( $rb, $level, 0, 0 );
	}
	
	/**
	 * Remove Wall
	 *
	 * @param Level $level        	
	 * @param unknown $width        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function removeArenaWall(Level $level, $width, $height, $dataX, $dataY, $dataZ, $wallType) {
		$status = false;
		try {
			$x = $dataX;
			for($rx = 0; $rx < $width; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $width; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->resetBlock ( $rb, $level, 0 );
						if ($z == $dataZ) {
							$this->renderRandomBlocks3 ( $rb, $level );
						}
						if ($ry == 0) {
							$rb = $level->getBlock ( new Vector3 ( $x, $y - 1, $z ) );
							$this->renderRedTeamRandomBlocks ( $rb, $level );
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * Build Team Wall
	 *
	 * @param Level $level        	
	 * @param unknown $width        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildTeamArenaWall(Level $level, $width, $height, $dataX, $dataY, $dataZ, $wallType) {
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			for($rx = 0; $rx < $width; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $width; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->resetBlock ( $rb, $level, 0 );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($width - 1) || $rz == ($width - 1) || $rx == 0 || $rz == 0 || $ry == ($width - 1) || $ry == 0) {
							if ($rx == 2 && $ry > 0 && $ry < ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($ry == 0) {
								$this->resetBlock ( $rb, $level, 0 );
							} else if ($ry == ($width - 1)) {
								$this->resetBlock ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($rx == ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else {
								$this->resetBlock ( $rb, $level, $wallType );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * Build Team Base
	 *
	 * @param Level $level        	
	 * @param unknown $width        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildTeamArenaBase(Level $level, $width, $height, $dataX, $dataY, $dataZ, $wallType) {
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			for($rx = 0; $rx < $width; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $width; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->resetBlock ( $rb, $level, 0 );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($width - 1) || $rz == ($width - 1) || $rx == 0 || $rz == 0 || $ry == ($width - 1) || $ry == 0) {
							if ($rx == 2 && $ry > 0 && $ry < ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($ry == 0) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($ry == ($width - 1)) {
								$this->resetBlock ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($rx == ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else {
								$this->resetBlock ( $rb, $level, $wallType );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * Build Team Arena
	 *
	 * @param Level $level        	
	 * @param unknown $width        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildTeamArena(Level $level, $width, $height, $dataX, $dataY, $dataZ, $wallType) {
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			for($rx = 0; $rx < $width; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $width; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->resetBlock ( $rb, $level, 0 );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($width - 1) || $rz == ($width - 1) || $rx == 0 || $rz == 0 || $ry == ($width - 1) || $ry == 0) {
							if ($rx == 2 && $ry > 0 && $ry < ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($ry == 0) {
								$this->renderRandomBlocks2 ( $rb, $level );
							} else if ($ry == ($width - 1)) {
								$this->resetBlock ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($rx == ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else {
								$this->resetBlock ( $rb, $level, $wallType );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * Build Blue Team Arena
	 *
	 * @param Level $level        	
	 * @param unknown $width        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildBlueTeamArena(Level $level, $width, $height, $dataX, $dataY, $dataZ, $wallType) {
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			for($rx = 0; $rx < $width; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $width; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->resetBlock ( $rb, $level, 0 );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($width - 1) || $rz == ($width - 1) || $rx == 0 || $rz == 0 || $ry == ($width - 1) || $ry == 0) {
							if ($rx == 2 && $ry > 0 && $ry < ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($ry == 0) {
								$this->renderBlueTeamRandomBlocks ( $rb, $level );
							} else if ($ry == ($width - 1)) {
								$this->resetBlock ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($rx == ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else {
								$this->resetBlock ( $rb, $level, $wallType );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * Build Red Team Arena
	 *
	 * @param Level $level        	
	 * @param unknown $width        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildRedTeamArena(Level $level, $width, $height, $dataX, $dataY, $dataZ, $wallType) {
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			for($rx = 0; $rx < $width; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $width; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->resetBlock ( $rb, $level, 0 );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($width - 1) || $rz == ($width - 1) || $rx == 0 || $rz == 0 || $ry == ($width - 1) || $ry == 0) {
							if ($rx == 2 && $ry > 0 && $ry < ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($ry == 0) {
								$this->renderRedTeamRandomBlocks ( $rb, $level );
							} else if ($ry == ($width - 1)) {
								// $this->log ( TextFormat::BLUE . "roof blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								$this->resetBlock ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else if ($rx == ($width - 1)) {
								$this->resetBlock ( $rb, $level, $wallType );
							} else {
								$this->resetBlock ( $rb, $level, $wallType );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	

	
	/**
	 * remove blocks
	 *
	 * @param array $blocks        	
	 * @param Player $p        	
	 */
	public function removeBlocks(Block $block, Player $xp) {
		$this->updateBlock ( $block, $xp, 0 );
	}
	public function removeUpdateBlock($topblock, $tntblock) {
		foreach ( $this->getPlugin ()->livePlayers as $livep ) {
			if ($livep instanceof MGArenaPlayer) {
				$this->removeBlocks ( $topblock, $livep->player );
				$this->removeBlocks ( $tntblock, $livep->player );
			} else {
				$this->removeBlocks ( $topblock, $livep );
				$this->removeBlocks ( $tntblock, $livep );
			}
		}
	}
	
	/**
	 * Render and Update Block
	 *
	 * @param Block $block
	 * @param Level $level
	 * @param unknown $blockType
	 * @param number $meta
	 */
	public function resetBlock(Block $block, Level $level, $blockType, $meta = 0) {
		$this->updateBlock2 ( $block, $level, $blockType );
	}
	/**
	 *
	 * @param Block $block
	 * @param Player $p
	 * @param unknown $blockType
	 */
	public function replaceBlockType(Level $level, Block $block, $blockType) {
		$this->updateBlock2 ( $block, $level, $blockType );
	}
	
	/**
	 * Update block
	 *
	 * @param Block $block        	
	 * @param Player $xp        	
	 * @param unknown $blockType        	
	 */
	public function updateBlock(Block $block, Player $xp, $blockType) {
		$this->updateBlock2 ( $block, $xp->level, $blockType );
	}
	
	
	public function updateBlock2(Block $block, Level $level, $blockType) {
		$players = $level->getPlayers ();
		foreach ( $players as $p ) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = $blockType;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			$level->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $blockType );
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $level->getBlock ( $pos, true );
			$direct = true;
			$update = true;
			$level->setBlock ( $pos, $block, $direct, $update );
		}
	}
	
	/**
	 * render random blocks
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 */
	public function renderRandomBlocks(Block $block, Player $p) {
		$b = array_rand ( $this->boardBlocksTypes );
		$blockType = $this->boardBlocksTypes [$b];
		// randomly place a mine
		$this->updateBlock ( $block, $p, $blockType );
	}
	
	/**
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 * @param unknown $blockType        	
	 */
	public function renderBlockByType(Block $block, Player $p, $blockType) {
		// randomly place a mine
		$this->updateBlock ( $block, $p, $blockType );
	}
	
	/**
	 * replace random blocks
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 */
	public function replaceRandomBlocks(Level $level, Block $block) {
		$b = array_rand ( $this->boardBlocksTypes );
		$blockType = $this->boardBlocksTypes [$b];
		// randomly place a mine
		$this->replaceBlockType ( $level, $block, $blockType );
	}
	

	/**
	 * remove arena
	 *
	 * @param unknown $player        	
	 * @param unknown $xx        	
	 * @param unknown $yy        	
	 * @param unknown $zz        	
	 */
	public function removeArena($player, $xx, $yy, $zz) {
		$wallheighSize = 70;
		$bsize = $this->boardsize;
		$xmax = $this->boardsize + 3;
		$ymax = $this->boardsize;
		
		For($z = 0; $z <= $xmax; $z ++) {
			For($x = 0; $x <= $xmax; $x ++) {
				For($y = 0; $y <= $wallheighSize; $y ++) {
					$mx = $xx + $x;
					$my = $yy + $y;
					$mz = $zz + $z;
					$bk = $player->getLevel ()->getBlock ( new Vector3 ( $mx, $my, $mz ) );
					$this->removeBlocks ( $bk, $player );
				}
			}
		}
	}
// 	public function getManager() {
// 		return $this->getPlugIn ()->ctfManager;
// 	}
// 	public function getPlugin() {
// 		return $this->plugin;
// 	}
// 	public function getMsg($key) {
// 		return $this->getPlugIn ()->ctfMessages->getMessageByKey ( $key );
// 	}
// 	public function getSetup() {
// 		return $this->getPlugin ()->ctfSetup;
// 	}
// 	private function log($msg) {
// 		$this->getPlugin ()->getLogger ()->info ( $msg );
// 	}
}

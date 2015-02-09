<?php

namespace mcg76\game\ctf;

use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;

/**
 * MCG76 CTF Game Kit
 *
 * Copyright (C) 2014 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *        
 */
class CTFGameKit extends MiniGameBase {

	const DIR_KITS = "kits/";
	const KIT_BLUE_TEAM = "BlueTeam";
	const KIT_RED_TEAM = "RedTeam";	
	const KIT_UNKNOWN = "Unknown";
	
	public function __construct(CTFPlugIn $plugin) {
		parent::__construct ( $plugin );
		$this->init();
	}
	
	private function init() {
		@mkdir ( $this->plugin->getDataFolder () . self::DIR_KITS, 0777, true );
		$this->getKit(self::KIT_BLUE_TEAM);
		$this->getKit(self::KIT_RED_TEAM);		
	}

	
	public function putOnGameKit(Player $p, $kitType) {
		switch ($kitType) {
			case self::KIT_BLUE_TEAM :
				$this->loadKit(self::KIT_BLUE_TEAM, $p);
				break;
			case self::KIT_RED_TEAM :
			    $this->loadKit(self::KIT_RED_TEAM, $p);
				break;
			default :
			   // no armors kit
			   $this->loadKit(self::KIT_UNKNOWN, $p);
		}
	}
	
	public function getKit($kitName) {
		if (! (file_exists ( $this->getPlugin ()->getDataFolder () . self::DIR_KITS . strtolower ( $kitName ) . ".yml" ))) {
			
			if ($kitName == self::KIT_BLUE_TEAM) {
				
				return new Config ( $this->plugin->getDataFolder () . self::DIR_KITS . strtolower ( self::KIT_BLUE_TEAM ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_BLUE_TEAM,
						"isDefault" => false,
						"cost" => 0,
						"health" => 20,
						"armors" => array (
								"helmet" => array (
										"302",
										"0",
										"1" 
								),
								"chestplate" => array (
										"303",
										"0",
										"1" 
								),
								"leggings" => array (
										"304",
										"0",
										"1" 
								),
								"boots" => array (
										"305",
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								"272" => array (
										"272",
										"0",
										"1" 
								),
								"50" => array (
										"50",
										"0",
										"1" 
								),
								"261" => array (
										"261",
										"0",
										"1" 
								),
								"262" => array (
										"262",
										"0",
										"64" 
								) 
						),
						"foods" => array (
								"260" => array (
										"260",
										"0",
										"2" 
								),
								"366" => array (
										"366",
										"0",
										"2" 
								),
								"320" => array (
										"320",
										"0",
										"2" 
								),
								"323" => array (
										"323",
										"0",
										"2" 
								),
								"364" => array (
										"364",
										"0",
										"2" 
								) 
						) 
				) );
			} elseif ($kitName == self::KIT_RED_TEAM) {
				return new Config ( $this->plugin->getDataFolder () . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_RED_TEAM,
						"isDefault" => false,
						"cost" => 0,
						"health" => 20,
						"armors" => array (
								"helmet" => array (
										"306",
										"0",
										"1" 
								),
								"chestplate" => array (
										"307",
										"0",
										"1" 
								),
								"leggings" => array (
										"308",
										"0",
										"1" 
								),
								"boots" => array (
										"309",
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								"272" => array (
										"272",
										"0",
										"1" 
								),
								"50" => array (
										"50",
										"0",
										"1" 
								),
								"261" => array (
										"261",
										"0",
										"1" 
								),
								"262" => array (
										"262",
										"0",
										"64" 
								) 
						),
						"foods" => array (
								"260" => array (
										"260",
										"0",
										"2" 
								),
								"366" => array (
										"366",
										"0",
										"2" 
								),
								"320" => array (
										"320",
										"0",
										"2" 
								),
								"323" => array (
										"323",
										"0",
										"2" 
								),
								"364" => array (
										"364",
										"0",
										"2" 
								) 
						) 
				) );
			} else {
				return new Config ( $this->plugin->getDataFolder () . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_UNKNOWN,
						"isDefault" => false,
						"cost" => 0,
						"health" => 20,
						"armors" => array (
								"helmet" => array (
										"0",
										"0",
										"1" 
								),
								"chestplate" => array (
										"0",
										"0",
										"1" 
								),
								"leggings" => array (
										"0",
										"0",
										"1" 
								),
								"boots" => array (
										"0",
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								"272" => array (
										"272",
										"0",
										"1" 
								),
								"50" => array (
										"50",
										"0",
										"1" 
								),
								"261" => array (
										"261",
										"0",
										"1" 
								),
								"262" => array (
										"262",
										"0",
										"64" 
								) 
						),
						"foods" => array (
								"260" => array (
										"260",
										"0",
										"2" 
								),
								"366" => array (
										"366",
										"0",
										"2" 
								),
								"320" => array (
										"320",
										"0",
										"2" 
								),
								"323" => array (
										"323",
										"0",
										"2" 
								),
								"364" => array (
										"364",
										"0",
										"2" 
								) 
						) 
				) );
			}
		} else {
			return new Config ( $this->getPlugin ()->getDataFolder () . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array () );
		}
	}
	
	public function loadKit($teamkitName, Player $p) {
		//$p->sendMessage ( $this->getMsg ( "game.start-equipment" ) );
		$teamKit = $this->getKit($teamkitName)->getAll();
	
		//player must clear all equipments
		$p->getInventory()->clearAll();
		//add armors
		if ($teamKit["armors"]["helmet"][0]!=null) {
			$p->getInventory ()->setHelmet ( new Item ( $teamKit["armors"]["helmet"][0], $teamKit["armors"]["helmet"][1], $teamKit["armors"]["helmet"][2] ) );
		}
		if ($teamKit["armors"]["chestplate"][0]!=null) {
			$p->getInventory ()->setChestplate ( new Item ( $teamKit["armors"]["chestplate"][0], $teamKit["armors"]["chestplate"][1], $teamKit["armors"]["chestplate"][2] ) );
		}
		if ($teamKit["armors"]["leggings"][0]!=null) {
			$p->getInventory ()->setLeggings ( new Item ( $teamKit["armors"]["leggings"][0], $teamKit["armors"]["leggings"][1], $teamKit["armors"]["leggings"][2] ) );
		}
		if ($teamKit["armors"]["boots"][0]!=null) {
			$p->getInventory ()->setBoots ( new Item ( $teamKit["armors"]["boots"][0], $teamKit["armors"]["boots"][1], $teamKit["armors"]["boots"][2] ) );
		}
		// notify viewers
		$p->getInventory ()->sendArmorContents ( $p );
		// set health
		if ($teamKit["health"]!=null && $teamKit["health"]>1){
			$p->setHealth ($teamKit["health"]);
		} else {
			$p->setHealth (20);
		}
		// add iron sword, if not exist
		$weapons = $teamKit["weapons"];
		foreach ($weapons as $w) {
			$item = new Item($w[0],$w[1],$w[2]);
			$p->getInventory ()->addItem($item);
		}
		$foods = $teamKit["foods"];
		foreach ($foods as $w) {
			$item = new Item($w[0],$w[1],$w[2]);
			$p->getInventory ()->addItem($item);
		}	
		$p->updateMovement ();
		$p->getInventory ()->setHeldItemIndex ( 0 );
	}
	
	public function removePlayerIventory(Player $bp) {
		if ($bp!=null) {
			$bp->getInventory ()->setBoots ( new Item ( 0 ) );
			$bp->getInventory ()->setChestplate ( new Item ( 0 ) );
			$bp->getInventory ()->setHelmet ( new Item ( 0 ) );
			$bp->getInventory ()->setLeggings ( new Item ( 0 ) );		
			$bp->getInventory ()->sendArmorContents($bp->getViewers());
			$bp->getInventory ()->sendContents ( $bp );					
			$bp->getInventory ()->clearAll ();			
			$bp->updateMovement ();
		}
	}
	public function getArmor($type, $slot) {
		if ($type == "leather") {
			return new Item ( 298 + $slot, 0, 1 );
		} else if ($type == "chainmail") {
			return new Item ( 302 + $slot, 0, 1 );
		} else if ($type == "iron") {
			return new Item ( 306 + $slot, 0, 1 );
		} else if ($type == "gold") {
			return new Item ( 314 + $slot, 0, 1 );
		} else if ($type == "diamond") {
			return new Item ( 310 + $slot, 0, 1 );
		} else {
			return new Item ( 0, 0, 1 );
		}
	}
}
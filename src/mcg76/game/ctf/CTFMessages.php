<?php

namespace mcg76\game\ctf;

use pocketmine\utils\Config;

/**
 * MCG76 CTF Messages
 *
 * Copyright (C) 2014 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *      
 */
class CTFMessages extends MiniGameBase {
	private $messages;
	public function __construct(CTFPlugIn $plugin) {
		parent::__construct ( $plugin );
		$this->loadLanguageMessages ();
	}
	public function getMessageByKey($key) {
		return isset ( $this->messages [$key] ) ? $this->messages [$key] : $key;
	}
	public function getMessageWithVars($node, $vars) {
		$msg = $this->messages->getNested ( $node );
		
		if ($msg != null) {
			$number = 0;
			foreach ( $vars as $v ) {
				$msg = str_replace ( "%var$number%", $v, $msg );
				$number ++;
			}
			return $msg;
		}
		return null;
	}
	public function getVersion() {
		return $this->messages->get ( "version" );
	}
	private function parseMessages(array $messages) {
		$result = [ ];
		foreach ( $messages as $key => $value ) {
			if (is_array ( $value )) {
				foreach ( $this->parseMessages ( $value ) as $k => $v ) {
					$result [$key . "." . $k] = $v;
				}
			} else {
				$result [$key] = $value;
			}
		}
		return $result;
	}
	
	/**
	 * Load Languages
	 */
	public function loadLanguageMessages() {
		if (! file_exists ( $this->getPlugin ()->getDataFolder () )) {
			@mkdir ( $this->getDataFolder (), 0777, true );
			file_put_contents ( $this->getDataFolder () . "config.yml", $this->getResource ( "config.yml" ) );
		}
		$this->getPlugin ()->saveDefaultConfig ();
		// retrieve language setting
		$configlang = $this->getSetup ()->getMessageLanguage ();
		$messageFile = $this->getPlugin ()->getDataFolder () . "messages_" . $configlang . ".yml";
		$this->getPlugin ()->getLogger ()->info ( "CTF Message Language = " . $messageFile );
		if (! file_exists ( $messageFile )) {
			$this->getPlugin ()->getLogger ()->info ( "Game Messages Default to EN" );
			file_put_contents ( $this->getPlugin ()->getDataFolder () . "messages_EN.yml", $this->getPlugin ()->getResource ( "messages_EN.yml" ) );
			$msgConfig = new Config ( $this->getPlugin ()->getDataFolder () . "messages_EN.yml" );
			$messages = $msgConfig->getAll ();
			$this->messages = $this->parseMessages ( $messages );
			// $this->getPlugin ()->getLogger ()->info ( "Warning!, specify configuration language not found!, fall back to use English" );
		} else {
			$this->getPlugin ()->getLogger ()->info ( "use existing" );
			$messages = (new Config ( $messageFile ))->getAll ();
			$this->messages = $this->parseMessages ( $messages );
		}
	}
	public function reloadMessages() {
		$this->messages->reload ();
	}
	public static function prefixMsg(&$msg) {
		return "[CTF]" . $msg;
	}
}
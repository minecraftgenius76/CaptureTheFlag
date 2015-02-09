<?php

namespace mcg76\game\ctf;

/**
 * MCG76 Mini-Game Base Class
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *
 */

abstract class MiniGameBase {		
	protected $plugin;
	public function __construct(CTFPlugIn $plugin) {
		if($plugin === null){
			throw new \InvalidStateException("plugin may not be null");
		}
		$this->plugin = $plugin;
	}
	
	protected function getManager() {
		return $this->plugin->ctfManager;
	}
	protected function getPlugin() {
		return $this->plugin;
	}
	protected function getMsg($key) {
		return $this->plugin->ctfMessages->getMessageByKey ( $key );
	}
	protected function getSetup() {
		return $this->plugin->ctfSetup;
	}
	protected function getBuilder() {
		return $this->plugin->ctfBuilder;
	}
	
	protected function getGameKit() {
		return $this->plugin->ctfGameKit;
	}
	
	protected function getLog() {
		return $this->plugin->getLogger();
	}
	
	protected function log($msg) {
		return $this->plugin->getLogger()->info($msg);
	}
	
	protected function getConfig($key, $defaultValue=null) {
		return $this->plugin->getConfig ()->get ( $key, $defaultValue);
	}
	
	protected function setConfig($key, $value) {
		return $this->plugin->getConfig ()->set ( $key, $value );
	}
}
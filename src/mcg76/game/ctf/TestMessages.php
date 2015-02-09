<?php

namespace mcg76\game\ctf;

use pocketmine\utils\TextFormat;

/**
 * MCG76 CTF Setup
 *
 * Copyright (C) 2014 minecraftgenius76
 *
 * @author MCG76
 * @link http://www.youtube.com/user/minecraftgenius76
 *
 */
class TestMessages extends MiniGameBase {

	public function __construct(CTFPlugIn $plugin) {
		parent::__construct ( $plugin );
		$this->ctfmsg = new CTFMessages($plugin);
	}
	
	public function runTests() {
		$this->testMessage("ctf.name");
		$this->testMessage("ctf.status");		
		$this->testMessage("team.scores.score");		
		$this->testMessage("ctf.error.no-permission");
		$this->testMessage("ctf.error.not-game-stop");
		$this->testMessage("ctf.setup.success");
		$this->testMessage("ctf.setup.failed");
		$this->testMessage("ctf.setup.select");
		$this->testMessage("ctf.setup.action");				
		$this->testMessage("arena.created");
		$this->testMessage("block.display-on");
		$this->testMessage("block.display-off");
		$this->testMessage("team.join-blue" );
		$this->testMessage("team.join-red");
		$this->testMessage("game.player-left" );
		$this->testMessage("game.player-stop" );		
		$this->testMessage("game.player-start");
		$this->testMessage("game.stats");
		$this->testMessage("team.scores.score");
		$this->testMessage("team.scores.red-players");		
		$this->testMessage("team.scores.players");
		$this->testMessage("team.scores.round");
		$this->testMessage("team.scores.blue-players");		
		$this->testMessage("team.scores.redteam-wins");
		$this->testMessage("team.scores.blueteam-wins");
		$this->testMessage("game.in-progress");
		$this->testMessage("game.new-game");		
		$this->testMessage("ctf.error.blueteam-flag-exist" );
		$this->testMessage("ctf.conglatulations");
		$this->testMessage("ctf.red-team.capturedflag");
		$this->testMessage("ctf.blue-team.score");
		$this->testMessage("ctf.red-team.score");
		$this->testMessage("ctf.error.redteam-flag-exist" );		
		$this->testMessage("ctf.conglatulations");
		$this->testMessage("ctf.blue-team.capturedflag");
		$this->testMessage("ctf.blue-team.score" );
		$this->testMessage("ctf.red-team.score" );		
		$this->testMessage("game.getready");		
		$this->testMessage("game.nextround");	
		$this->testMessage("game.roundstart");
		$this->testMessage("ctf.finished");
		$this->testMessage("game.ticks");		
		$this->testMessage("ctf.finished");
		$this->testMessage("team.welcome-blue");		
		$this->testMessage("team.tap-start");
		$this->testMessage("team.blue");
		$this->testMessage("team.joined-blue");
		$this->testMessage("team.members");	
		$this->testMessage("team.welcome-red");		
		$this->testMessage("team.tap-start");		
		$this->testMessage("team.red" );
		$this->testMessage("team.joined-red");
		$this->testMessage("team.members");		
		$this->testMessage("game.remove-equipment");
		$this->testMessage("ctf.left-game");		
		$this->testMessage( "game.stop");
		$this->testMessage("ctf.return-waiting-area");
		$this->testMessage("team.scores.red-players" );
		$this->testMessage("team.scores.players");		
		$this->testMessage("game.full" );
		$this->testMessage("team.scores.blue-players" );
		$this->testMessage("team.scores.players" );		
		$this->testMessage("game.resetting");
		$this->testMessage("ctf.spawn_player");		
		$this->testMessage("sign.world-not-found");
		$this->testMessage("sign.teleport.spawn");
		$this->testMessage("sign.teleport.ctf");
		$this->testMessage("ctf.error.wrong-sender");		
		$this->testMessage("game.not-enought-players");
		$this->testMessage("game.in-progress");		
		$this->testMessage("game.hit-stop" );
		$this->testMessage("game.round");
		$this->testMessage("game.go");
		$this->testMessage("ctf.return-flag");
		$this->testMessage("team.left-blue");
		$this->testMessage("ctf.return-flag");
		$this->testMessage("game.final.draw");
		$this->testMessage("game.final.red-win");		
		$this->testMessage("game.final.blue-win");				
		$this->testMessage("sign.world-not-found");
		$this->testMessage("sign.teleport.world");		
		$this->testMessage("sign.teleport.game");		
		$this->testMessage("sign.done" );
		$this->testMessage("game.start-equipment");
	}
	
	public function testMessage($key) {
		$value = $this->getMsg($key);
		if ($value==null) {
			$value = TextFormat::RED ."* KEY NOT FOUND !!!";
		}
		if ($key==$value) {
			$value = TextFormat::RED ."* KEY NOT FOUND !!!";
		}
		$this->getPlugIn()->getLogger()->info($key." = ".$value);
	}
}
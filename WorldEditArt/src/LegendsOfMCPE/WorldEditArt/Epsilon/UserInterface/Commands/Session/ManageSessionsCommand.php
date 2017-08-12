<?php

/*
 *
 * WorldEditArt-Epsilon
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session;

use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\WorldEditArtCommand;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ManageSessionsCommand extends WorldEditArtCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/session", "Manage your sessions", "//session start|close <passphrase>", ["/ses"], Consts::PERM_SESSION_START, [
			"default" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["start", "close"],
					"optional" => true,
				],
				[
					"name" => "passphrase",
					"type" => "string",
					"optional" => true,
				],
			],
		]);
	}

	public function testPermissionSilent(CommandSender $target) : bool{
		return parent::testPermissionSilent($target) && $target instanceof Player;
	}

	public function testPermission(CommandSender $target) : bool{
		if(!($target instanceof Player)){
			$target->sendMessage("Please run this command in-game.");
			return false;
		}
		return parent::testPermission($target);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return;
		}
		$sessions = $this->getPlugin()->getSessionsOf($sender);
		if(!isset($args[0])){
			$sender->sendMessage(sprintf("You %s started a player session.", isset($sessions[PlayerBuilderSession::SESSION_KEY]) ? "have" : "have not"));
			return;
		}
		/** @var Player $sender */
		switch(mb_strtolower($args[0])){
			case "start":
				if(isset($sessions[PlayerBuilderSession::SESSION_KEY])){
					$sender->sendMessage(TextFormat::RED . "You have already started a player session!");
					return;
				}
				if($pass = $this->getPlugin()->getConfig()->get(Consts::CONFIG_SESSION_GLOBAL_PASSPHRASE)){
					if(!isset($args[1])){
						$sender->sendMessage(TextFormat::RED . "Usage: //session start <passphrase>");
						return;
					}
					if($args[1] !== $pass){
						$sender->sendMessage(TextFormat::RED . "Wrong passphrase");
						return;
					}
				}
				$this->getPlugin()->startPlayerSession($sender);
				$sender->sendMessage(TextFormat::GREEN . "Started builder session");
				return;
			case "close":
				if(!isset($sessions[PlayerBuilderSession::SESSION_KEY])){
					$sender->sendMessage(TextFormat::RED . "You haven't started a session to close");
					return;
				}
				$this->getPlugin()->closePlayerSession($sender);
				$sender->sendMessage(TextFormat::GREEN . "Your session has been closed");
		}
	}
}

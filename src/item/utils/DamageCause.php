<?php
declare(strict_types=1);

namespace customiesdevs\customies\item\utils;

/**
 * Represents all possible causes of damage in the game.
 */
enum DamageCause: string {
	const NONE = "none";
	const ALL = "all";
	const ANVIL = "anvil";
	const BLOCK_EXPLOSION = "block_explosion";
	const CAMPFIRE = "campfire";
	const CHARGING = "charging";
	const CONTACT = "contact";
	const DROWNING = "drowning";
	const ENTITY_ATTACK = "entity_attack";
	const ENTITY_EXPLOSION = "entity_explosion";
	const FALL = "fall";
	const FALLING_BLOCK = "falling_block";
	const FIRE = "fire";
	const FIRE_TICK = "fire_tick";
	const FIREWORKS = "fireworks";
	const FLY_INTO_WALL = "fly_into_wall";
	const FREEZING = "freezing";
	const LAVA = "lava";
	const LIGHTNING = "lightning";
	const MAGIC = "magic";
	const MAGMA = "magma";
	const OVERRIDE = "override";
	const PISTON = "piston";
	const PROJECTILE = "projectile";
	const RAM_ATTACK = "ram_attack";
	const SELF_DESTRUCT = "self_destruct";
	const SONIC_BOOM = "sonic_boom";
	const SOUL_CAMPFIRE = "soul_campfire";
	const STALACTITE = "stalactite";
	const STALAGMITE = "stalagmite";
	const STARVE = "starve";
	const SUFFOCATION = "suffocation";
	const TEMPERATURE = "temperature";
	const THORNS = "thorns";
	const VOID = "void";
	const WITHER = "wither";
}
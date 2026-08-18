<?php
declare(strict_types=1);

namespace customiesdevs\customies;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\protocol\types\Experiments;
use function count;

final class CustomiesListener implements Listener {

	/** @var BlockPaletteEntry[] */
	private array $cachedBlockPalette = [];
	private Experiments $experiments;

	public function __construct() {
		$this->experiments = new Experiments([
			// "data_driven_items" is required for custom blocks to render in-game.
			// With this disabled, custom blocks will appear as the UPDATE texture block.
			"data_driven_items" => true,
			"upcoming_creator_features" => true,
			"experimental_graphics" => true //This is vibrant visuals
		], true);
	}

	public function onDataPacketSend(DataPacketSendEvent $event): void {
		$packets = $event->getPackets();
		foreach($packets as $i => $packet){
			if($packet instanceof StartGamePacket){
				if(count($this->cachedBlockPalette) === 0){
					// Wait for the data to be needed before it is actually cached. Allows for all blocks and items to be
					// registered before they are cached for the rest of the runtime.
					$this->cachedBlockPalette = CustomiesBlockFactory::getInstance()->getBlockPaletteEntries();
				}
				$packet->levelSettings->experiments = $this->experiments;
				$packet->blockPalette = $this->cachedBlockPalette;
			}elseif($packet instanceof ResourcePackStackPacket) {
				$packet->experiments = $this->experiments;
			} elseif($packet instanceof ResourcePacksInfoPacket && $packet->isForceDisableVibrantVisuals()) {
				unset($packets[$i]);
				foreach ($event->getTargets() as $target) {
					$target->sendDataPacket(ResourcePacksInfoPacket::create(
						resourcePackEntries: $packet->resourcePackEntries,
						mustAccept: $packet->mustAccept,
						hasAddons: $packet->hasAddons,
						hasScripts: $packet->hasScripts,
						worldTemplateId: $packet->getWorldTemplateId(),
						worldTemplateVersion: $packet->getWorldTemplateVersion(),
						forceDisableVibrantVisuals: false //we want vibrant visuals to be enabled
					));
				}
			}
		}
		if (empty($packets)) $event->cancel();
		else $event->setPackets($packets);
	}
}

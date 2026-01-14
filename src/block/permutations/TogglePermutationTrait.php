<?php

namespace customiesdevs\customies\block\permutations;

use customiesdevs\customies\block\component\LightEmissionComponent;
use customiesdevs\customies\block\component\MaterialInstancesComponent;
use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\RenderMethod;
use customiesdevs\customies\block\properties\TintMethod;
use customiesdevs\customies\block\states\BlockState;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;

trait TogglePermutationTrait
{
	use BlockPermutationsTrait;

    private bool $isToggled = false;

    public function initStates(): void
	{
        $this->addState(new BlockState("histeria:toggled", [false, true]));
    }

    public function initPermutations(): void
    {
        foreach ([false, true] as $enabled) {
            $texture = $this->getBaseTexture() . ($enabled ? "_on" : "_off");
            $materials = $this->getTargetMaterials($texture);

			$this->addPermutation(new BlockPermutation("q.block_state('histeria:toggled') == $enabled", new MaterialInstancesComponent($materials)));
			$this->addPermutation(new BlockPermutation("q.block_state('histeria:toggled') == $enabled", new LightEmissionComponent($enabled ? 15 : 0)));
        }
    }

    public function getCurrentStates(): array
    {
        return [$this->isToggled];
    }

    public function serializeState(BlockStateWriter $blockStateOut): void
    {
        $blockStateOut->writeBool("histeria:toggled", $this->isToggled);
    }

    public function deserializeState(BlockStateReader $blockStateIn): void
    {
        $this->isToggled = $blockStateIn->readBool("histeria:toggled");
    }

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
    {
        $w->bool($this->isToggled);
    }

    public function setToggled(bool $toggled): void
    {
        $this->isToggled = $toggled;
        $this->position->getWorld()->setBlock($this->getPosition(), $this);
    }

    public function isToggled(): bool
    {
        return $this->isToggled;
    }

    public function getTargetMaterials($texture): array
    {
        return [new Material(Material::TARGET_ALL, $texture, RenderMethod::ALPHA_TEST, TintMethod::NONE, false, false)];
    }

    abstract public function getBaseTexture(): string;
}

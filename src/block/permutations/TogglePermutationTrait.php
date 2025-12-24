<?php

namespace customiesdevs\customies\block\permutations;

use customiesdevs\customies\block\component\MaterialInstancesComponent;
use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\RenderMethod;
use customiesdevs\customies\block\properties\TintMethod;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\nbt\tag\CompoundTag;

trait TogglePermutationTrait
{
    private bool $isToggled = false;

    public function getBlockProperties(): array {
        return [
            new BlockProperty("histeria:toggled", [false, true]),
        ];
    }

    public function getPermutations(): array
    {
        $permutations = [];
        foreach ([false, true] as $enabled) {
            $texture = $this->getBaseTexture() . ($enabled ? "_on" : "_off");
            $materials = $this->getTargetMaterials($texture);

            $permutation = (new Permutation("q.block_state('histeria:toggled') == $enabled"))
                ->withComponent(new MaterialInstancesComponent($materials));
            $this->getAdditionalComponents($permutation, $enabled);
            $permutations[] = $permutation;
        }

        return $permutations;
    }

    public function getCurrentBlockProperties(): array
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

    /**
     * This is made to be override to add permutations
     * @return void
     */
    public function getAdditionalComponents(Permutation $permutation, bool $toggled): void
    {

    }

    abstract public function getBaseTexture(): string;
}
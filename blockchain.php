<?php

class Block
{
    private int $id;
    private string $dttm;
    private string $hash;
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDttm(): string
    {
        return $this->dttm;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function calculateHash(string $previousHash): string
    {
        return hash('sha256', $this->content . $previousHash);
    }

    public function seal(string $previousHash): void
    {
        $this->dttm = date('Y-m-d H:i:s');
        $this->hash = $this->calculateHash($previousHash);
    }
}

class Chain implements IChain
{
    private array $blocks = [];

    public function addBlock(Block $block): static
    {
        $previousBlock = $this->getLastBlock();
        $block->seal($previousBlock !== null ? $previousBlock->getHash() : '');
        $block->id = count($this->blocks) + 1;
        $this->blocks[] = $block;
        return $this;
    }

    public function getBlock(int $id): ?Block
    {
        return $this->blocks[$id - 1] ?? null;
    }

    public function getLastBlock(): ?Block
    {
        return end($this->blocks) ?: null;
    }

    public function isValid(): bool
    {
        foreach ($this->blocks as $i => $block) {
            if ($i > 0) {
                $previousBlock = $this->blocks[$i - 1];
                if ($block->getHash() !== $block->calculateHash($previousBlock->getHash())) {
                    return false;
                }
            }
        }
        return true;
    }
}

interface IChain
{
    public function addBlock(Block $block): static;
    public function getBlock(int $id): ?Block;
    public function getLastBlock(): ?Block;
    public function isValid(): bool;
}

// Příklad použití
$chain = (new Chain)
    ->addBlock(new Block("Varnsdorf"))
    ->addBlock(new Block("Rumburk"));

var_dump($chain);
echo "Chain " . ($chain->isValid() ? "is" : "is not") . " valid.\n";
echo "Poslední město: " . $chain->getLastBlock()->getContent() . "\n";

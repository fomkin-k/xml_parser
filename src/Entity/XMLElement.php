<?php

namespace App\Entity;

use App\Repository\XMLElementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: XMLElementRepository::class)]
class XMLElement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $tag = null;

    #[ORM\Column(type: Types::ARRAY, nullable:true)]
    private array $attributes = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parent_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $element_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getParentId(): ?string
    {
        return $this->parent_id;
    }

    public function setParentId(?string $parent_id): static
    {
        $this->parent_id = $parent_id;

        return $this;
    }

    public function getElementId(): ?string
    {
        return $this->element_id;
    }

    public function setElementId(?string $element_id): static
    {
        $this->element_id = $element_id;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\AiModelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AiModelRepository::class)]
class AiModel implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $systemPrompt = null;

    #[ORM\Column]
    private ?bool $useEpubContext = null;

    #[ORM\Column]
    private ?bool $useWikipediaContext = null;

    #[ORM\Column]
    private ?bool $useAmazonContext = null;

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function label(): string
    {
        return $this->id.' '.$this->type.' '.$this->url.' '.$this->model;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    public function setSystemPrompt(?string $systemPrompt): static
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }

    public function isUseEpubContext(): ?bool
    {
        return $this->useEpubContext;
    }

    public function setUseEpubContext(bool $useEpubContext): static
    {
        $this->useEpubContext = $useEpubContext;

        return $this;
    }

    public function isUseWikipediaContext(): ?bool
    {
        return $this->useWikipediaContext;
    }

    public function setUseWikipediaContext(bool $useWikipediaContext): static
    {
        $this->useWikipediaContext = $useWikipediaContext;

        return $this;
    }

    public function isUseAmazonContext(): ?bool
    {
        return $this->useAmazonContext;
    }

    public function setUseAmazonContext(bool $useAmazonContext): static
    {
        $this->useAmazonContext = $useAmazonContext;

        return $this;
    }
}
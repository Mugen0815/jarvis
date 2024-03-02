<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?thread $thread = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?bool $is_function_call = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $full_respoonse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThread(): ?thread
    {
        return $this->thread;
    }

    public function setThread(?thread $thread): static
    {
        $this->thread = $thread;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function isIsFunctionCall(): ?bool
    {
        return $this->is_function_call;
    }

    public function setIsFunctionCall(bool $is_function_call): static
    {
        $this->is_function_call = $is_function_call;

        return $this;
    }

    public function getFullRespoonse(): ?string
    {
        return $this->full_respoonse;
    }

    public function setFullRespoonse(string $full_respoonse): static
    {
        $this->full_respoonse = $full_respoonse;

        return $this;
    }
}

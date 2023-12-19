<?php

namespace App\Entity;

use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=AuthorRepository::class)
 */
class Author implements JsonSerializable
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=3, minMessage="Last name must be at least 3 characters long")
     */
    private ?string $last_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $surname;

    /**
     * @ORM\ManyToMany(targetEntity="Book", mappedBy="authors")
     */
    private Collection $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'surname' => $this->surname
        ];
    }

    /**
     * @param Book $book
     */
    public function addBook(Book $book): void
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            $book->addAuthor($this);
        }
    }

    /**
     * @param Book $book
     */
    public function removeBook(Book $book): void
    {
        $this->books->removeElement($book);
        $book->removeAuthor($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }
}

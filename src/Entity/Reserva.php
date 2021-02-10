<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReservaRepository")
 */
class Reserva
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario", inversedBy="reservas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Horas", inversedBy="reservas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $hora;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pista", inversedBy="reservas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pista;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getHora(): ?Horas
    {
        return $this->hora;
    }

    public function setHora(?Horas $hora): self
    {
        $this->hora = $hora;

        return $this;
    }

    public function getPista(): ?Pista
    {
        return $this->pista;
    }

    public function setPista(?Pista $pista): self
    {
        $this->pista = $pista;

        return $this;
    }
}

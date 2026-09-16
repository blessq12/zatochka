<?php

namespace App\Domain\CRM\Entity;

use App\Domain\CRM\Event\ClientRegistered;
use App\Domain\CRM\Event\ClientUpdated;
use App\Domain\CRM\ValueObject\AdditionalData;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final class Client extends AggregateRoot
{
    private Phone $phone;
    private ?string $name;
    private ?Email $email;
    private AdditionalData $additionalData;

    private function __construct(
        private readonly EntityId $id,
        Phone $phone,
        ?string $name = null,
        ?Email $email = null,
        ?AdditionalData $additionalData = null,
    ) {
        $this->phone = $phone;
        $this->name = $name;
        $this->email = $email;
        $this->additionalData = $additionalData ?? AdditionalData::empty();
    }

    public static function register(
        EntityId $id,
        Phone $phone,
        ?string $name = null,
        ?Email $email = null,
        ?string $birthDate = null,
        ?string $deliveryAddress = null,
    ): self {
        $additionalData = AdditionalData::empty()
            ->withBirthDate($birthDate)
            ->withDeliveryAddress($deliveryAddress);

        $client = new self($id, $phone, $name, $email, $additionalData);
        $client->record(new ClientRegistered($id, $phone->value));

        return $client;
    }

    public static function reconstitute(
        EntityId $id,
        Phone $phone,
        ?string $name,
        ?Email $email,
        AdditionalData $additionalData,
    ): self {
        return new self($id, $phone, $name, $email, $additionalData);
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function phone(): Phone
    {
        return $this->phone;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function email(): ?Email
    {
        return $this->email;
    }

    public function additionalData(): AdditionalData
    {
        return $this->additionalData;
    }

    public function birthDate(): ?string
    {
        return $this->additionalData->birthDate();
    }

    public function deliveryAddress(): ?string
    {
        return $this->additionalData->deliveryAddress();
    }

    public function updateProfile(
        ?string $name,
        ?Phone $phone,
        ?Email $email,
        ?string $birthDate = null,
        ?string $deliveryAddress = null,
        bool $updateBirthDate = false,
        bool $updateDeliveryAddress = false,
    ): void {
        if ($name !== null) {
            if (trim($name) === '') {
                throw new DomainException('Client name cannot be empty.');
            }

            $this->name = $name;
        }

        if ($phone !== null) {
            $this->phone = $phone;
        }

        if ($email !== null) {
            $this->email = $email;
        }

        if ($updateBirthDate) {
            $this->additionalData = $this->additionalData->withBirthDate($birthDate);
        }

        if ($updateDeliveryAddress) {
            $this->additionalData = $this->additionalData->withDeliveryAddress($deliveryAddress);
        }

        $this->record(new ClientUpdated($this->id));
    }
}

<?php

namespace App\Domain\CRM\Entity;

use App\Domain\CRM\Event\BonusAccrued;
use App\Domain\CRM\Event\ClientRegistered;
use App\Domain\CRM\Event\ClientUpdated;
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
    private ?string $birthDate;
    private ?string $deliveryAddress;
    private ?string $passwordHash;
    private BonusAccount $bonusAccount;

    /** @var list<ClientHistoryEntry> */
    private array $history = [];

    private function __construct(
        private readonly EntityId $id,
        Phone $phone,
        BonusAccount $bonusAccount,
        ?string $name = null,
        ?Email $email = null,
        ?string $birthDate = null,
        ?string $deliveryAddress = null,
        ?string $passwordHash = null,
    ) {
        $this->phone = $phone;
        $this->bonusAccount = $bonusAccount;
        $this->name = $name;
        $this->email = $email;
        $this->birthDate = $birthDate;
        $this->deliveryAddress = $deliveryAddress;
        $this->passwordHash = $passwordHash;
    }

    public static function register(
        EntityId $id,
        Phone $phone,
        EntityId $bonusAccountId,
        ?string $name = null,
        ?Email $email = null,
        ?string $birthDate = null,
        ?string $deliveryAddress = null,
        ?string $passwordHash = null,
    ): self {
        $client = new self(
            $id,
            $phone,
            new BonusAccount($bonusAccountId),
            $name,
            $email,
            $birthDate,
            $deliveryAddress,
            $passwordHash,
        );
        $client->record(new ClientRegistered($id, $phone->value));

        return $client;
    }

    /**
     * @param list<ClientHistoryEntry> $history
     */
    public static function reconstitute(
        EntityId $id,
        Phone $phone,
        BonusAccount $bonusAccount,
        ?string $name,
        ?Email $email,
        array $history = [],
        ?string $birthDate = null,
        ?string $deliveryAddress = null,
        ?string $passwordHash = null,
    ): self {
        $client = new self($id, $phone, $bonusAccount, $name, $email, $birthDate, $deliveryAddress, $passwordHash);
        $client->history = $history;

        return $client;
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

    public function birthDate(): ?string
    {
        return $this->birthDate;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function passwordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function hasPortalPassword(): bool
    {
        return $this->passwordHash !== null && $this->passwordHash !== '';
    }

    public function bonusAccount(): BonusAccount
    {
        return $this->bonusAccount;
    }

    /** @return list<ClientHistoryEntry> */
    public function history(): array
    {
        return $this->history;
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
            $this->birthDate = $birthDate !== null && trim($birthDate) !== '' ? trim($birthDate) : null;
        }

        if ($updateDeliveryAddress) {
            $normalized = $deliveryAddress !== null ? trim($deliveryAddress) : null;
            $this->deliveryAddress = $normalized !== '' ? $normalized : null;
        }

        $this->record(new ClientUpdated($this->id));
    }

    public function setPasswordHash(string $passwordHash): void
    {
        if (trim($passwordHash) === '') {
            throw new DomainException('Password hash cannot be empty.');
        }

        $this->passwordHash = $passwordHash;
        $this->record(new ClientUpdated($this->id));
    }

    public function accrueBonus(string $amount): void
    {
        $this->bonusAccount->accrue($amount);
        $this->record(new BonusAccrued($this->id, $this->bonusAccount->id(), $amount));
    }

    public function appendHistory(ClientHistoryEntry $entry): void
    {
        $this->history[] = $entry;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Query\Projections;

use App\Domain\Shared\ValueObject\DateTime;
use App\Domain\User\ValueObject\Auth\Credentials;
use App\Domain\User\ValueObject\Auth\HashedPassword;
use App\Domain\User\ValueObject\Email;
use Broadway\ReadModel\SerializableReadModel;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @psalm-suppress MissingConstructor
 */
class UserView implements SerializableReadModel
{
    private UuidInterface $uuid;

    private Credentials $credentials;

    private DateTime$createdAt;

    private ?DateTime $updatedAt;

    private function __construct(
        UuidInterface $uuid,
        Credentials $credentials,
        DateTime $createdAt,
        ?DateTime $updatedAt
    ) {
        $this->uuid = $uuid;
        $this->credentials = $credentials;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @throws \App\Domain\Shared\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     */
    public static function fromSerializable(Serializable $event): self
    {
        return self::deserialize($event->serialize());
    }

    /**
     * @throws \App\Domain\Shared\Exception\DateTimeException
     * @throws \Assert\AssertionFailedException
     *
     * @return UserView
     */
    public static function deserialize(array $data): self
    {
        return new self(
            Uuid::fromString($data['uuid']),
            new Credentials(
                Email::fromString($data['credentials']['email']),
                HashedPassword::fromHash($data['credentials']['password'] ?? '')
            ),
            DateTime::fromString($data['created_at']),
            isset($data['updated_at']) ? DateTime::fromString($data['updated_at']) : null
        );
    }

    public function serialize(): array
    {
        return [
            'uuid' => $this->getId(),
            'credentials' => [
                'email' => (string) $this->credentials->email,
            ],
        ];
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function email(): string
    {
        return (string) $this->credentials->email;
    }

    public function changeEmail(Email $email): void
    {
        $this->credentials->email = $email;
    }

    public function changeUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function hashedPassword(): string
    {
        return (string) $this->credentials->password;
    }

    public function getId(): string
    {
        return $this->uuid->toString();
    }
}

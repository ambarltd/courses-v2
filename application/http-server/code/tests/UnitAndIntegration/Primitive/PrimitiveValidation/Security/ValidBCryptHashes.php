<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security;

abstract class ValidBCryptHashes
{
    /**
     * @return string[]
     */
    public static function listValidBCryptHashes(): array
    {
        return [
            // hashes, salts, lengths
            '$2y$10$RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '$2y$10$mPpwOGID//gZOXu1DNjUP.LxGxWddydQK0KK5FQNSftOXOlm2fH9y',
            '$2y$10$Bh1L/5maC16hZV/XhdOuNOH4HvQBD/nqHRqALE7NzhyAyXna0dZou',
            '$2y$10$9Xe31ya4WfE0iSOxZy1nnOHQT9C0T54UofrLO2cqwre/hN8nFGi8K',
            '$2y$10$eAolUf5YDeucXY0.ob.jaOJCEQ57nokDPGMtJpQKAjpRjKmoHSDcK',
            '$2y$10$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            // costs
            '$2y$10$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$11$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$12$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$14$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$15$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$16$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$17$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$18$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$19$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$20$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$21$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$22$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$23$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$24$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$25$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$26$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$27$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$28$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$29$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$30$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$31$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
        ];
    }
}

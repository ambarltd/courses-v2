<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security;

abstract class InvalidBCryptHashes
{
    /**
     * @return string[]
     */
    public static function listInvalidBCryptHashes(): array
    {
        return [
            '', // way too short
            '$2y$11$9Xe31ya4WfE0iSOxZy1nnOHQT9C0T54UofrLO2cqwre/hN8nFGi8', // too short
            '                                                            ', // only spaces
            '$2y$11$9Xe31ya4WfE0iSOxZy1nnOHQT9C0T54UofrLO2cqwre/hN8nFGi8Ka', // too long
            '$2y$11$9Xe31ya4WfE0iSOxZy1nnOHQT9C0T54UofrLO2cqwre/hN8nFGi8Kasasdkfjsafjasd7ZYASD', // way too long
            // invalid format (should have $ at positions 0, 3, and 5.
            ' 2y$11$RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '$2y 11$RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '$2y$11 RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '@2y$11$RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '$2y~11$RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '$2y~11@RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '11y$11$RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '$2y311$RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            '$2y$115RU7YR7zNDuk0zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2',
            // invalid algorithms
            '$2x$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2a$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2z$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$1x$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$1a$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$1b$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$  $13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$ y$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2 $13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$$$$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$$y$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2$$13$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            // invalid costs
            '$2y$00$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$01$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$02$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$03$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$04$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$05$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$06$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$07$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$08$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$09$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$32$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$33$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$34$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$35$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$36$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$99$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$aa$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$bb$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$$1$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$3$$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$  $MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$ 1$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            '$2y$@3$MS5cJU3LW.xUICx9Qw9cheVkinGsuPDWI/8AqTMdzYAgMWQwaOs7y',
            // invalid salts
            '$2y$11$RU7YR7zNDuk@zxtK3/hHp.1QICBKb3uHxE9DSWrAees1U5UihtZP2', // invalid @character
            '$2y$11$mPpwOGID//g OXu1DNjUP.LxGxWddydQK0KK5FQNSftOXOlm2fH9y', // invalid space
            // invalid hashes (the hash section only)
            '$2y$11$mPpwOGID//gZOXu1DNjUP.LxGxWddydQK0KK5FQNSftOXOlm2fH9@', // invalid @ character
            '$2y$11$Bh1L/5maC16hZV/XhdOuNOH4HvQBD/nqHRqALE7NzhyAyXna0 Zou', // invalid space
        ];
    }
}

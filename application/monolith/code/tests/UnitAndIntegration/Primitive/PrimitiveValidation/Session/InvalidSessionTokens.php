<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session;

abstract class InvalidSessionTokens
{
    /**
     * @return string[]
     */
    public static function listInvalidSessionTokens(): array
    {
        return [
            'XIRG46Yacs82eghb6Yd', // too short (must be exactly 96 characters)
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j', // too short (must be exactly 56 characters)
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jyy', // too long (must be exactly 56 characters)
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jyXIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // too long (must be exactly 56 characters)
            '', // empty
            ' ', // space
            '                                                        ', // spaces
            ' IRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // leading space
            'XIRG46Yacs82eghb6Yd_OPiP LlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // space in the middle
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j ', // space at the end
            '!IRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // leads with !
            'XIRG46Yacs82eghb6Yd_OPiP!LlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // invalid ! in the middle
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j!', // invalid ! at the end
            '?IRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // leads with ?
            'XIRG46Yacs82eghb6Yd_OPiP?LlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // invalid ? in the middle
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j?', // invalid ? at the end
            '@IRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // leads with @
            'XIRG46Yacs82eghb6Yd_OPiP@LlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // invalid @ in the middle
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j@', // invalid @ at the end
            '+IRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // leads with +
            'XIRG46Yacs82eghb6Yd_OPiP+LlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // invalid + in the middle
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j+', // invalid + at the end
            '=IRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // leads with =
            'XIRG46Yacs82eghb6Yd_OPiP=LlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // invalid = in the middle
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j=', // invalid = at the end
            '/IRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // leads with /
            'XIRG46Yacs82eghb6Yd_OPiP/LlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6jy', // invalid / in the middle
            'XIRG46Yacs82eghb6Yd_OPiPnLlcKz-0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZ0uA9jYJHJZZcVvrrZQr3me6j/', // invalid / at the end
        ];
    }
}

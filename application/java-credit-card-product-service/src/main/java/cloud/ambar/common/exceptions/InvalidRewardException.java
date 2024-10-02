package cloud.ambar.common.exceptions;

import lombok.NoArgsConstructor;

@NoArgsConstructor
public class InvalidRewardException extends RuntimeException {
    public InvalidRewardException(String msg) {
        super(msg);
    }
}

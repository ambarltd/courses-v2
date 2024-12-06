package cloud.ambar.creditcard.enrollment.queryhandler;

import lombok.Builder;
import lombok.Getter;
import lombok.NonNull;

@Builder
@Getter
public class GetUserEnrollmentsQuery {
    @NonNull private String userId;
}

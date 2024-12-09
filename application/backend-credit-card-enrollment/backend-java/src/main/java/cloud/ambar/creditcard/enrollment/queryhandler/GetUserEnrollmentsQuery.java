package cloud.ambar.creditcard.enrollment.queryhandler;

import cloud.ambar.common.queryhandler.Query;
import lombok.Builder;
import lombok.Getter;
import lombok.NonNull;

@Builder
@Getter
public class GetUserEnrollmentsQuery  extends Query {
    @NonNull private String sessionToken;
}

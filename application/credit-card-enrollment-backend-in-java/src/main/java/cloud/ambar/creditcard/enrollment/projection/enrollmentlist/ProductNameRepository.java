package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import cloud.ambar.common.projection.MongoTransactionalAPI;
import lombok.RequiredArgsConstructor;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.Optional;

@RequestScope
@Service
@RequiredArgsConstructor
public class ProductNameRepository {
    private final MongoTransactionalAPI mongoTransactionalAPI;

    public void save(final ProductName productName) {
        mongoTransactionalAPI.operate().save(productName, "CreditCard_Enrollment_ProductName");
    }

    public Optional<ProductName> findOneById(final String id) {
        return Optional.ofNullable(mongoTransactionalAPI.operate().findOne(
                Query.query(
                        Criteria.where("id").is(id)
                ),
                ProductName.class,
                "CreditCard_Enrollment_ProductName"
        ));
    }
}

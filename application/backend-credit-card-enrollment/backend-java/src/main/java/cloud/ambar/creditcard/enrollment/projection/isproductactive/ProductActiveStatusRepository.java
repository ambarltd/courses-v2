package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import lombok.RequiredArgsConstructor;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.Optional;

@RequestScope
@Service
@RequiredArgsConstructor
public class ProductActiveStatusRepository {
    private final MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;

    public boolean isThereAnActiveProductWithId(final String productId) {
        Optional<ProductActiveStatus> productActiveStatus = Optional.ofNullable(mongoTransactionalProjectionOperator.operate().findOne(
                Query.query(
                        Criteria.where("id").is(productId)
                                .and("active").is(true)
                ),
                ProductActiveStatus.class,
                "CreditCard_Enrollment_ProductActiveStatus"
        ));

        return productActiveStatus.isPresent() && productActiveStatus.get().getActive();
    }

    public Optional<ProductActiveStatus> findOneById(final String productId) {
        return Optional.ofNullable(mongoTransactionalProjectionOperator.operate().findOne(
                Query.query(Criteria.where("id").is(productId)),
                ProductActiveStatus.class,
                "CreditCard_Enrollment_ProductActiveStatus"
        ));
    }

    public void save(final ProductActiveStatus productActiveStatus) {
        mongoTransactionalProjectionOperator.operate().save(productActiveStatus, "CreditCard_Enrollment_ProductActiveStatus");
    }
}

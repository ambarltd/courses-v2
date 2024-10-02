package cloud.ambar.creditCardProduct.aggregate;

public interface IEventTransformProduct {
    ProductAggregate transformProduct(ProductAggregate productAggregate);
}

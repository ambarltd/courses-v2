import {ProductName} from "./ProductName";
import {MongoTransactionalProjectionOperator} from "../../../../common/projection/MongoTransactionalProjectionOperator";

export class ProductNameRepository {
    private readonly collectionName = 'CreditCard_Enrollment_ProductName';

    constructor(private readonly mongoOperator: MongoTransactionalProjectionOperator) {}

    async save(productName: ProductName): Promise<void> {
        await this.mongoOperator.replaceOne(
            this.collectionName,
            { _id: productName._id },
            productName,
            { upsert: true }
        );
    }

    async findOneById(_id: string): Promise<ProductName | null> {
        const results = await this.mongoOperator.find<ProductName>(this.collectionName, { _id });
        return results.length > 0 ? results[0] : null;
    }
}
import {ProductActiveStatus} from "./ProductActiveStatus";
import {MongoTransactionalProjectionOperator} from "../../../../common/projection/MongoTransactionalProjectionOperator";
import {inject, injectable} from "tsyringe";

@injectable()
export class ProductActiveStatusRepository {
    private readonly collectionName = 'CreditCard_Enrollment_ProductActiveStatus';

    constructor(
        @inject(MongoTransactionalProjectionOperator) private readonly mongoOperator: MongoTransactionalProjectionOperator
    ) {}

    async isThereAnActiveProductWithId(productId: string): Promise<boolean> {
        const results = await this.mongoOperator.find<ProductActiveStatus>(
            this.collectionName,
            { _id: productId, active: true }
        );
        return results.length > 0;
    }

    async findOneById(productId: string): Promise<ProductActiveStatus | null> {
        const results = await this.mongoOperator.find<ProductActiveStatus>(this.collectionName, { _id: productId });
        return results.length > 0 ? results[0] : null;
    }

    async save(productActiveStatus: ProductActiveStatus): Promise<void> {
        await this.mongoOperator.replaceOne(
            this.collectionName,
            { _id: productActiveStatus._id },
            productActiveStatus,
            { upsert: true }
        );
    }
}
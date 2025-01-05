import {Enrollment} from "./Enrollment";
import {MongoTransactionalProjectionOperator} from "../../../../common/projection/MongoTransactionalProjectionOperator";

export class EnrollmentRepository {
    private readonly collectionName = 'CreditCard_Enrollment_Enrollment';

    constructor(private readonly mongoOperator: MongoTransactionalProjectionOperator) {}

    async save(enrollment: Enrollment): Promise<void> {
        await this.mongoOperator.replaceOne(
            this.collectionName,
            { _id: enrollment._id },
            enrollment,
            { upsert: true }
        );
    }

    async findOneById(_id: string): Promise<Enrollment | null> {
        const results = await this.mongoOperator.find<Enrollment>(this.collectionName, { _id });
        return results.length > 0 ? results[0] : null;
    }

    async findAllByUserId(userId: string): Promise<Enrollment[]> {
        return this.mongoOperator.find<Enrollment>(this.collectionName, { userId });
    }
}
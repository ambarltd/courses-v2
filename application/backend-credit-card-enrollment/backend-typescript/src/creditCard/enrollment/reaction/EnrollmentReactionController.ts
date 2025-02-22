import { ReactionController } from '../../../common/reaction/ReactionController';
import { AmbarHttpRequest } from '../../../common/ambar/AmbarHttpRequest';
import { ReviewEnrollmentReactionHandler } from './ReviewEnrollmentReactionHandler';
import { PostgresTransactionalEventStore } from '../../../common/eventStore/PostgresTransactionalEventStore';
import { MongoTransactionalProjectionOperator } from '../../../common/projection/MongoTransactionalProjectionOperator';
import { Deserializer } from '../../../common/serializedEvent/Deserializer';
import {Request, Response, Router} from "express";
import {inject, injectable} from "tsyringe";

@injectable()
export class EnrollmentReactionController extends ReactionController {
    public readonly router: Router;

    constructor(
        @inject(PostgresTransactionalEventStore) eventStore: PostgresTransactionalEventStore,
        @inject(MongoTransactionalProjectionOperator) mongoOperator: MongoTransactionalProjectionOperator,
        @inject(Deserializer) deserializer: Deserializer,
        @inject(ReviewEnrollmentReactionHandler) private readonly reviewEnrollmentReactionHandler: ReviewEnrollmentReactionHandler
    ) {
        super(eventStore, mongoOperator, deserializer);
        this.router = Router();
        this.router.post('/review_enrollment', this.reactWithReviewEnrollment.bind(this));
    }

    async reactWithReviewEnrollment(req: Request, res: Response): Promise<void> {
        const response = await this.processReactionHttpRequest(
            req.body as AmbarHttpRequest,
            this.reviewEnrollmentReactionHandler,
        );
        res.status(200).contentType('application/json').send(response);
    }
}

import {Request, Response, Router} from 'express';
import { QueryController } from '../../../common/query/QueryController';
import { MongoTransactionalProjectionOperator } from '../../../common/projection/MongoTransactionalProjectionOperator';
import { GetUserEnrollmentsQueryHandler } from './GetUserEnrollmentsQueryHandler';
import { GetUserEnrollmentsQuery } from './GetUserEnrollmentsQuery';
import {inject, injectable} from "tsyringe";

@injectable()
export class EnrollmentQueryController extends QueryController {
    public readonly router: Router;

    private readonly getUserEnrollmentsQueryHandler: GetUserEnrollmentsQueryHandler;

    constructor(
        @inject(MongoTransactionalProjectionOperator) mongoTransactionalProjectionOperator: MongoTransactionalProjectionOperator,
        @inject(GetUserEnrollmentsQueryHandler) getUserEnrollmentsQueryHandler: GetUserEnrollmentsQueryHandler
    ) {
        super(mongoTransactionalProjectionOperator);
        this.getUserEnrollmentsQueryHandler = getUserEnrollmentsQueryHandler;
        this.router = Router();
        this.router.post('/list-enrollments', this.listEnrollments.bind(this));
    }

    async listEnrollments(req: Request, res: Response): Promise<void> {
        const sessionToken = req.header('X-With-Session-Token');
        if (!sessionToken) {
            res.status(400).send({ error: 'Session token is required' });
            return;
        }

        const query = new GetUserEnrollmentsQuery(sessionToken);

        const result = await this.processQuery(query, this.getUserEnrollmentsQueryHandler);
        res.status(200).json(result);
    }
}

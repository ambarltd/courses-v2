import { Router, Request, Response } from 'express';
import { CommandController } from '../../../common/command/CommandController';
import { PostgresTransactionalEventStore } from '../../../common/eventStore/PostgresTransactionalEventStore';
import { MongoTransactionalProjectionOperator } from '../../../common/projection/MongoTransactionalProjectionOperator';
import { RequestEnrollmentCommandHandler } from './RequestEnrollmentCommandHandler';
import { RequestEnrollmentCommand } from './RequestEnrollmentCommand';
import { RequestEnrollmentHttpRequest } from './RequestEnrollmentHttpRequest';
import {inject, injectable} from "tsyringe";
import {typeSafeCoercion} from "../../../common/util/TypeSafeCoercion";

@injectable()
export class EnrollmentCommandController extends CommandController {
    public readonly router: Router;

    private readonly requestEnrollmentCommandHandler: RequestEnrollmentCommandHandler;

    constructor(
        @inject(PostgresTransactionalEventStore) postgresTransactionalEventStore: PostgresTransactionalEventStore,
        @inject(MongoTransactionalProjectionOperator) mongoTransactionalProjectionOperator: MongoTransactionalProjectionOperator,
        @inject(RequestEnrollmentCommandHandler) requestEnrollmentCommandHandler: RequestEnrollmentCommandHandler
    ) {
        super(postgresTransactionalEventStore, mongoTransactionalProjectionOperator);
        this.requestEnrollmentCommandHandler = requestEnrollmentCommandHandler;
        this.router = Router();
        this.router.post('/request-enrollment', this.requestEnrollment.bind(this));
    }

    async requestEnrollment(req: Request, res: Response): Promise<void> {
        const sessionToken = req.header('X-With-Session-Token');
        if (!sessionToken) {
            res.status(400).send({ error: 'Session token is required' });
            return;
        }

        const requestBody = typeSafeCoercion<RequestEnrollmentHttpRequest>(req.body);
        const command = new RequestEnrollmentCommand(
            sessionToken,
            requestBody.productId,
            requestBody.annualIncomeInCents
        );

        await this.processCommand(command, this.requestEnrollmentCommandHandler);
        res.status(200).json({});
    }
}

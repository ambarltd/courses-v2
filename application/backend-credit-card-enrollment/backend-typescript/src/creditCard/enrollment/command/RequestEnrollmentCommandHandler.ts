import { CommandHandler } from '../../../common/command/CommandHandler';
import { PostgresTransactionalEventStore } from '../../../common/eventStore/PostgresTransactionalEventStore';
import { IdGenerator } from '../../../common/util/IdGenerator';
import { RequestEnrollmentCommand } from './RequestEnrollmentCommand';
import { EnrollmentRequested } from '../event/EnrollmentRequested';
import {SessionService} from "../../../common/sessionAuth/SessionService";
import {IsProductActive} from "../projection/isProductActive/IsProductActive";

export class RequestEnrollmentCommandHandler extends CommandHandler {
    private readonly sessionService: SessionService;
    private readonly isProductActive: IsProductActive;

    constructor(
        postgresTransactionalEventStore: PostgresTransactionalEventStore,
        sessionService: SessionService,
        isProductActive: IsProductActive
    ) {
        super(postgresTransactionalEventStore);
        this.sessionService = sessionService;
        this.isProductActive = isProductActive;
    }

    async handleCommand(command: RequestEnrollmentCommand): Promise<void> {
        const userId = await this.sessionService.authenticatedUserIdFromSessionToken(command.sessionToken);

        if (!(await this.isProductActive.isProductActiveById(command.productId))) {
            throw new Error('Product is inactive and not eligible for enrollment request.');
        }

        const eventId = IdGenerator.generateRandomId();
        const aggregateId = IdGenerator.generateRandomId();

        const enrollmentRequested = new EnrollmentRequested(
            eventId,
            aggregateId,
            1,
            eventId,
            eventId,
            new Date(),
            userId,
            command.productId,
            command.annualIncomeInCents
        );

        await this.postgresTransactionalEventStore.saveEvent(enrollmentRequested);
    }
}

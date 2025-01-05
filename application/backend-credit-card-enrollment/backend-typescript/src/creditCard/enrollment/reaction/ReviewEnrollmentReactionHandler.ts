import { ReactionHandler } from '../../../common/reaction/ReactionHandler';
import { EnrollmentRequested } from '../event/EnrollmentRequested';
import { EnrollmentAccepted } from '../event/EnrollmentAccepted';
import { EnrollmentDeclined } from '../event/EnrollmentDeclined';
import { PostgresTransactionalEventStore } from '../../../common/eventStore/PostgresTransactionalEventStore';
import { IdGenerator } from '../../../common/util/IdGenerator';
import {GetEnrollmentList} from "../projection/enrollmentList/GetEnrollmentList";
import {Enrollment} from "../aggregate/Enrollment";
import {Event} from "../../../common/event/Event";
import {inject, injectable} from "tsyringe";

@injectable()
export class ReviewEnrollmentReactionHandler extends ReactionHandler {
    constructor(
        @inject(PostgresTransactionalEventStore)eventStore: PostgresTransactionalEventStore,
        @inject(GetEnrollmentList) private readonly getEnrollmentList: GetEnrollmentList,
    ) {
        super(eventStore);
    }

    async react(event: Event): Promise<void> {
        if (!(event instanceof EnrollmentRequested)) {
            return;
        }

        const aggregateData = await this.postgresTransactionalEventStore.findAggregate<Enrollment>(event.aggregateId);
        const enrollment = aggregateData.aggregate;

        if (enrollment.status !== 'Requested') {
            return;
        }

        const reactionEventId = IdGenerator.generateDeterministicId(`ReviewedEnrollment${event.eventId}`);
        if (await this.postgresTransactionalEventStore.doesEventAlreadyExist(reactionEventId)) {
            return;
        }

        const alreadyAccepted = await this.getEnrollmentList.isThereAnyAcceptedEnrollmentForUserAndProduct(
            enrollment.userId,
            enrollment.productId
        );

        if (alreadyAccepted) {
            const reactionEvent = new EnrollmentDeclined(
                reactionEventId,
                enrollment.aggregateId,
                enrollment.aggregateVersion + 1,
                aggregateData.correlationIdOfLastEvent,
                aggregateData.eventIdOfLastEvent,
                new Date(),
                'ALREADY_ACCEPTED',
                'You were already accepted to this product.'
            );

            await this.postgresTransactionalEventStore.saveEvent(reactionEvent);
            return;
        }

        if (enrollment.annualIncomeInCents < 1500000) {
            const reactionEvent = new EnrollmentDeclined(
                reactionEventId,
                enrollment.aggregateId,
                enrollment.aggregateVersion + 1,
                aggregateData.correlationIdOfLastEvent,
                aggregateData.eventIdOfLastEvent,
                new Date(),
                'INSUFFICIENT_INCOME',
                'Insufficient annual income.'
            );

            await this.postgresTransactionalEventStore.saveEvent(reactionEvent);
            return;
        }

        const reactionEvent = new EnrollmentAccepted(
            reactionEventId,
            enrollment.aggregateId,
            enrollment.aggregateVersion + 1,
            aggregateData.correlationIdOfLastEvent,
            aggregateData.eventIdOfLastEvent,
            new Date(),
            'ALL_CHECKS_PASSED',
            'All checks passed.'
        );

        await this.postgresTransactionalEventStore.saveEvent(reactionEvent);
    }
}

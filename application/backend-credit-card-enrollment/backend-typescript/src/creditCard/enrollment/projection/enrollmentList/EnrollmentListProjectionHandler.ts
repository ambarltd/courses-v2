import { ProjectionHandler } from '../../../../common/projection/ProjectionHandler';
import { Enrollment } from './Enrollment';
import { EnrollmentRepository } from './EnrollmentRepository';
import { EnrollmentRequested } from '../../event/EnrollmentRequested';
import { EnrollmentAccepted } from '../../event/EnrollmentAccepted';
import { EnrollmentDeclined } from '../../event/EnrollmentDeclined';
import {ProductDefined} from "../../../product/event/ProductDefined";
import {ProductNameRepository} from "./ProductNameRepository";
import {ProductName} from "./ProductName";
import {EnrollmentStatus} from "../../aggregate/EnrollmentStatus";

export class EnrollmentListProjectionHandler extends ProjectionHandler {
    constructor(
        private readonly enrollmentRepository: EnrollmentRepository,
        private readonly productNameRepository: ProductNameRepository
    ) {
        super();
    }

    async project(event: any): Promise<void> {
        if (event instanceof ProductDefined) {
            await this.productNameRepository.save(new ProductName(event.aggregateId, event.name));
        } else if (event instanceof EnrollmentRequested) {
            const enrollment = new Enrollment(
                event.aggregateId,
                event.userId,
                event.productId,
                event.recordedOn,
                EnrollmentStatus.Requested
            );
            await this.enrollmentRepository.save(enrollment);
        } else if (event instanceof EnrollmentAccepted) {
            const enrollment = await this.enrollmentRepository.findOneById(event.aggregateId);
            if (!enrollment) throw new Error('Enrollment not found');
            enrollment.status = EnrollmentStatus.Accepted;
            enrollment.reviewedOn = event.recordedOn;
            enrollment.statusReason = event.reasonDescription;
            await this.enrollmentRepository.save(enrollment);
        } else if (event instanceof EnrollmentDeclined) {
            const enrollment = await this.enrollmentRepository.findOneById(event.aggregateId);
            if (!enrollment) throw new Error('Enrollment not found');
            enrollment.status = EnrollmentStatus.Declined;
            enrollment.reviewedOn = event.recordedOn;
            enrollment.statusReason = event.reasonDescription;
            await this.enrollmentRepository.save(enrollment);
        }
    }
}
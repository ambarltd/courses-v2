import { EnrollmentRepository } from './EnrollmentRepository';
import { EnrollmentListItem } from './EnrollmentListItem';
import { ProductNameRepository } from './ProductNameRepository';

export class GetEnrollmentList {
    constructor(
        private readonly enrollmentRepository: EnrollmentRepository,
        private readonly productNameRepository: ProductNameRepository
    ) {}

    async getList(userId: string): Promise<EnrollmentListItem[]> {
        const enrollments = await this.enrollmentRepository.findAllByUserId(userId);
        return Promise.all(
            enrollments.map(async (e) => {
                const productName = await this.productNameRepository.findOneById(e.productId);
                if (!productName) throw new Error('Product name not found');
                return new EnrollmentListItem(
                    e._id,
                    e.userId,
                    e.productId,
                    productName.name,
                    e.requestedDate,
                    e.status,
                    e.statusReason,
                    e.reviewedOn
                );
            })
        );
    }

    async isThereAnyAcceptedEnrollmentForUserAndProduct(userId: string, productId: string): Promise<boolean> {
        const enrollments = await this.enrollmentRepository.findAllByUserId(userId);
        return enrollments.some(e => e.productId === productId && e.status === 'Accepted');
    }
}
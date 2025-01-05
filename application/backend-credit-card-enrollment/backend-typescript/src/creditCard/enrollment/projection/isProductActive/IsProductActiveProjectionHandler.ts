import { ProjectionHandler } from '../../../../common/projection/ProjectionHandler';
import { ProductActiveStatus } from './ProductActiveStatus';
import { ProductActiveStatusRepository } from './ProductActiveStatusRepository';
import { ProductActivated } from '../../../product/event/ProductActivated';
import { ProductDeactivated } from '../../../product/event/ProductDeactivated';
import { ProductDefined } from '../../../product/event/ProductDefined';

export class IsProductActiveProjectionHandler extends ProjectionHandler {
    constructor(private readonly productActiveStatusRepository: ProductActiveStatusRepository) {
        super();
    }

    async project(event: any): Promise<void> {
        if (event instanceof ProductDefined) {
            const productStatus = new ProductActiveStatus(event.aggregateId, false);
            await this.productActiveStatusRepository.save(productStatus);
        } else if (event instanceof ProductActivated) {
            const productStatus = await this.productActiveStatusRepository.findOneById(event.aggregateId);
            if (!productStatus) throw new Error('Product not found');
            productStatus.active = true;
            await this.productActiveStatusRepository.save(productStatus);
        } else if (event instanceof ProductDeactivated) {
            const productStatus = await this.productActiveStatusRepository.findOneById(event.aggregateId);
            if (!productStatus) throw new Error('Product not found');
            productStatus.active = false;
            await this.productActiveStatusRepository.save(productStatus);
        }
    }
}
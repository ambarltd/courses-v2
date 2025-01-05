import {ProductActiveStatusRepository} from "./ProductActiveStatusRepository";

export class IsProductActive {
    constructor(private readonly productActiveStatusRepository: ProductActiveStatusRepository) {}

    async isProductActiveById(productId: string): Promise<boolean> {
        return this.productActiveStatusRepository.isThereAnActiveProductWithId(productId);
    }
}
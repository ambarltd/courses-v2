import {ProductActiveStatusRepository} from "./ProductActiveStatusRepository";
import {inject, injectable} from "tsyringe";

@injectable()
export class IsProductActive {
    constructor(
        @inject(ProductActiveStatusRepository) private readonly productActiveStatusRepository: ProductActiveStatusRepository
    ) {}

    async isProductActiveById(productId: string): Promise<boolean> {
        return this.productActiveStatusRepository.isThereAnActiveProductWithId(productId);
    }
}
import { Command } from '../../../common/command/Command';

export class RequestEnrollmentCommand extends Command {
    constructor(
        public readonly sessionToken: string,
        public readonly productId: string,
        public readonly annualIncomeInCents: number
    ) {
        super();
    }
}

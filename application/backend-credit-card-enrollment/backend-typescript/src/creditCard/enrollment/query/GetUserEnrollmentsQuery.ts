import { Query } from '../../../common/query/Query';

export class GetUserEnrollmentsQuery extends Query {
    constructor(public readonly sessionToken: string) {
        super();
    }
}

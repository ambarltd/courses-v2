import { QueryHandler } from '../../../common/query/QueryHandler';
import { GetUserEnrollmentsQuery } from './GetUserEnrollmentsQuery';
import {SessionService} from "../../../common/sessionAuth/SessionService";
import {EnrollmentListItem} from "../projection/enrollmentList/EnrollmentListItem";
import {GetEnrollmentList} from "../projection/enrollmentList/GetEnrollmentList";
import {MongoTransactionalProjectionOperator} from "../../../common/projection/MongoTransactionalProjectionOperator";

export class GetUserEnrollmentsQueryHandler extends QueryHandler {
    private readonly sessionService: SessionService;
    private readonly getEnrollmentList: GetEnrollmentList;

    constructor(
        mongoTransactionalProjectionOperator: MongoTransactionalProjectionOperator,
        sessionService: SessionService,
        getEnrollmentList: GetEnrollmentList
    ) {
        super(mongoTransactionalProjectionOperator);
        this.sessionService = sessionService;
        this.getEnrollmentList = getEnrollmentList;
    }

    async handleQuery(query: GetUserEnrollmentsQuery): Promise<EnrollmentListItem[]> {
        const userId = await this.sessionService.authenticatedUserIdFromSessionToken(query.sessionToken);
        return this.getEnrollmentList.getList(userId);
    }
}

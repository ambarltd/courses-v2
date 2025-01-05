import { Request, Response, Router } from 'express';
import { MongoTransactionalProjectionOperator } from '../../../common/projection/MongoTransactionalProjectionOperator';
import { Deserializer } from '../../../common/serializedEvent/Deserializer';
import { AmbarHttpRequest } from '../../../common/ambar/AmbarHttpRequest';
import {ProjectionController} from "../../../common/projection/ProjectionController";
import {IsProductActiveProjectionHandler} from "./isProductActive/IsProductActiveProjectionHandler";
import {EnrollmentListProjectionHandler} from "./enrollmentList/EnrollmentListProjectionHandler";

export class EnrollmentProjectionController extends ProjectionController{
    public readonly router: Router;

    constructor(
        mongoOperator: MongoTransactionalProjectionOperator,
        deserializer: Deserializer,
        private readonly isProductActiveProjectionHandler: IsProductActiveProjectionHandler,
        private readonly enrollmentListProjectionHandler: EnrollmentListProjectionHandler
    ) {
        super(mongoOperator, deserializer);
        this.router = Router();
        this.router.post('/is_card_product_active', this.projectIsCardProductActive.bind(this));
        this.router.post('/enrollment_list', this.projectEnrollmentList.bind(this));
    }

    private async projectIsCardProductActive(req: Request, res: Response): Promise<void> {
        const response = await this.processProjectionHttpRequest(
            req.body as AmbarHttpRequest,
            this.isProductActiveProjectionHandler,
            'CreditCard_Enrollment_IsProductActive'
        );
        res.status(200).contentType('application/json').send(response);
    }

    private async projectEnrollmentList(req: Request, res: Response): Promise<void> {
        const response = await this.processProjectionHttpRequest(
            req.body as AmbarHttpRequest,
            this.enrollmentListProjectionHandler,
            'CreditCard_Enrollment_EnrollmentList'
        );
        res.status(200).contentType('application/json').send(response);
    }
}

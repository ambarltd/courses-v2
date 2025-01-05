import express from 'express';
import { EnrollmentProjectionController } from './src/creditCard/enrollment/projection/EnrollmentProjectionController';
import {EnrollmentQueryController} from "./src/creditCard/enrollment/query/EnrollmentQueryController";
import {EnrollmentCommandController} from "./src/creditCard/enrollment/command/EnrollmentCommandController";
import {EnrollmentReactionController} from "./src/creditCard/enrollment/reaction/EnrollmentReactionController";

const app = express();
app.use(express.json());

app.use('/api/v1/credit_card/enrollment', ( new EnrollmentCommandController()).router);
app.use('/api/v1/credit_card/enrollment', ( new EnrollmentQueryController()).router);
app.use('/api/v1/credit_card/enrollment/projection', ( new EnrollmentProjectionController()).router);
app.use('/api/v1/credit_card/enrollment/reaction', ( new EnrollmentReactionController()).router);
app.get('/healthcheck', (req, res) => res.send('OK'));

app.listen(8080, () => {
    console.log(`Server is running on port 8080`);
});
import 'reflect-metadata';
import express from 'express';
import { EnrollmentProjectionController } from './src/creditCard/enrollment/projection/EnrollmentProjectionController';
import { EnrollmentQueryController } from "./src/creditCard/enrollment/query/EnrollmentQueryController";
import { EnrollmentCommandController } from "./src/creditCard/enrollment/command/EnrollmentCommandController";
import { EnrollmentReactionController } from "./src/creditCard/enrollment/reaction/EnrollmentReactionController";
import { configureDependencies } from './src/di/container';
import { scopedContainer } from './src/di/scopedContainer';

// Configure dependency injection
configureDependencies();

const app = express();
app.use(express.json());

// Add scoped container middleware
app.use(scopedContainer);

// Add routes
app.use('/api/v1/credit_card/enrollment', (req, res, next) => {
    const controller = req.container.resolve(EnrollmentCommandController);
    return controller.router(req, res, next);
});

app.use('/api/v1/credit_card/enrollment', (req, res, next) => {
    const controller = req.container.resolve(EnrollmentQueryController);
    return controller.router(req, res, next);
});

app.use('/api/v1/credit_card/enrollment/projection', (req, res, next) => {
    const controller = req.container.resolve(EnrollmentProjectionController);
    return controller.router(req, res, next);
});

app.use('/api/v1/credit_card/enrollment/reaction', (req, res, next) => {
    const controller = req.container.resolve(EnrollmentReactionController);
    return controller.router(req, res, next);
});

app.get('/docker_healthcheck', (req, res) => res.send('OK'));

// Error handling middleware
app.use((err: Error, req: express.Request, res: express.Response, next: express.NextFunction) => {
    console.error('Unhandled error:', err);
    res.status(500).json({
        error: err.message,
        stack: 'Available in logs'
    });
});

app.listen(8080, () => {
    console.log('Server is running on port 8080');
});
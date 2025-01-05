import 'reflect-metadata';
import express from 'express';
import { EnrollmentProjectionController } from './creditCard/enrollment/projection/EnrollmentProjectionController';
import { EnrollmentQueryController } from "./creditCard/enrollment/query/EnrollmentQueryController";
import { EnrollmentCommandController } from "./creditCard/enrollment/command/EnrollmentCommandController";
import { EnrollmentReactionController } from "./creditCard/enrollment/reaction/EnrollmentReactionController";
import { container } from 'tsyringe';
import { configureDependencies } from './di/container';
import { scopedContainer } from './di/scopedContainer';
import {MongoInitializer} from "./common/util/MongoInitializer";
import {PostgresInitializer} from "./common/util/PostgresInitializer";
import {log} from "./common/util/Logger";

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
    log.error('Unhandled error:', err);
    res.status(500).json({
        error: err.message,
        stack: 'Available in logs'
    });
});

const mongoInitializer = container.resolve(MongoInitializer);
const postgresInitializer = container.resolve(PostgresInitializer);

Promise.all([
    postgresInitializer.initialize(),
    mongoInitializer.initialize()
])
    .then(() => {
        app.listen(8080, () => {
            console.log('Server is running on port 8080');
        });
    })
    .catch(error => {
        console.error('Failed to initialize databases:', error);
        process.exit(1);
    });
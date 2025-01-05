import { Request, Response, NextFunction } from 'express';
import { container, DependencyContainer } from 'tsyringe';

declare global {
    namespace Express {
        interface Request {
            container: DependencyContainer;
        }
    }
}

export function scopedContainer(req: Request, res: Response, next: NextFunction) {
    req.container = container.createChildContainer();

    res.on('finish', () => {
        req.container.dispose();
    });

    next();
}